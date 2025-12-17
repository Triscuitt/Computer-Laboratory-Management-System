<?php
require_once '../dbconnection.php';
date_default_timezone_set('Asia/Manila');

class Session
{
    private $session_id;
    private $session_code;
    private $faculty_id;
    private $subject;
    private $section;
    private $lab_name;
    private $pc_number;
    private $duration_minutes;
    private $created_at;
    private $expires_at;
    private $is_active;

    public function __construct($faculty_id, $subject, $section, $lab_name = null, $duration_minutes = 15)
    {
        $this->faculty_id = $faculty_id;
        $this->subject = $subject;
        $this->section = $section;
        $this->lab_name = $lab_name;
        $this->duration_minutes = $duration_minutes;
    }

    /**
     * Create a new attendance session and return the session ID
     * @return int|false Session ID on success, false on failure
     */
    public function create()
    {
        $conn = getConnection();
        
        // Generate unique session code
        $this->session_code = $this->generateSessionCode();
        
        // Calculate expiration time
        $created_at = date('Y-m-d H:i:s');
        $expires_at = date('Y-m-d H:i:s', strtotime($created_at . " +{$this->duration_minutes} minutes"));

        
        // 7 parameters when lab_name is provided: session_code(s), faculty_id(i), subject(s), section(s), lab_name(s), duration_minutes(i), expires_at(s)
        $stmt = $conn->prepare("INSERT INTO lab_sessions (session_code, faculty_id, subject, section, lab_name, duration_minutes, expires_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sisssis", 
            $this->session_code,
            $this->faculty_id,
            $this->subject,
            $this->section,
            $this->lab_name,
            $this->duration_minutes,
            $expires_at
        );
        
        $result = $stmt->execute();
        
        // Check for errors
        if (!$result) {
            $error_msg = $stmt->error;
            $error_code = $stmt->errno;
            error_log("Session creation failed: Error #$error_code - $error_msg");
            $stmt->close();
            $conn->close();
            throw new Exception("Database error: $error_msg (Code: $error_code)");
        }
        
        $this->session_id = $conn->insert_id;
        $this->created_at = date('Y-m-d H:i:s');
        $this->expires_at = $expires_at;
        $this->is_active = 1;
        
        $stmt->close();
        $conn->close();
        
        return $this->session_id;
    }

    /**
     * Generate a unique session code
     * @return string
     */
    private function generateSessionCode()
    {
        $conn = getConnection();
        $unique = false;
        $code = '';
        
        while (!$unique) {
            // Format: SUBJECT-SECTION-TIMESTAMP (e.g., IT110-BSIT2A-123456)
            $timestamp = substr(time(), -6);
            $code = strtoupper(substr($this->subject, 0, 5) . '-' . substr($this->section, 0, 6) . '-' . $timestamp);
            
            $stmt = $conn->prepare("SELECT session_id FROM lab_sessions WHERE session_code = ? LIMIT 1");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $unique = true;
            }
            
            $stmt->close();
        }
        
        $conn->close();
        return $code;
    }

    /**
     * Get session by session code
     * @param string $session_code
     * @return array|false Session data or false if not found
     */
    public static function getByCode($session_code)
    {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM lab_sessions WHERE session_code = ? AND is_active = 1 LIMIT 1");
        $stmt->bind_param("s", $session_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $session = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $session;
    }

    /**
     * Get all active sessions for a faculty member
     * @param int $faculty_id
     * @return array
     */
    public static function getActiveByFaculty($faculty_id)
    {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT s.*, 
        (SELECT COUNT(*) FROM session_attendance a WHERE a.session_id = s.session_id) as attendee_count 
        FROM lab_sessions s 
        WHERE s.faculty_id = ? AND s.is_active = 1 AND s.expires_at >= NOW()
        ORDER BY s.created_at DESC;");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            array_push($sessions, $row);
        }
        
        $stmt->close();
        $conn->close();
        
        return $sessions;
    }

    public static function getAllArchived()
    {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT s.*, 
        u.first_name, u.last_name,
        (SELECT COUNT(*) FROM session_attendance a WHERE a.session_id = s.session_id) as attendee_count 
        FROM lab_sessions s 
        JOIN users u ON s.faculty_id = u.id 
        WHERE s.is_active = 0
        ORDER BY s.created_at DESC;");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            array_push($sessions, $row);
        }
        
        $stmt->close();
        $conn->close();
        
        return $sessions;
    }

    /**
     * Get all active sessions (for students to scan)
     * @return array
     */
    public static function getAllActive()
    {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT s.*, 
        u.first_name, u.last_name,
        (SELECT COUNT(*) FROM session_attendance a WHERE a.session_id = s.session_id) as attendee_count 
        FROM lab_sessions s 
        JOIN users u ON s.faculty_id = u.id 
        WHERE s.is_active = 1 AND s.expires_at >= NOW()
        ORDER BY s.created_at DESC;");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            array_push($sessions, $row);
        }
        
        $stmt->close();
        $conn->close();
        
        return $sessions;
    }

    /**
     * Deactivate a session
     * @param int $session_id
     * @return bool
     */
    public static function deactivate($session_id)
    {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE lab_sessions SET is_active = 0 WHERE session_id = ?");
        $stmt->bind_param("i", $session_id);
        $result = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $result;
    }

    /**
     * Extend session duration
     * @param int $session_id
     * @param int $additional_minutes
     * @return bool
     */
    public static function extend($session_id, $additional_minutes)
    {
        $conn = getConnection();
        $stmt = $conn->prepare("UPDATE lab_sessions SET expires_at = DATE_ADD(expires_at, INTERVAL ? MINUTE) WHERE session_id = ? AND is_active = 1");
        $stmt->bind_param("ii", $additional_minutes, $session_id);
        $result = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $result;
    }

    // Getters
    public function getSessionId() { return $this->session_id; }
    public function getSessionCode() { return $this->session_code; }
    public function getFacultyId() { return $this->faculty_id; }
    public function getSubject() { return $this->subject; }
    public function getSection() { return $this->section; }
    public function getLabName() { return $this->lab_name; }
    public function getPcNumber() { return $this->pc_number; }
    public function getDurationMinutes() { return $this->duration_minutes; }
    public function getCreatedAt() { return $this->created_at; }
    public function getExpiresAt() { return $this->expires_at; }
    public function getIsActive() { return $this->is_active; }
}
?>

