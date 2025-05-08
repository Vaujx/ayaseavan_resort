<?php
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get username and password from POST data
$username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Check credentials (hardcoded for this example)
if ($username === 'admin' && $password === '123') {
    // Get admin user from database or create a session directly
    $adminQuery = "SELECT * FROM users WHERE username = 'admin' AND user_type = 'admin' LIMIT 1";
    $stmt = $conn->prepare($adminQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Admin exists in database, set session from database record
        $admin = $stmt->fetch();
        $_SESSION['user_id'] = $admin['user_id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['user_type'] = $admin['user_type'];
        
        echo json_encode(['success' => true]);
    } else {
        // Admin doesn't exist in database, create a temporary session
        // In a real application, you might want to check if the admin exists in the database
        $_SESSION['user_id'] = 1; // Assuming admin has ID 1
        $_SESSION['username'] = 'admin';
        $_SESSION['user_type'] = 'admin';
        
        echo json_encode(['success' => true]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}
?>