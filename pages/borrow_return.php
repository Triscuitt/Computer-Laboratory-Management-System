<?php
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$borrow_id = (int)($_GET['id'] ?? 0);

if ($borrow_id <= 0) {
    $_SESSION['borrow_error'] = "Invalid borrow record.";
    header("Location: borrow.php");
    exit();
}

// Get borrow details
$stmt = $conn->prepare("SELECT equipment_id FROM borrow WHERE borrow_id = ?");
$stmt->bind_param("i", $borrow_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['borrow_error'] = "Borrow record not found.";
    header("Location: borrow.php");
    exit();
}

$borrow = $result->fetch_assoc();
$equipment_id = $borrow['equipment_id'];
$stmt->close();

// Update borrow status to Returned and set return date
$update_borrow = $conn->prepare("UPDATE borrow SET status = 'Returned', return_date = NOW() WHERE borrow_id = ?");
$update_borrow->bind_param("i", $borrow_id);

if ($update_borrow->execute()) {
    // Update equipment status back to Available
    $update_equip = $conn->prepare("UPDATE equipment SET status = 'Available' WHERE equipment_id = ?");
    $update_equip->bind_param("i", $equipment_id);
    $update_equip->execute();
    $update_equip->close();

    $_SESSION['borrow_success'] = "Item marked as RETURNED successfully!";
} else {
    $_SESSION['borrow_error'] = "Failed to update return status.";
}

$update_borrow->close();
$conn->close();

header("Location: borrow.php");
exit();
