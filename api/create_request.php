<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../dbconnection.php';
require_once '../model/request.php';
require_once '../model/user.php';
session_start();

header('Content-Type: application/json');

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'PHP Fatal Error: ' . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line'],
            'type' => $error['type']
        ]);
        exit();
    }
});

// Check if user is logged in and is faculty
if (!isset($_SESSION['loggedInUser']) || !isset($_SESSION['User'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user = $_SESSION['User'];
if ($user->getRole() !== 'faculty' && $user->getRole() !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only faculty can create sessions']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user->getEmail());
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$faculty) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Faculty user not found']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$required = ['title','type', 'priority', 'description'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

$title = trim($data['title']);
$type = $data['type'];
$priority = $data['priority'];
$description = trim($data['description']);
$faculty_id = $faculty['id'];

try{
    $request = new Request($title, $type, $priority, $description, $faculty_id);
    $request_id = $request->create();

    if (!$request_id) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create request. Please check database connection and table structure.',
            'hint' => 'Make sure request table exists.'
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Request created successfully',
        'request' => [
            'request_id' => $request_id,
            'request_title' => $title,
            'request_type' => $type,
            'request_priority' => $priority,
            'request_description' => $description,
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error creating request: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'PHP Error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
}

?>