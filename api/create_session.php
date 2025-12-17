<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../dbconnection.php';
require_once '../model/session.php';
require_once '../model/user.php';
session_start();

header('Content-Type: application/json');

// Catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'PHP Fatal Error: ' . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line'],
            'type' => $error['type']
        ]);
        exit();
    }
});

// Check if user is logged in and is faculty
if (!isset($_SESSION['loggedInUser']) || !isset($_SESSION['User'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user = $_SESSION['User'];
if ($user->getRole() !== 'faculty' && $user->getRole() !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only faculty can create sessions']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['subject', 'section', 'duration_minutes'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

$subject = trim($data['subject']);
$section = trim($data['section']);
$lab_name = isset($data['lab_name']) ? trim($data['lab_name']) : null;
$duration_minutes = intval($data['duration_minutes']);

// Validate duration
if ($duration_minutes < 1 || $duration_minutes > 120) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Duration must be between 1 and 120 minutes']);
    exit();
}

// Get faculty ID from session
$conn = getConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user->getEmail());
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$faculty) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Faculty user not found']);
    exit();
}

$faculty_id = $faculty['id'];

try {
    // Create session
    $session = new Session($faculty_id, $subject, $section, $lab_name, $duration_minutes);
    $session_id = $session->create();

    if (!$session_id) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create session. Please check database connection and table structure.',
            'hint' => 'Make sure lab_sessions table exists. Run database_schema.sql if needed.'
        ]);
        exit();
    }

    // Get session code
    $session_code = $session->getSessionCode();
    
    if (!$session_code) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Session created but session code is missing'
        ]);
        exit();
    }

    // Get the created session data directly from database instead of getByCode
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM lab_sessions WHERE session_id = ? LIMIT 1");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $session_data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    if (!$session_data) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Session created but could not retrieve session data',
            'session_id' => $session_id
        ]);
        exit();
    }
    
    // Generate QR code URL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go from /api/create_session.php to root
    $base_url = $protocol . "://" . $host . $script_path;
    $qr_url = $base_url . "/pages/scan_qr.php?code=" . urlencode($session_code);
    
    echo json_encode([
        'success' => true,
        'message' => 'Session created successfully',
        'session' => [
            'session_id' => $session_id,
            'session_code' => $session_code,
            'subject' => $subject,
            'section' => $section,
            'lab_name' => $lab_name,
            'duration_minutes' => $duration_minutes,
            'expires_at' => $session_data['expires_at'],
            'qr_url' => $qr_url
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error creating session: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'PHP Error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}
?>

