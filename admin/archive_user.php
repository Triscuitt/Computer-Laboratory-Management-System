<?php
// admin/archive_user.php

session_start();
require_once '../config/db_connect.php';

// Security Check: Only Admins can archive users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_GET['id'] ?? null;

if ($user_id && is_numeric($user_id)) {

    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "You cannot archive your own account while logged in!";
    } else {

        $sql = "UPDATE users SET is_archived = 1 WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "User ID {$user_id} has been successfully **Archived** (soft deleted).";
        } else {
            $_SESSION['error_message'] = "Error archiving user or user not found. " . $conn->error;
        }
        $stmt->close();
    }
} else {
    $_SESSION['error_message'] = "Invalid user ID provided for archiving.";
}

$conn->close();


header("Location: users.php");
exit();
