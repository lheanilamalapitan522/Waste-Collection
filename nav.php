<?php
require_once 'auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
$adminPages = ['admin.php', 'manage-service.php', 'users.php', 'developer.php'];
?>
<nav class="navbar navbar-expand-lg" aria-label="Main navigation">
    <div class="container-fluid">
        <a href="index.php" class="nav-brand navbar-brand" aria-label="Mauban Waste Collection home">
            <span class="nav-brand-mark" aria-hidden="true">MW</span>
            <span class="nav-brand-name">Mauban <small>Waste Collection</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavigation" aria-controls="siteNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNavigation">
            <div class="nav-links navbar-nav ms-auto align-items-lg-center">
                <a href="index.php" class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="live-tracking.php" class="nav-link <?php echo $current_page === 'live-tracking.php' ? 'active' : ''; ?>">Live Tracking</a>
                <a href="route.php" class="nav-link <?php echo $current_page === 'route.php' ? 'active' : ''; ?>">Routes</a>
                <a href="schedule.php" class="nav-link <?php echo in_array($current_page, ['schedule.php', 'monthly-schedule.php'], true) ? 'active' : ''; ?>">Schedule</a>
                <a href="about.php" class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>">About</a>
                <?php if (is_admin()): ?>
                    <a href="admin.php" class="nav-link <?php echo in_array($current_page, $adminPages, true) ? 'active' : ''; ?>">Admin</a>
                    <a href="logout.php" class="nav-link login-btn">Log out</a>
                <?php elseif (is_collector()): ?>
                    <a href="collector.php" class="nav-link <?php echo $current_page === 'collector.php' ? 'active' : ''; ?>">Driver tracking</a>
                    <a href="logout.php" class="nav-link login-btn">Log out</a>
                <?php elseif (is_resident()): ?>
                    <span class="nav-role">Resident</span>
                    <a href="logout.php" class="nav-link login-btn">Log out</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link login-btn <?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Log in</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if (is_admin()): ?>
<aside class="admin-sidebar" aria-label="Administration sidebar">
    <div class="admin-sidebar-heading"><strong>Admin menu</strong><small>Mauban Waste Collection</small></div>
    <nav class="nav flex-column admin-sidebar-nav" aria-label="Admin pages">
        <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php"><i class="bi bi-house-door" aria-hidden="true"></i><span>Home</span></a>
        <a class="nav-link <?php echo $current_page === 'admin.php' ? 'active' : ''; ?>" href="admin.php"><i class="bi bi-speedometer2" aria-hidden="true"></i><span>Overview</span></a>
        <a class="nav-link <?php echo $current_page === 'manage-service.php' ? 'active' : ''; ?>" href="manage-service.php#routes"><i class="bi bi-signpost-split" aria-hidden="true"></i><span>Manage routes</span></a>
        <a class="nav-link <?php echo $current_page === 'manage-service.php' ? 'active' : ''; ?>" href="manage-service.php#schedules"><i class="bi bi-calendar2-week" aria-hidden="true"></i><span>Manage schedules</span></a>
        <a class="nav-link <?php echo $current_page === 'manage-service.php' ? 'active' : ''; ?>" href="manage-service.php#trucks"><i class="bi bi-truck" aria-hidden="true"></i><span>Manage trucks</span></a>
        <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php"><i class="bi bi-people" aria-hidden="true"></i><span>Manage accounts</span></a>
        <?php if (is_developer()): ?><a class="nav-link <?php echo $current_page === 'developer.php' ? 'active' : ''; ?>" href="developer.php"><i class="bi bi-tools" aria-hidden="true"></i><span>Maintenance</span></a><?php endif; ?>
    </nav>
</aside>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous" defer></script>
<?php if (getenv('APP_ENV') === 'development'): ?><script src="dev-reload.js" defer></script><?php endif; ?>
