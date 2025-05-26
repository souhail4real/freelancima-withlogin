<?php
require_once 'config.php';

/**
 * Registers a new user
 * @param string $username Username
 * @param string $email Email
 * @param string $password Password (plain text)
 * @return array Result with success status and message
 */
function registerUser($username, $email, $password) {
    $conn = createConnection();
    
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Registration successful'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Authenticates a user
 * @param string $username Username
 * @param string $password Password (plain text)
 * @return array Result with success status, message, and user data if successful
 */
function loginUser($username, $password) {
    $conn = createConnection();
    
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Remove password from user data
                unset($user['password']);
                
                return [
                    'success' => true, 
                    'message' => 'Login successful',
                    'user' => $user
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid username or password'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
    }
}
?>