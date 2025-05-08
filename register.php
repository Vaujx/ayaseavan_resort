<?php
require_once 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $contact_number = sanitize($_POST['contact_number']);
    $address = sanitize($_POST['address']);
    
    // Validate input
    if (strlen($username) < 4) {
        $error = "Username must be at least 4 characters long";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if username or email already exists
        $checkQuery = "SELECT * FROM users WHERE username = :username OR email = :email";
        $stmt = $conn->prepare($checkQuery);
        $stmt->execute([
            'username' => $username,
            'email' => $email
        ]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            if ($user['username'] === $username) {
                $error = "Username already exists";
            } else {
                $error = "Email already exists";
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // OPTION 1: Modify the SQL to not specify user_type (if it has a default value)
            $insertQuery = "INSERT INTO users (username, email, password, first_name, last_name, contact_number, address) 
                            VALUES (:username, :email, :password, :first_name, :last_name, :contact_number, :address)";
            
            // OPTION 2: If you need to specify a valid user_type value, uncomment this and use a known valid value
            // $insertQuery = "INSERT INTO users (username, email, password, first_name, last_name, contact_number, address, user_type) 
            //                VALUES (:username, :email, :password, :first_name, :last_name, :contact_number, :address, 'user')";
            
            $stmt = $conn->prepare($insertQuery);
            $params = [
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'contact_number' => $contact_number,
                'address' => $address
            ];
            
            try {
                if ($stmt->execute($params)) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Error creating account: " . implode(", ", $stmt->errorInfo());
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | AyaSeavan Resort</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="register-container">
        <h2>Create an Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo $success; ?>
                <p><a href="login.php" class="btn primary">Login Now</a></p>
            </div>
        <?php else: ?>
            <form action="register.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name*</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name*</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username*</label>
                    <input type="text" id="username" name="username" required>
                    <small>Must be at least 4 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password*</label>
                        <input type="password" id="password" name="password" required>
                        <small>Must be at least 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password*</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn primary">Register</button>
                
                <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
