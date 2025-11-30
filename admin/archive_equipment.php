<?php


session_start();
require_once '../config/db_connect.php';


if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'technician')) {
    header("Location: ../index.php");
    exit();
}

$equipment_id = $_GET['id'] ?? null;

if ($equipment_id && is_numeric($equipment_id)) {

    $sql = "UPDATE equipment SET status = 'Pulled out' WHERE equipment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $equipment_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {

        $_SESSION['success_message'] = "Equipment ID {$equipment_id} has been archived (Status set to 'Pulled out').";
    } else {
        $_SESSION['error_message'] = "Error archiving equipment or equipment not found. " . $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Invalid equipment ID provided for archiving.";
}

$conn->close();

header("Location: inventory.php");
exit();
