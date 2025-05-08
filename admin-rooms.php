<?php
require_once 'config.php';
requireAdmin();

// Get all rooms
$roomsQuery = "SELECT * FROM rooms ORDER BY room_id";
$stmt = $conn->query($roomsQuery);
$rooms = $stmt->fetchAll();

// Handle room status update
if (isset($_GET['room_id']) && isset($_GET['status']) && is_numeric($_GET['room_id'])) {
    $roomId = sanitize($_GET['room_id']);
    $status = sanitize($_GET['status']);
    
    if (in_array($status, ['available', 'occupied', 'maintenance'])) {
        $updateQuery = "UPDATE rooms SET status = :status WHERE room_id = :roomId";
        $stmt = $conn->prepare($updateQuery);
        
        if ($stmt->execute(['status' => $status, 'roomId' => $roomId])) {
            $successMessage = "Room status updated successfully!";
            // Refresh the rooms list
            $stmt = $conn->query($roomsQuery);
            $rooms = $stmt->fetchAll();
        } else {
            $errorMessage = "Error updating room status: " . implode(", ", $stmt->errorInfo());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms | AyaSeavan Resort</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>🏝️ AyaSeavan</h2>
            <ul>
                <li><a href="dashboard.php">🏠 Dashboard</a></li>
                <li><a href="admin-reservations.php">📅 Reservations</a></li>
                <li><a href="admin-rooms.php" class="active">🛏️ Rooms</a></li>
                <li><a href="admin-settings.php">⚙️ Settings</a></li>
                <li><a href="index.php" target="_blank">🌐 View Site</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Manage Rooms</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </header>

            <?php if (isset($successMessage)): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if (isset($errorMessage)): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <!-- Rooms Table -->
            <section class="rooms-table">
                <h2>All Rooms</h2>
                <a href="#" class="btn primary add-btn">Add New Room</a>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Room Type</th>
                            <th>Capacity</th>
                            <th>Price (Overnight)</th>
                            <th>Price (Daytour)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?php echo $room['room_id']; ?></td>
                                <td>
                                    <img src="assets/images/<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" class="room-thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                                <td><?php echo $room['capacity']; ?> guests</td>
                                <td>₱<?php echo number_format($room['price_overnight'], 2); ?></td>
                                <td>₱<?php echo number_format($room['price_daytour'], 2); ?></td>
                                <td class="room-status <?php echo strtolower($room['status']); ?>">
                                    <?php echo ucfirst($room['status']); ?>
                                </td>
                                <td class="actions">
                                    <div class="dropdown">
                                        <button class="dropdown-btn">Actions ▼</button>
                                        <div class="dropdown-content">
                                            <a href="#" onclick="editRoom(<?php echo $room['room_id']; ?>)">Edit Room</a>
                                            
                                            <?php if ($room['status'] !== 'available'): ?>
                                                <a href="admin-rooms.php?room_id=<?php echo $room['room_id']; ?>&status=available">Mark Available</a>
                                            <?php endif; ?>
                                            
                                            <?php if ($room['status'] !== 'occupied'): ?>
                                                <a href="admin-rooms.php?room_id=<?php echo $room['room_id']; ?>&status=occupied">Mark Occupied</a>
                                            <?php endif; ?>
                                            
                                            <?php if ($room['status'] !== 'maintenance'): ?>
                                                <a href="admin-rooms.php?room_id=<?php echo $room['room_id']; ?>&status=maintenance">Mark Under Maintenance</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <script>
        function editRoom(roomId) {
            alert('Edit room functionality would open a form for room ID: ' + roomId);
            // In a real implementation, this would open a modal or redirect to an edit page
            return false;
        }
    </script>
</body>
</html>