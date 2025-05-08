<?php
require_once 'config.php';
requireAdmin();

// Handle status filter
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$validStatuses = ['pending', 'confirmed', 'cancelled', 'completed', ''];

if (!in_array($statusFilter, $validStatuses)) {
    $statusFilter = '';
}

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Handle status update
if (isset($_GET['booking_id']) && isset($_GET['new_status']) && is_numeric($_GET['booking_id'])) {
    $bookingId = sanitize($_GET['booking_id']);
    $newStatus = sanitize($_GET['new_status']);
    
    if (in_array($newStatus, ['pending', 'confirmed', 'cancelled', 'completed'])) {
        $updateQuery = "UPDATE bookings SET status = :newStatus WHERE booking_id = :bookingId";
        $stmt = $conn->prepare($updateQuery);
        
        if ($stmt->execute(['newStatus' => $newStatus, 'bookingId' => $bookingId])) {
            $successMessage = "Booking status updated successfully!";
        } else {
            $errorMessage = "Error updating booking status: " . implode(", ", $stmt->errorInfo());
        }
    }
}

// Handle booking deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $bookingId = sanitize($_GET['delete']);
    $deleteQuery = "DELETE FROM bookings WHERE booking_id = :bookingId";
    $stmt = $conn->prepare($deleteQuery);
    
    if ($stmt->execute(['bookingId' => $bookingId])) {
        $successMessage = "Booking deleted successfully!";
    } else {
        $errorMessage = "Error deleting booking: " . implode(", ", $stmt->errorInfo());
    }
}

// Build query based on filters
$query = "SELECT b.booking_id, b.check_in_date, b.check_out_date, b.booking_type, 
          b.total_amount, b.num_guests, b.status, b.payment_status, b.created_at,
          u.first_name, u.last_name, u.email, u.contact_number,
          r.room_type, r.room_id
          FROM bookings b 
          JOIN users u ON b.user_id = u.user_id 
          JOIN rooms r ON b.room_id = r.room_id";

$whereConditions = [];
$params = [];

if (!empty($statusFilter)) {
    $whereConditions[] = "b.status = :status";
    $params['status'] = $statusFilter;
}

if (!empty($search)) {
    $whereConditions[] = "(u.first_name ILIKE :search OR u.last_name ILIKE :search OR u.email ILIKE :search OR r.room_type ILIKE :search)";
    $params['search'] = '%' . $search . '%';
}

if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations | AyaSeavan Resort</title>
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
                <li><a href="admin-reservations.php" class="active">📅 Reservations</a></li>
                <li><a href="admin-rooms.php">🛏️ Rooms</a></li>
                <li><a href="admin-settings.php">⚙️ Settings</a></li>
                <li><a href="index.php" target="_blank">🌐 View Site</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Manage Reservations</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </header>

            <!-- Filters and Search -->
            <section class="filters-section">
                <div class="filters-container">
                    <div class="status-filters">
                        <a href="admin-reservations.php" class="filter-btn <?php echo $statusFilter === '' ? 'active' : ''; ?>">All</a>
                        <a href="admin-reservations.php?status=pending" class="filter-btn <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">Pending</a>
                        <a href="admin-reservations.php?status=confirmed" class="filter-btn <?php echo $statusFilter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                        <a href="admin-reservations.php?status=completed" class="filter-btn <?php echo $statusFilter === 'completed' ? 'active' : ''; ?>">Completed</a>
                        <a href="admin-reservations.php?status=cancelled" class="filter-btn <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                    </div>
                    
                    <form action="admin-reservations.php" method="GET" class="search-form">
                        <?php if (!empty($statusFilter)): ?>
                            <input type="hidden" name="status" value="<?php echo $statusFilter; ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Search by name, email, or room type" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn">Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="<?php echo empty($statusFilter) ? 'admin-reservations.php' : "admin-reservations.php?status=$statusFilter"; ?>" class="btn">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <?php if (isset($successMessage)): ?>
                    <div class="success-message"><?php echo $successMessage; ?></div>
                <?php endif; ?>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="error-message"><?php echo $errorMessage; ?></div>
                <?php endif; ?>
            </section>

            <!-- Bookings Table -->
            <section class="bookings-table">
                <h2>
                    <?php 
                    if (!empty($statusFilter)) {
                        echo ucfirst($statusFilter) . " Reservations";
                    } else {
                        echo "All Reservations";
                    }
                    ?>
                </h2>
                
                <?php if (count($bookings) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Guests</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['booking_id']; ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></div>
                                        <div class="small-text"><?php echo htmlspecialchars($booking['email']); ?></div>
                                        <?php if (!empty($booking['contact_number'])): ?>
                                            <div class="small-text"><?php echo htmlspecialchars($booking['contact_number']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                    <td><?php echo $booking['num_guests']; ?></td>
                                    <td>₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                                    <td class="status <?php echo strtolower($booking['status']); ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </td>
                                    <td class="payment-status <?php echo $booking['payment_status']; ?>">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </td>
                                    <td class="actions">
                                        <div class="dropdown">
                                            <button class="dropdown-btn">Actions ▼</button>
                                            <div class="dropdown-content">
                                                <a href="#" onclick="showBookingDetails(<?php echo $booking['booking_id']; ?>)">View Details</a>
                                                
                                                <?php if ($booking['status'] !== 'confirmed'): ?>
                                                    <a href="admin-reservations.php?booking_id=<?php echo $booking['booking_id']; ?>&new_status=confirmed<?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Confirm</a>
                                                <?php endif; ?>
                                                
                                                <?php if ($booking['status'] !== 'completed'): ?>
                                                    <a href="admin-reservations.php?booking_id=<?php echo $booking['booking_id']; ?>&new_status=completed<?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Mark Completed</a>
                                                <?php endif; ?>
                                                
                                                <?php if ($booking['status'] !== 'cancelled'): ?>
                                                    <a href="admin-reservations.php?booking_id=<?php echo $booking['booking_id']; ?>&new_status=cancelled<?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-danger">Cancel</a>
                                                <?php endif; ?>
                                                
                                                <a href="admin-reservations.php?delete=<?php echo $booking['booking_id']; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.')" class="text-danger">Delete</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <p>No reservations found.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div id="bookingDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Booking Details</h2>
            <div id="bookingDetailsContent">
                Loading...
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById("bookingDetailsModal");
        const closeBtn = document.getElementsByClassName("close")[0];
        
        function showBookingDetails(bookingId) {
            modal.style.display = "block";
            
            // Here you would typically fetch the booking details via AJAX
            // For simplicity, we'll just display some placeholder content
            document.getElementById("bookingDetailsContent").innerHTML = `
                <div class="booking-details">
                    <p><strong>Booking ID:</strong> ${bookingId}</p>
                    <p><strong>Status:</strong> Loading...</p>
                    <p><strong>Guest:</strong> Loading...</p>
                    <p><strong>Room:</strong> Loading...</p>
                    <p><strong>Dates:</strong> Loading...</p>
                    <p><strong>Amount:</strong> Loading...</p>
                    <p><strong>Payment Status:</strong> Loading...</p>
                    <p><strong>Created:</strong> Loading...</p>
                </div>
            `;
            
            // In a real implementation, you would fetch the data via AJAX
            // fetch(`get-booking-details.php?id=${bookingId}`)
            //     .then(response => response.json())
            //     .then(data => {
            //         // Update the modal content with the fetched data
            //     });
            
            return false;
        }
        
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>