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
$role = $user->getRole();

// Get sessions based on role
$sessions = Session::getAllArchived();

// Generate QR URLs for each session
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Go from /api/get_sessions.php to root
$base_url = $protocol . "://" . $host . $script_path;

foreach ($sessions as $session) {
    $session['qr_url'] = $base_url . "/pages/scan_qr.php?code=" . urlencode($session['session_code']);
    
    // Calculate time remaining
    $expires = strtotime($session['expires_at']);
    $now = time();
    $remaining = $expires - $now;
    
    if ($remaining > 0) {
        $hours = floor($remaining / 3600);
        $minutes = floor(($remaining % 3600) / 60);
        $seconds = $remaining % 60;
        $session['time_remaining'] = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        $session['time_remaining_seconds'] = $remaining;
    } else {
        $session['time_remaining'] = '00:00:00';
        $session['time_remaining_seconds'] = 0;
    }
}

echo json_encode([
    'success' => true,
    'sessions' => $sessions,
    'count' => count($sessions)
]);
?>