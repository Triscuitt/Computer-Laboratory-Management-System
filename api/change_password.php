<?php

require_once '../utilities/validation.php';
require_once '../model/user.php';

session_start();

header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedInUser']) || !isset($_SESSION['User'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
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
if (!isset($data['new_password']) || empty($data['new_password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password is required']);
    exit();
}

if(!validateNewPassword($data['new_password'])){
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password not valid']);
    exit();
}

// Record attendance
$result = User::setPassword($data['new_password'],$_SESSION['User']->getEmail());

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
}
?>

