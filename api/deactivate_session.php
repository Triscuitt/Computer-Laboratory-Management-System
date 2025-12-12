<?php

require_once '../dbconnection.php';
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

// Only faculty and admin can deactivate sessions
if ($user->getRole() !== 'faculty' && $user->getRole() !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only faculty can deactivate sessions']);
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

if (!isset($data['session_id']) || empty($data['session_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit();
}

$session_id = intval($data['session_id']);

// Verify ownership (unless admin)
if ($user->getRole() !== 'Admin') {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT faculty_id FROM lab_sessions WHERE session_id = ? LIMIT 1");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $session = $result->fetch_assoc();
    $stmt->close();
    
    if (!$session) {
        $conn->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Session not found']);
        exit();
    }
    
    // Get user ID
    $userStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $userStmt->bind_param("s", $user->getEmail());
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userData = $userResult->fetch_assoc();
    $userStmt->close();
    $conn->close();
    
    if ($userData['id'] != $session['faculty_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only deactivate your own sessions']);
        exit();
    }
}

// Deactivate session
$result = Session::deactivate($session_id);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Session deactivated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to deactivate session']);
}
?>

