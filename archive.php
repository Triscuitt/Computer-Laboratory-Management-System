<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);
$archived_by = $_SESSION['user_id'] ?? 1;

if ($id <= 0 || !in_array($type, ['user', 'borrow'])) {
    if ($type === 'borrow') {
        $_SESSION['borrow_error'] = "Invalid action.";
        header("Location: borrow.php");
    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: users.php");
    }
    exit();
}

$conn->begin_transaction();

try {
    if ($type === 'user') {
        // Move user to archive
        $stmt = $conn->prepare("INSERT INTO archive_users 
            SELECT *, ?, NOW() FROM users WHERE id = ?");
        $stmt->bind_param("ii", $archived_by, $id);
        $stmt->execute();

        // Delete from main users table (only if not admin)
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $_SESSION['success'] = "User archived successfully!";
        $redirect = "users.php";
    } elseif ($type === 'borrow') {
        // 1. Get borrow record details
        $stmt = $conn->prepare("SELECT * FROM borrow WHERE borrow_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Borrow record not found.");
        }

        $borrow = $result->fetch_assoc();
        $stmt->close();

        // 2. Copy to archive_borrow table
        $archive_stmt = $conn->prepare("
            INSERT INTO archive_borrow 
            (borrow_id, user_id, equipment_id, borrow_date, return_date, status, archived_by, archived_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $archive_stmt->bind_param(
            "iiisssi",
            $borrow['borrow_id'],
            $borrow['user_id'],
            $borrow['equipment_id'],
            $borrow['borrow_date'],
            $borrow['return_date'],
            $borrow['status'],
            $archived_by
        );

        if (!$archive_stmt->execute()) {
            throw new Exception("Failed to create archive record.");
        }
        $archive_stmt->close();

        // 3. If item was still borrowed, set equipment back to Available
        if ($borrow['status'] === 'Borrowed' || $borrow['status'] === 'Overdue') {
            $update_equip = $conn->prepare("UPDATE equipment SET status = 'Available' WHERE equipment_id = ?");
            $update_equip->bind_param("i", $borrow['equipment_id']);
            $update_equip->execute();
            $update_equip->close();
        }

        // 4. Delete from main borrow table
        $delete_stmt = $conn->prepare("DELETE FROM borrow WHERE borrow_id = ?");
        $delete_stmt->bind_param("i", $id);

        if (!$delete_stmt->execute()) {
            throw new Exception("Failed to delete borrow record.");
        }
        $delete_stmt->close();

        $_SESSION['borrow_success'] = "Borrow record archived successfully!";
        $redirect = "borrow.php";
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    if ($type === 'borrow') {
        $_SESSION['borrow_error'] = "Archive failed: " . $e->getMessage();
        $redirect = "borrow.php";
    } else {
        $_SESSION['error'] = "Archive failed.";
        $redirect = "users.php";
    }
}

header("Location: $redirect");
exit();
