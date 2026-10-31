<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<main class="home-page">
    <div class="hero-content">
        <div class="hero">
            <h1>Waste Collection<br><span>Mauban, Quezon</span></h1>
            <p>Efficient, reliable, and eco-friendly waste collection services. Keeping our town clean and green!</p>
            <div class="hero-buttons">
                <a href="live-tracking.php" class="btn-primary">📍 Track Vehicle</a>
                <a href="schedule.php" class="btn-secondary">📅 View Schedule</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">🚛</span>
                <h3>Active Trucks</h3>
                <p>5 Units</p>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🏘</span>
                <h3>Barangays</h3>
                <p>22 Covered</p>
            </div>
            <div class="stat-card">
                <span class="stat-icon">👨👩👧👦</span>
                <h3>Households</h3>
                <p>12,500+</p>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🌿</span>
                <h3>Serving Since</h3>
                <p>2023</p>
            </div>
        </div>
    </div>
</main>

<script src="script.js"></script>
</body>
</html>