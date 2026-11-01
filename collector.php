<?php
require_once 'auth.php';
require 'config.php';
require_role('collector');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Tracking — <?php echo SITE_NAME; ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper">
    <div class="page-header">
        <div class="page-title-section">
            <h1>🚛 COLLECTOR TRACKING</h1>
            <p>Your location is sent to the live map only during the active collection schedule assigned to your collector account, route, and truck.</p>
        </div>
    </div>
    <div class="page-card">
        <h2>Collector location sharing</h2>
        <p>Allow location access once when your browser asks. If permission is saved, sharing starts automatically when you open this page. Keep it open and your phone's location service on while on duty. The server stores and displays your location only during your assigned active schedule.</p>
        <p id="collector-status" class="tracking-status">Tracking is stopped.</p>
        <button id="collector-toggle" class="submit-btn" type="button">Enable location sharing</button>
        <a href="live-tracking.php" class="btn-secondary">View public tracking map</a>
    </div>
</main>
<script src="collector.js"></script>
</body>
</html>
