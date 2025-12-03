<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if ($id <= 0 || !in_array($type, ['user', 'equipment'])) {
    $_SESSION['error'] = "Invalid action.";
    header("Location: inventory.php");
    exit();
}

$table = $type === 'user' ? 'users' : 'equipment';

// Base query — always works
$sql = "UPDATE $table SET is_archived = 1";
$params = [];
$types = '';

// Check columns and add only if they exist
$has_archived_by = $conn->query("SHOW COLUMNS FROM $table LIKE 'archived_by'")->num_rows > 0;
$has_archived_at = $conn->query("SHOW COLUMNS FROM $table LIKE 'archived_at'")->num_rows > 0;

if ($has_archived_by) {
    $sql .= ", archived_by = ?";
    $params[] = $_SESSION['user_id'] ?? 1;
    $types .= 'i';
}
if ($has_archived_at) {
    $sql .= ", archived_at = NOW()";
}

$sql .= " WHERE id = ?";
$params[] = $id;
$types .= 'i';

// Now bind correctly
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = ucfirst($type) . " archived successfully!";
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'inventory.php'));
exit();
