<?php
require_once 'config.php';

// Check if site is in maintenance mode
$maintenanceMode = isMaintenanceMode();

// If in maintenance mode and not admin, redirect to home
if ($maintenanceMode && !isAdmin()) {
    header("Location: index.php");
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit();
}

$userId = $_SESSION['user_id'];
$error = "";
$success = "";

// Get all rooms for selection
$roomsQuery = "SELECT * FROM rooms WHERE status = 'available'";
$stmt = $conn->query($roomsQuery);
$rooms = $stmt->fetchAll();

// Pre-select room if specified in URL
$selectedRoomId = isset($_GET['room']) ? (int)$_GET['room'] : 0;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $roomId = sanitize($_POST['room_id']);
    $checkInDate = sanitize($_POST['check_in_date']);
    $checkOutDate = sanitize($_POST['check_out_date']);
    $bookingType = sanitize($_POST['booking_type']);
    $numGuests = (int)sanitize($_POST['num_guests']);
    
    // Validate dates
    $today = date('Y-m-d');
    if ($checkInDate < $today) {
        $error = "Check-in date cannot be in the past";
    } elseif ($checkOutDate <= $checkInDate) {
        $error = "Check-out date must be after check-in date";
    } else {
        // Get room details
        $roomQuery = "SELECT * FROM rooms WHERE room_id = :roomId";
        $stmt = $conn->prepare($roomQuery);
        $stmt->execute(['roomId' => $roomId]);
        
        if ($stmt->rowCount() == 1) {
            $room = $stmt->fetch();
            
            // Calculate total amount
            $checkIn = new DateTime($checkInDate);
            $checkOut = new DateTime($checkOutDate);
            $interval = $checkIn->diff($checkOut);
            $numDays = $interval->days;
            
            if ($bookingType == 'overnight') {
                $totalAmount = $room['price_overnight'] * $numDays;
            } else {
                $totalAmount = $room['price_daytour'];
            }
            
            // Insert booking
            $insertQuery = "INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, 
                            booking_type, total_amount, num_guests, status, payment_status) 
                            VALUES (:userId, :roomId, :checkInDate, :checkOutDate, 
                            :bookingType, :totalAmount, :numGuests, 'pending', 'unpaid')";
            
            $stmt = $conn->prepare($insertQuery);
            $params = [
                'userId' => $userId,
                'roomId' => $roomId,
                'checkInDate' => $checkInDate,
                'checkOutDate' => $checkOutDate,
                'bookingType' => $bookingType,
                'totalAmount' => $totalAmount,
                'numGuests' => $numGuests
            ];
            
            if ($stmt->execute($params)) {
                $success = "Booking successful! Your reservation is pending confirmation.";
            } else {
                $error = "Error creating booking: " . implode(", ", $stmt->errorInfo());
            }
        } else {
            $error = "Invalid room selection";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - AyaSeavan Beach Resort</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header Section -->
    <div class="header booking-header">
        <div class="overlay">
            <div class="nav-container">
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="rooms.php">Rooms</a>
                    <a href="index.php#contact">Contact</a>
                </div>
                <div class="auth-buttons">
                    <a href="logout.php" class="btn">Logout</a>
                </div>
            </div>
            <div class="header-text">
                <h1>Book Your Stay</h1>
                <p>Reserve your perfect getaway at AyaSeavan Beach Resort.</p>
            </div>
        </div>
    </div>
    
    <!-- Booking Form Section -->
    <section class="booking-section">
        <div class="booking-container">
            <h2>Reservation Form</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                    <p><a href="index.php" class="btn primary">Return to Home</a></p>
                </div>
            <?php else: ?>
                <form action="booking.php" method="POST">
                    <div class="form-group">
                        <label for="room_id">Select Room</label>
                        <select id="room_id" name="room_id" required>
                            <option value="">-- Select a Room --</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['room_id']; ?>" 
                                        <?php echo ($room['room_id'] == $selectedRoomId) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room['room_type']); ?> - 
                                    ₱<?php echo number_format($room['price_overnight'], 2); ?> Overnight / 
                                    ₱<?php echo number_format($room['price_daytour'], 2); ?> DayTour
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_type">Booking Type</label>
                        <select id="booking_type" name="booking_type" required>
                            <option value="overnight">Overnight Stay</option>
                            <option value="daytour">Day Tour</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="check_in_date">Check-in Date</label>
                            <input type="date" id="check_in_date" name="check_in_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="check_out_date">Check-out Date</label>
                            <input type="date" id="check_out_date" name="check_out_date" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="num_guests">Number of Guests</label>
                        <input type="number" id="num_guests" name="num_guests" min="1" max="10" required>
                    </div>
                    
                    <button type="submit" class="btn primary">Book Now</button>
                </form>
            <?php endif; ?>
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

    <script>
        // Set minimum dates for check-in and check-out
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('check_in_date').min = today;
            document.getElementById('check_out_date').min = today;
            
            // Update check-out min date when check-in changes
            document.getElementById('check_in_date').addEventListener('change', function() {
                document.getElementById('check_out_date').min = this.value;
            });
            
            // Handle booking type change
            document.getElementById('booking_type').addEventListener('change', function() {
                const checkOutDate = document.getElementById('check_out_date');
                const checkInDate = document.getElementById('check_in_date');
                
                if (this.value === 'daytour') {
                    // For day tour, check-out is same as check-in
                    checkOutDate.value = checkInDate.value;
                    checkOutDate.disabled = true;
                } else {
                    checkOutDate.disabled = false;
                }
            });
        });
    </script>
</body>
</html>