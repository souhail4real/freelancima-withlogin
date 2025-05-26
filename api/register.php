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
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validate input
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'All fields are required']);
    exit;
}

if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Passwords do not match']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

// Register the user
$result = registerUser($username, $email, $password);

if ($result['success']) {
    echo json_encode(['error' => false, 'message' => $result['message']]);
} else {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => $result['message']]);
}
?>