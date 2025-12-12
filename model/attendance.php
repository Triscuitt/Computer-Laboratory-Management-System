<?php
require_once '../dbconnection.php';

class Attendance
{
    /**
     * Record attendance for a student
     * @param int $session_id
     * @param int $student_id
     * @return array Result with success status and message
     */
    public static function record($session_id, $student_id, $pc_number)
    {
        $conn = getConnection();
        
        // Check if session exists and is active
        $sessionStmt = $conn->prepare("SELECT session_id, expires_at, is_active FROM lab_sessions WHERE session_id = ? LIMIT 1");
        $sessionStmt->bind_param("i", $session_id);
        $sessionStmt->execute();
        $sessionResult = $sessionStmt->get_result();
        
        if ($sessionResult->num_rows === 0) {
            $sessionStmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Session not found'];
        }
        
        $session = $sessionResult->fetch_assoc();
        $sessionStmt->close();
        
        // Check if session is still active
        if ($session['is_active'] == 0) {
            $conn->close();
            return ['success' => false, 'message' => 'Session has ended'];
        }
        
        // Check if session has expired
        if (strtotime($session['expires_at']) < time()) {
            $conn->close();
            return ['success' => false, 'message' => 'Session has expired'];
        }
        
        // Check if student already recorded attendance
        $checkStmt = $conn->prepare("SELECT attendance_id FROM session_attendance WHERE session_id = ? AND student_id = ? LIMIT 1");
        $checkStmt->bind_param("ii", $session_id, $student_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $checkStmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Attendance already recorded'];
        }
        $checkStmt->close();
        
        // Get student information
        $studentStmt = $conn->prepare("SELECT id, first_name, last_name, student_number FROM users WHERE id = ? LIMIT 1");
        $studentStmt->bind_param("i", $student_id);
        $studentStmt->execute();
        $studentResult = $studentStmt->get_result();
        
        if ($studentResult->num_rows === 0) {
            $studentStmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Student not found'];
        }
        
        $student = $studentResult->fetch_assoc();
        $studentStmt->close();
        
        // Record attendance
        $insertStmt = $conn->prepare("INSERT INTO session_attendance (session_id, student_id, student_name, student_number, pc_number, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $student_name = $student['first_name'] . ' ' . $student['last_name'];
        $insertStmt->bind_param("iisss", $session_id, $student_id, $student_name, $student['student_number'], $pc_number);
        $result = $insertStmt->execute();
        
        $insertStmt->close();
        $conn->close();
        
        if ($result) {
            return [
                'success' => true, 
                'message' => 'Attendance recorded successfully',
                'student_name' => $student_name,
                'student_number' => $student['student_number']
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to record attendance'];
        }
    }

    /**
     * Get attendance records for a session
     * @param int $session_id
     * @return array
     */
    public static function getBySession($session_id)
    {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT a.*
                FROM session_attendance a 
                JOIN users u ON a.student_id = u.id 
                WHERE a.session_id = ? 
                ORDER BY a.timestamp DESC");
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attendances = [];
        while ($row = $result->fetch_assoc()) {
            $attendances[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $attendances;
    }

    /**
     * Get attendance records with filters
     * @param string|null $date
     * @param string|null $subject
     * @param string|null $section
     * @return array
     */
    public static function getFiltered($date = null, $subject = null, $section = null)
    {
        $conn = getConnection();
        $conditions = [];
        $params = [];
        $types = '';
        
        if ($date) {
            $conditions[] = "DATE(a.timestamp) = ?";
            $params[] = $date;
            $types .= 's';
        }
        
        if ($subject) {
            $conditions[] = "s.subject = ?";
            $params[] = $subject;
            $types .= 's';
        }
        
        if ($section) {
            $conditions[] = "s.section = ?";
            $params[] = $section;
            $types .= 's';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT a.*, s.subject, s.section, s.pc_number, u.department 
                FROM session_attendance a 
                JOIN lab_sessions s ON a.session_id = s.session_id
                JOIN users u ON a.student_id = u.id 
                $whereClause
                ORDER BY a.timestamp DESC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $attendances = [];
        while ($row = $result->fetch_assoc()) {
            $attendances[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $attendances;
    }
}
?>

