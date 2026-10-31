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
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper">
    <div class="page-header">
        <div class="page-title-section">
            <h1>🚛 COLLECTOR TRACKING</h1>
            <p>Share your location while collecting waste.</p>
        </div>
    </div>
    <div class="page-card">
        <h2>Start location sharing</h2>
        <p>Your browser will ask for location permission. Keep this page open while you are on duty.</p>
        <p id="collector-status" class="tracking-status">Tracking is stopped.</p>
        <button id="collector-toggle" class="submit-btn" type="button">Start sharing location</button>
        <a href="live-tracking.php" class="btn-secondary">View public tracking map</a>
    </div>
</main>
<script src="collector.js"></script>
</body>
</html>
