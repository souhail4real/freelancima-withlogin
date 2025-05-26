<?php
require_once 'config.php';
require_once 'auth.php';

// Set headers
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate input
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Username and password are required']);
    exit;
}

// Authenticate the user
$result = loginUser($username, $password);

if ($result['success']) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Store user data in session
    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['username'] = $result['user']['username'];
    
    echo json_encode([
        'error' => false, 
        'message' => $result['message'],
        'user' => [
            'id' => $result['user']['id'],
            'username' => $result['user']['username']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => true, 'message' => $result['message']]);
}
?>