<?php
require_once 'config.php';
requireAdmin();

$successMessage = '';
$errorMessage = '';

// Get current settings
$settingsQuery = "SELECT * FROM settings";
$stmt = $conn->query($settingsQuery);

$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_maintenance'])) {
        $maintenanceMode = isset($_POST['maintenance_mode']) ? 'on' : 'off';
        $maintenanceMessage = sanitize($_POST['maintenance_message']);
        
        // Update maintenance mode setting
        $updateMaintenanceMode = "UPDATE settings SET setting_value = :maintenanceMode WHERE setting_name = 'maintenance_mode'";
        $stmt = $conn->prepare($updateMaintenanceMode);
        $stmt->execute(['maintenanceMode' => $maintenanceMode]);
        
        $updateMaintenanceMessage = "UPDATE settings SET setting_value = :maintenanceMessage WHERE setting_name = 'maintenance_message'";
        $stmt = $conn->prepare($updateMaintenanceMessage);
        $stmt->execute(['maintenanceMessage' => $maintenanceMessage]);
        
        if ($stmt) {
            $successMessage = "Maintenance settings updated successfully!";
            
            // Update local settings array
            $settings['maintenance_mode'] = $maintenanceMode;
            $settings['maintenance_message'] = $maintenanceMessage;
        } else {
            $errorMessage = "Error updating maintenance settings: " . implode(", ", $conn->errorInfo());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings | AyaSeavan Resort</title>
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
                <li><a href="admin-rooms.php">🛏️ Rooms</a></li>
                <li><a href="admin-settings.php" class="active">⚙️ Settings</a></li>
                <li><a href="index.php" target="_blank">🌐 View Site</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Site Settings</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                </div>
            </header>

            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <!-- Settings Sections -->
            <div class="settings-container">
                <!-- Maintenance Mode Settings -->
                <section id="maintenance" class="settings-section">
                    <h2>Maintenance Mode</h2>
                    <p class="settings-description">
                        When maintenance mode is enabled, the website will display a maintenance message to visitors and disable booking functionality.
                    </p>
                    
                    <form action="admin-settings.php#maintenance" method="POST" class="settings-form">
                        <div class="form-group checkbox-group">
                            <label for="maintenance_mode" class="checkbox-label">
                                <input type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === 'on' ? 'checked' : ''; ?>>
                                <span class="checkbox-text">Enable Maintenance Mode</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="maintenance_message">Maintenance Message</label>
                            <textarea id="maintenance_message" name="maintenance_message" rows="4"><?php echo isset($settings['maintenance_message']) ? htmlspecialchars($settings['maintenance_message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_maintenance" class="btn primary">Save Changes</button>
                        </div>
                    </form>
                </section>
                
                <!-- Site Information Settings -->
                <section id="site_info" class="settings-section">
                    <h2>Site Information</h2>
                    <p class="settings-description">
                        Update your resort's contact information and other details.
                    </p>
                    
                    <form action="admin-settings.php#site_info" method="POST" class="settings-form">
                        <div class="form-group">
                            <label for="site_title">Site Title</label>
                            <input type="text" id="site_title" name="site_title" value="AyaSeavan Beach Resort">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" id="contact_email" name="contact_email" value="fajardokire@gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" value="+63 951 056 8188">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3">AyaSeavan Beach Resort, Bani, Masinloc, Zambales, Philippines</textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_site_info" class="btn primary">Save Changes</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</body>
</html>