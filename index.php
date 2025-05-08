<?php
require_once 'config.php';

// Check if site is in maintenance mode
$maintenanceMode = isMaintenanceMode();
$maintenanceMessage = '';

if ($maintenanceMode && !isAdmin()) {
    $maintenanceMessage = getMaintenanceMessage();
}

// Get featured rooms
$roomsQuery = "SELECT * FROM rooms LIMIT 3";
$stmt = $conn->query($roomsQuery);
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AyaSeavan Beach Resort</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php if ($maintenanceMode && !isAdmin()): ?>
        <!-- Maintenance Mode Banner -->
        <div class="maintenance-banner">
            <div class="maintenance-content">
                <h1>🛠️ Site Under Maintenance</h1>
                <p><?php echo htmlspecialchars($maintenanceMessage); ?></p>
                <?php if (isAdmin()): ?>
                    <p class="admin-notice">You're seeing this page because you're logged in as an admin. Regular users will only see the maintenance message.</p>
                    <a href="admin-settings.php#maintenance" class="btn primary">Disable Maintenance Mode</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Header Section -->
        <div class="header">
            <div class="overlay">
                <div class="nav-container">
                    <div class="nav-links">
                        <a href="index.php" class="active">Home</a>
                        <a href="rooms.php">Rooms</a>
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
                    <h1>AyaSeavan <span id="admin-trigger">Beach</span> Resort</h1>
                    <p>Experience the SUN AND FUN at AyaSeavan Beach Resort.<br>Reward yourself with rest and relaxation that you truly deserve.</p>
                    
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

        <!-- Best Rooms Section -->
        <section class="rooms-section">
            <h2>Our Best Rooms</h2>
            <div class="room-container">
                <?php foreach ($rooms as $room): ?>
                    <div class="room-card">
                        <img src="assets/images/<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>">
                        <h3><?php echo htmlspecialchars($room['room_type']); ?></h3>
                        <p>₱<?php echo number_format($room['price_overnight'], 2); ?> Overnight</p>
                        <p>₱<?php echo number_format($room['price_daytour'], 2); ?> DayTour</p>
                        <p><?php echo htmlspecialchars($room['description']); ?></p>
                        <?php if (!$maintenanceMode): ?>
                            <a href="booking.php?room=<?php echo $room['room_id']; ?>" class="btn primary">Book Now</a>
                        <?php else: ?>
                            <span class="btn disabled">Booking Disabled</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all">
                <a href="rooms.php" class="btn primary">View All Rooms</a>
            </div>
        </section>

        <!-- Activities Section -->
        <section class="activities-section">
            <h2>Fun Activities</h2>
            <div class="activity-content">
                <div class="activity-text">
                    <p>Enjoy a wide range of beach activities at AyaSeavan Beach Resort! From thrilling water sports to relaxing sunset cruises, we have something for everyone.</p>
                    <ul>
                        <li>🏖️ Beach Volleyball</li>
                        <li>🔥 Bonfire Nights</li>
                        <li>🏊 Swimming</li>
                        <li>🎣 Fishing</li>
                    </ul>
                </div>
                <div class="activity-image">
                    <img src="assets/images/activity.jpg" alt="Beach Activities">
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact-section">
            <h2>Contact Us</h2>
            <div class="contact-info">
                <p><strong>📍 Address:</strong> AyaSeavan Beach Resort, Bani, Masinloc, Zambales, Philippines</p>
                <p><strong>📞 Phone:</strong> +63 951 056 8188</p>
                <p><strong>📧 Email:</strong> fajardokire@gmail.com</p>
            </div>
        </section>

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
    <?php endif; ?>

    <!-- Admin Login Modal -->
    <div id="adminLoginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Admin Login</h2>
            <div id="admin-login-error" class="error-message" style="display: none;"></div>
            <form id="admin-login-form">
                <div class="form-group">
                    <label for="admin-username">Username</label>
                    <input type="text" id="admin-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="admin-password">Password</label>
                    <input type="password" id="admin-password" name="password" required>
                </div>
                <button type="submit" class="btn primary">Login</button>
            </form>
        </div>
    </div>

    <!-- Add maintenance mode styles -->
    <style>
        .maintenance-banner {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .maintenance-content {
            max-width: 600px;
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .maintenance-content h1 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .maintenance-content p {
            margin-bottom: 20px;
            font-size: 1.1rem;
            color: #555;
            line-height: 1.6;
        }
        
        .admin-notice {
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
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

        /* Admin trigger styling */
        #admin-trigger {
            cursor: pointer;
            position: relative;
            display: inline-block;
        }
        
        #admin-trigger:hover {
            text-decoration: underline;
            color: #f8f9fa;
        }
    </style>

    <script>
        // Admin login modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const adminTrigger = document.getElementById('admin-trigger');
            const adminModal = document.getElementById('adminLoginModal');
            const closeBtn = adminModal.querySelector('.close');
            const adminForm = document.getElementById('admin-login-form');
            const errorMessage = document.getElementById('admin-login-error');
            
            // Open modal when clicking on "Beach"
            if (adminTrigger) {
                adminTrigger.addEventListener('click', function() {
                    adminModal.style.display = 'block';
                });
            }
            
            // Close modal when clicking on X
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    adminModal.style.display = 'none';
                });
            }
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === adminModal) {
                    adminModal.style.display = 'none';
                }
            });
            
            // Handle form submission
            if (adminForm) {
                adminForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const username = document.getElementById('admin-username').value;
                    const password = document.getElementById('admin-password').value;
                    
                    // Check credentials (hardcoded for this example)
                    if (username === 'admin' && password === '123') {
                        // Send AJAX request to set admin session
                        fetch('admin-login-ajax.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'username=' + encodeURIComponent(username) + '&password=' + encodeURIComponent(password)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = 'dashboard.php';
                            } else {
                                errorMessage.textContent = data.message;
                                errorMessage.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            errorMessage.textContent = 'An error occurred. Please try again.';
                            errorMessage.style.display = 'block';
                        });
                    } else {
                        errorMessage.textContent = 'Invalid username or password';
                        errorMessage.style.display = 'block';
                    }
                });
            }
        });
    </script>
</body>
</html>