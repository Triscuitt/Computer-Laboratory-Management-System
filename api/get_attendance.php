<?php

require_once '../dbconnection.php';
require_once '../model/attendance.php';
require_once '../model/session.php';
require_once '../model/user.php';
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedInUser']) || !isset($_SESSION['User'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user = $_SESSION['User'];

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get session_id from query parameter
if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit();
}

$session_id = intval($_GET['session_id']);

// Check if user has permission to view this session's attendance
// Faculty can view their own sessions, Admin can view all
$conn = getConnection();
$sessionStmt = $conn->prepare("SELECT faculty_id FROM lab_sessions WHERE session_id = ? LIMIT 1");
$sessionStmt->bind_param("i", $session_id);
$sessionStmt->execute();
$sessionResult = $sessionStmt->get_result();
$sessionData = $sessionResult->fetch_assoc();
$sessionStmt->close();

if (!$sessionData) {
    $conn->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Session not found']);
    exit();
}

// Check permissions
$userStmt = $conn->prepare("SELECT id, role FROM users WHERE email = ? LIMIT 1");
$email = $user->getEmail();
$userStmt->bind_param("s", $email);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();
$userStmt->close();
$conn->close();

if ($userData['role'] !== 'Admin' && $userData['id'] != $sessionData['faculty_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to view this session']);
    exit();
}

// Get attendance records
$attendances = Attendance::getBySession($session_id);

echo json_encode([
    'success' => true,
    'attendances' => $attendances,
    'count' => count($attendances)
]);
?>

