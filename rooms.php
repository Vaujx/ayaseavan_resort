<?php
require_once 'config.php';

// Check if site is in maintenance mode
$maintenanceMode = isMaintenanceMode();

// Get all rooms
$roomsQuery = "SELECT * FROM rooms";
$stmt = $conn->query($roomsQuery);
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - AyaSeavan Beach Resort</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header Section -->
    <div class="header rooms-header">
        <div class="overlay">
            <div class="nav-container">
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="rooms.php" class="active">Rooms</a>
                    <a href="index.php#contact">Contact</a>
                    <?php if (isAdmin()): ?>
                        <a href="dashboard.php">Admin Dashboard</a>
                    <?php endif; ?>
                </div>
                <div class="auth-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="btn">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn">Login</a>
                    <?php endif; ?>
                    <?php if (!$maintenanceMode): ?>
                        <a href="booking.php" class="btn primary">Book Now</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="header-text">
                <h1>Our Rooms</h1>
                <p>Find the perfect accommodation for your stay at AyaSeavan Beach Resort.</p>
                
                <?php if ($maintenanceMode): ?>
                    <div class="maintenance-notice">
                        <p>⚠️ Booking is currently disabled due to maintenance.</p>
                        <?php if (isAdmin()): ?>
                            <a href="admin-settings.php#maintenance" class="btn">Manage Maintenance Mode</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Room Sections -->
    <?php foreach ($rooms as $room): ?>
        <section class="room-section">
            <h2><?php echo htmlspecialchars($room['room_type']); ?></h2>
            <div class="room-content">
                <img src="assets/images/<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>">
                <div class="room-details">
                    <p>₱<?php echo number_format($room['price_overnight'], 2); ?> Overnight</p>
                    <p>₱<?php echo number_format($room['price_daytour'], 2); ?> DayTour</p>
                    <p><?php echo htmlspecialchars($room['description']); ?></p>
                    
                    <h3>Features:</h3>
                    <ul>
                        <?php 
                        $features = explode(',', $room['features']);
                        foreach ($features as $feature): 
                            $feature = trim($feature);
                            if (!empty($feature)):
                        ?>
                            <li>✔ <?php echo htmlspecialchars($feature); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                    
                    <?php if (!$maintenanceMode): ?>
                        <a href="booking.php?room=<?php echo $room['room_id']; ?>" class="btn primary">Book Now</a>
                    <?php else: ?>
                        <span class="btn disabled">Booking Disabled</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="footer-overlay">
            <div class="footer-about">
                <h3>WHO WE ARE</h3>
                <p>AyaSeavan Beach Resort is your ultimate beach getaway in Zambales, offering tranquility, relaxation, and adventure.</p>
            </div>
            <p class="copyright">2025 AyaSeavan Beach Resort!</p>
        </div>
    </footer>

    <!-- Add maintenance mode styles -->
    <style>
        .maintenance-notice {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }
        
        .btn.disabled {
            background-color: #e0e0e0;
            color: #888;
            cursor: not-allowed;
        }
    </style>
</body>
</html>