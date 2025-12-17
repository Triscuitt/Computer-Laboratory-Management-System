<?php
require_once '../dbconnection.php';
require_once '../model/borrow.php';
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

// Get user ID
$conn = getConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$email = $user->getEmail();
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$userData) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user_id = $userData['id'];

$items = Borrow::getAllItems($user_id);

echo json_encode([
    'success' => true,
    'items' => $items,
    'count' => count($items)
]);
?>