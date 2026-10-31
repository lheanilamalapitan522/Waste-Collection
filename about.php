<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<div class="page-wrapper">
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <div class="page-header">
        <div class="page-title-section">
            <h1>ℹ ABOUT US</h1>
            <p>Learn more about our waste collection system</p>
        </div>
    </div>

    <div class="page-card">
        <h2>Mauban Waste Collection System</h2>
        <p>Our mission is to provide efficient, reliable, and eco-friendly waste collection services to the residents of Mauban, Quezon. Established in 2023, we serve 22 barangays and over 12,500 households.</p>
        
        <h3>Our Services</h3>
        <p>✅ Regular scheduled waste collection<br>
        ✅ Real-time vehicle tracking<br>
        ✅ Multiple collection routes<br>
        ✅ Authorized driver management</p>
        
        <h3>Contact Us</h3>
        <p>📍 Municipal Hall, Mauban, Quezon<br>
        📞 (042) 719-1234<br>
        ✉ info@maubanwaste.ph</p>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>