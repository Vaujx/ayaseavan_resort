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

// Handle image upload for a room
if (isset($_POST['upload_image']) && isset($_POST['room_id']) && is_numeric($_POST['room_id'])) {
    $roomId = sanitize($_POST['room_id']);
    
    // Check if file was uploaded without errors
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['room_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if (in_array(strtolower($filetype), $allowed)) {
            // Check filesize (max 5MB)
            if ($_FILES['room_image']['size'] <= 5242880) {
                // Generate a unique filename to prevent overwriting
                $new_filename = 'room_' . $roomId . '_' . uniqid() . '.' . $filetype;
                $upload_path = 'assets/images/' . $new_filename;
                
                // Try to move the uploaded file
                if (move_uploaded_file($_FILES['room_image']['tmp_name'], $upload_path)) {
                    // Update the database with the new image filename
                    $updateQuery = "UPDATE rooms SET image = :image WHERE room_id = :roomId";
                    $stmt = $conn->prepare($updateQuery);
                    
                    if ($stmt->execute(['image' => $new_filename, 'roomId' => $roomId])) {
                        $successMessage = "Room image uploaded successfully!";
                        // Refresh the rooms list
                        $stmt = $conn->query($roomsQuery);
                        $rooms = $stmt->fetchAll();
                    } else {
                        $errorMessage = "Error updating room image in database: " . implode(", ", $stmt->errorInfo());
                    }
                } else {
                    $errorMessage = "Error moving uploaded file! Check directory permissions.";
                }
            } else {
                $errorMessage = "File is too large! Maximum size is 5MB.";
            }
        } else {
            $errorMessage = "Invalid file type! Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    } else {
        $errorMessage = "Error uploading file! Error code: " . $_FILES['room_image']['error'];
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
    <style>
        /* Additional styles for image upload */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
        }
        
        .image-upload-form {
            margin-top: 20px;
        }
        
        .image-upload-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .image-upload-form input[type="file"] {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .image-preview {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
        
        .room-thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
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
                                    <?php if (!empty($room['image'])): ?>
                                        <img src="assets/images/<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" class="room-thumbnail">
                                    <?php else: ?>
                                        <span>No image</span>
                                    <?php endif; ?>
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
                                            <a href="#" onclick="openImageUploadModal(<?php echo $room['room_id']; ?>, '<?php echo htmlspecialchars($room['room_type']); ?>')">Update Image</a>
                                            
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

    <!-- Image Upload Modal -->
    <div id="imageUploadModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeImageUploadModal()">&times;</span>
            <h3>Upload Image for <span id="modalRoomTitle"></span></h3>
            
            <form class="image-upload-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="modalRoomId" name="room_id" value="">
                
                <label for="room_image">Select Image:</label>
                <input type="file" id="room_image" name="room_image" accept=".jpg,.jpeg,.png,.gif" onchange="previewImage(this)">
                
                <img id="imagePreview" class="image-preview" src="#" alt="Image Preview">
                
                <button type="submit" name="upload_image" class="btn primary">Upload Image</button>
            </form>
        </div>
    </div>

    <script>
        function editRoom(roomId) {
            alert('Edit room functionality would open a form for room ID: ' + roomId);
            // In a real implementation, this would open a modal or redirect to an edit page
            return false;
        }
        
        // Image upload modal functions
        function openImageUploadModal(roomId, roomType) {
            document.getElementById('modalRoomId').value = roomId;
            document.getElementById('modalRoomTitle').textContent = roomType;
            document.getElementById('imageUploadModal').style.display = 'block';
            document.getElementById('imagePreview').style.display = 'none';
            return false;
        }
        
        function closeImageUploadModal() {
            document.getElementById('imageUploadModal').style.display = 'none';
        }
        
        // Preview the selected image before upload
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('imageUploadModal');
            if (event.target == modal) {
                closeImageUploadModal();
            }
        }
    </script>
</body>
</html>
