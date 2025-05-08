<?php
// PostgreSQL database connection configuration
$host = "dpg-d0e7ii8dl3ps73beip30-a";  // Use the service name from docker-compose
$port = "5432";      // Default PostgreSQL port
$dbname = "ayaseavan_db";
$username = "root";
$password = "E1dwmfS2vvJgOnhj5cypZe3o55MWVcLj"; // Change this in production

// Create PDO connection
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";
    $conn = new PDO($dsn);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in as admin
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: login.php?error=unauthorized");
        exit();
    }
}

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

// Function to check if the site is in maintenance mode
function isMaintenanceMode() {
    global $conn;
    
    $query = "SELECT setting_value FROM settings WHERE setting_name = 'maintenance_mode'";
    $stmt = $conn->query($query);
    
    if ($stmt && $row = $stmt->fetch()) {
        return $row['setting_value'] === 'on';
    }
    
    return false;
}

// Function to get maintenance message
function getMaintenanceMessage() {
    global $conn;
    
    $query = "SELECT setting_value FROM settings WHERE setting_name = 'maintenance_message'";
    $stmt = $conn->query($query);
    
    if ($stmt && $row = $stmt->fetch()) {
        return $row['setting_value'];
    }
    
    return 'Our website is currently undergoing scheduled maintenance. We should be back shortly. Thank you for your patience.';
}

// Ensure admin user exists in the database
function ensureAdminExists() {
    global $conn;
    
    $query = "SELECT * FROM users WHERE username = 'admin' AND user_type = 'admin' LIMIT 1";
    $stmt = $conn->query($query);
    
    if ($stmt->rowCount() == 0) {
        // Admin doesn't exist, create it
        $hashedPassword = password_hash('123', PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password, email, first_name, last_name, user_type) 
                  VALUES ('admin', :password, 'admin@ayaseavan.com', 'Admin', 'User', 'admin')";
        $stmt = $conn->prepare($query);
        $stmt->execute(['password' => $hashedPassword]);
    }
}

// Call this function to ensure admin exists
ensureAdminExists();
?>