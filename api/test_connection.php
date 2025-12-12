<?php
// Simple test endpoint to verify API is accessible

require_once '../dbconnection.php';
require_once '../model/user.php';
session_start();
header('Content-Type: application/json');

try {
    $conn = getConnection();
    
    // Test database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'lab_sessions'");
    $table_exists = $result && $result->num_rows > 0;
    
    // Check if attendance table exists
    $result2 = $conn->query("SHOW TABLES LIKE 'session_attendance'");
    $attendance_table_exists = $result2 && $result2->num_rows > 0;
    
    // Get user role if available
    $user_role = 'not set';
    if (isset($_SESSION['User'])) {
        $user_role = $_SESSION['User']->getRole();
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'API is accessible',
        'database_connected' => true,
        'lab_sessions_table_exists' => $table_exists,
        'session_attendance_table_exists' => $attendance_table_exists,
        'session_active' => isset($_SESSION['loggedInUser']),
        'user_role' => $user_role
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

