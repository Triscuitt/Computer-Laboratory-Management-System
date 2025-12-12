<?php
session_start();
require_once "../dbconnection.php";
$conn = getConnection();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technician'])) {
    header("Location: ../index.php");
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['equipment_error'] = "Invalid equipment ID.";
    header("Location: inventory.php");
    exit();
}

// Get equipment name first
$get_name = $conn->prepare("SELECT name FROM equipment WHERE equipment_id = ?");
$get_name->bind_param("i", $id);
$get_name->execute();
$result = $get_name->get_result();
$equipment_name = $result->num_rows > 0 ? $result->fetch_assoc()['name'] : 'Equipment';
$get_name->close();

// Update status
$stmt = $conn->prepare("UPDATE equipment SET status = 'Pulled out' WHERE equipment_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['equipment_success'] = "Equipment '$equipment_name' marked as PULLED OUT.";
} else {
    $_SESSION['equipment_error'] = "Failed to update status.";
}
$stmt->close();

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'inventory.php'));
exit();
