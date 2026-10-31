<?php
require_once 'auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="nav-brand">♻ <?php echo SITE_NAME; ?></div>
    <div class="nav-links">
        <a href="index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a>
        <a href="live-tracking.php" <?php echo $current_page == 'live-tracking.php' ? 'class="active"' : ''; ?>>Live Tracking</a>
        <a href="route.php" <?php echo $current_page == 'route.php' ? 'class="active"' : ''; ?>>Routes</a>
        <a href="schedule.php" <?php echo $current_page == 'schedule.php' ? 'class="active"' : ''; ?>>Schedule</a>
        <a href="monthly-schedule.php" <?php echo $current_page == 'monthly-schedule.php' ? 'class="active"' : ''; ?>>Monthly</a>
        <a href="about.php" <?php echo $current_page == 'about.php' ? 'class="active"' : ''; ?>>About</a>
        
        <?php if (is_admin()): ?>
            <a href="admin.php" <?php echo $current_page == 'admin.php' ? 'class="active"' : ''; ?>>⚙ Admin</a>
            <a href="logout.php" class="login-btn">🚪 Logout</a>
        <?php elseif (is_collector()): ?>
            <a href="collector.php" <?php echo $current_page == 'collector.php' ? 'class="active"' : ''; ?>>🚛 Collector</a>
            <a href="logout.php" class="login-btn">🚪 Logout</a>
        <?php else: ?>
            <a href="login.php" class="login-btn">🔑 Login</a>
        <?php endif; ?>
    </div>
</nav>