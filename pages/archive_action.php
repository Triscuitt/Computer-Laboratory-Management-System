<?php
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Only admin can archive
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admin privileges required.";
    header("Location: ../index.php");
    exit();
}

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);
$archived_by = $_SESSION['user_id'] ?? 1; // Default to 1 if not set

// Validation
if ($id <= 0 || $type !== 'user') {
    $_SESSION['error'] = "Invalid archive request.";
    header("Location: users.php");
    exit();
}

// Prevent self-archiving
if (isset($_SESSION['user_id']) && $id === $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot archive your own account.";
    header("Location: users.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Check if user exists and get details
    $check = $conn->prepare("SELECT id, role, CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("User not found.");
    }

    $user = $result->fetch_assoc();
    $userName = $user['full_name'];

    // Prevent archiving admin accounts
    if ($user['role'] === 'admin') {
        throw new Exception("Cannot archive administrator accounts.");
    }
    $check->close();

    // 2. Copy user data to archive_users table
    $archive_stmt = $conn->prepare("
        INSERT INTO archive_users 
        (id, student_number, first_name, middle_name, last_name, suffix,
         username, email, password, role, account_status, created_at,
         archived_by, archived_at)
        SELECT 
            id, student_number, first_name, middle_name, last_name, suffix,
            username, email, password, role, account_status, created_at,
            ?, NOW()
        FROM users 
        WHERE id = ?
    ");
    $archive_stmt->bind_param("ii", $archived_by, $id);

    if (!$archive_stmt->execute()) {
        throw new Exception("Failed to create archive record: " . $conn->error);
    }
    $archive_stmt->close();

    // 3. Delete user from main users table
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $id);

    if (!$delete_stmt->execute()) {
        throw new Exception("Failed to delete user: " . $conn->error);
    }

    if ($delete_stmt->affected_rows === 0) {
        throw new Exception("No user was deleted. User may not exist.");
    }
    $delete_stmt->close();

    // SUCCESS - Commit transaction
    $conn->commit();
    $_SESSION['success'] = "User '$userName' has been successfully archived!";
} catch (Exception $e) {
    // FAILURE - Rollback all changes
    $conn->rollback();
    $_SESSION['error'] = "Archive failed: " . $e->getMessage();
}

$conn->close();
header("Location: users.php");
exit();
