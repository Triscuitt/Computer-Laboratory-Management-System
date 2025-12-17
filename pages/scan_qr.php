<?php

require_once '../dbconnection.php';
require_once '../model/session.php';
require_once '../model/attendance.php';
require_once '../model/user.php';
require_once '../utilities/utils.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedInUser']) || !isset($_SESSION['User'])) {
    headto('login.php');
    exit();
}

$user = $_SESSION['User'];

// Only students can scan QR codes
if ($user->getRole() !== 'student') {
    headto('main.php');
    exit();
}

// Get session code from URL
$session_code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($session_code)) {
    $_SESSION['alertMessage'] = 'Invalid QR code';
    headto('main.php');
    exit();
}

// Get session
$session = Session::getByCode($session_code);

if (!$session) {
    $_SESSION['alertMessage'] = 'Session not found or has expired';
    headto('main.php');
    exit();
}

// Get student ID
$conn = getConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user->getEmail());
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$student) {
    $_SESSION['alertMessage'] = 'Student account not found';
    headto('main.php');
    exit();
}

// Record attendance
$attendance_result = Attendance::record($session['session_id'], $student['id'], '');

// Set message and redirect
if ($attendance_result['success']) {
    $_SESSION['alertMessage'] = 'Attendance recorded successfully!';
} else {
    $_SESSION['alertMessage'] = $attendance_result['message'];
}

headto('main.php');
exit();
