<?php
require_once 'config.php';
requireAdmin();

// Get statistics for dashboard
$totalBookingsQuery = "SELECT COUNT(*) as total FROM bookings";
$stmt = $conn->query($totalBookingsQuery);
$totalBookings = $stmt->fetch()['total'];

$availableRoomsQuery = "SELECT COUNT(*) as total FROM rooms WHERE status = 'available'";
$stmt = $conn->query($availableRoomsQuery);
$availableRooms = $stmt->fetch()['total'];

$revenueQuery = "SELECT SUM(total_amount) as total FROM bookings WHERE status = 'confirmed' OR status = 'completed'";
$stmt = $conn->query($revenueQuery);
$revenue = $stmt->fetch()['total'] ?? 0;

$cancellationsQuery = "SELECT COUNT(*) as total FROM bookings WHERE status = 'cancelled'";
$stmt = $conn->query($cancellationsQuery);
$cancellations = $stmt->fetch()['total'];

// Get recent bookings
$recentBookingsQuery = "SELECT b.booking_id, b.check_in_date, b.total_amount, b.status, 
                        u.first_name, u.last_name, r.room_type 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.user_id 
                        JOIN rooms r ON b.room_id = r.room_id 
                        ORDER BY b.created_at DESC LIMIT 5";
$stmt = $conn->query($recentBookingsQuery);
$recentBookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | AyaSeavan Resort</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>🏝️ AyaSeavan</h2>
            <ul>
                <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
                <li><a href="admin-reservations.php">📅 Reservations</a></li>
                <li><a href="admin-rooms.php">🛏️ Rooms</a></li>
                <li><a href="admin-settings.php">⚙️ Settings</a></li>
                <li><a href="index.php" target="_blank">🌐 View Site</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </header>

            <!-- Stats Cards -->
            <section class="stats-cards">
                <div class="card blue">
                    <h3>Total Bookings</h3>
                    <p><?php echo $totalBookings; ?></p>
                </div>
                <div class="card green">
                    <h3>Available Rooms</h3>
                    <p><?php echo $availableRooms; ?></p>
                </div>
                <div class="card yellow">
                    <h3>Revenue</h3>
                    <p>₱<?php echo number_format($revenue, 2); ?></p>
                </div>
                <div class="card red">
                    <h3>Cancellations</h3>
                    <p><?php echo $cancellations; ?></p>
                </div>
            </section>

            <!-- Recent Bookings Table -->
            <section class="bookings-table">
                <h2>Recent Reservations</h2>
                <p class="view-all-link"><a href="admin-reservations.php">View all reservations →</a></p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Guest Name</th>
                            <th>Room Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentBookings) > 0): ?>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['check_in_date']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                                    <td>₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                                    <td class="status <?php echo strtolower($booking['status']); ?>">
                                        <?php 
                                        $statusIcon = '';
                                        switch($booking['status']) {
                                            case 'confirmed': $statusIcon = '✅'; break;
                                            case 'pending': $statusIcon = '⏳'; break;
                                            case 'cancelled': $statusIcon = '❌'; break;
                                            case 'completed': $statusIcon = '✓'; break;
                                        }
                                        echo $statusIcon . ' ' . ucfirst($booking['status']); 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="admin-reservations.php?status=pending" class="btn">View Pending Reservations</a>
                    <a href="admin-settings.php#maintenance" class="btn">Maintenance Settings</a>
                    <a href="admin-rooms.php" class="btn">Manage Rooms</a>
                </div>
            </section>
        </div>
    </div>
</body>
</html>