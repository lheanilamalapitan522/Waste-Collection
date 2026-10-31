<?php
require_once 'auth.php';
require 'config.php';

require_role('admin');

// Get real counts from database
$total_routes = $pdo->query("SELECT COUNT(*) FROM routes")->fetchColumn();
$active_routes = $pdo->query("SELECT COUNT(*) FROM routes WHERE status='active'")->fetchColumn();
$total_drivers = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();
$active_drivers = $pdo->query("SELECT COUNT(*) FROM drivers WHERE status='active'")->fetchColumn();
$total_schedule = $pdo->query("SELECT COUNT(*) FROM schedule")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<div class="page-wrapper">
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <div class="page-header">
        <div class="page-title-section">
            <h1>⚙ ADMIN PANEL</h1>
            <p>Manage all system settings and data</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card">
            <span class="stat-icon">🚛</span>
            <h3>Active Routes</h3>
            <p><?php echo $active_routes; ?> / <?php echo $total_routes; ?></p>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📍</span>
            <h3>Total Routes</h3>
            <p><?php echo $total_routes; ?></p>
        </div>
        <div class="stat-card">
            <span class="stat-icon">👥</span>
            <h3>Active Drivers</h3>
            <p><?php echo $active_drivers; ?> / <?php echo $total_drivers; ?></p>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📅</span>
            <h3>Schedules</h3>
            <p><?php echo $total_schedule; ?></p>
        </div>
    </div>

    <div class="section-card">
        <h3 style="padding: 15px 18px; border-bottom: 1px solid #eee;">Quick Actions</h3>
        <div style="padding: 20px; display: flex; flex-wrap: wrap; gap: 15px;">
            <a href="route.php" class="add-btn" style="display: inline-flex; text-decoration: none;">📍 Manage Routes</a>
            <a href="schedule.php" class="add-btn" style="display: inline-flex; text-decoration: none;">📅 Manage Schedule</a>
            <a href="live-tracking.php" class="add-btn" style="display: inline-flex; text-decoration: none;">🗺 Live Tracking</a>
            <a href="users.php" class="add-btn" style="display: inline-flex; text-decoration: none;">👥 Manage Users</a>
            <a href="about.php" class="add-btn" style="display: inline-flex; text-decoration: none;">ℹ About Page</a>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>