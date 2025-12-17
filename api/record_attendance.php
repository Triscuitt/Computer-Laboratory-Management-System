<?php

require_once '../dbconnection.php';
require_once '../model/attendance.php';
require_once '../model/session.php';
require_once '../model/user.php';

session_start();

header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedInUser']) || !isset($_SESSION['User'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit();
}

$user = $_SESSION['User'];
if ($user->getRole() !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only students can record attendance']);
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

// Check if session_code is provided
if (!isset($data['session_code']) || empty($data['session_code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session code is required']);
    exit();
}

$session_code = trim($data['session_code']);

// Get session by code
$session = Session::getByCode($session_code);

if (!$session) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Session not found or expired']);
    exit();
}

// Get student ID from session
$conn = getConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$email = $user->getEmail();
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$student) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Student user not found']);
    exit();
}

$student_id = $student['id'];

// Record attendance
$result = Attendance::record($session['session_id'], $student_id, $data['pc_number']);

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
}
?>

