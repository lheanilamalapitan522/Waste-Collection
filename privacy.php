<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container">
    <a href="index.php" class="back-link">← Back to Home</a>
    <article class="section-card policy-page">
        <h1>Privacy Policy</h1>
        <p>We collect only the information needed to operate waste collection services.</p>
        <h2>Information we use</h2>
        <ul>
            <li>Account usernames and password hashes for authorized users.</li>
            <li>Collector GPS coordinates only while an assigned active schedule is running.</li>
            <li>Operational route, vehicle, and schedule records.</li>
        </ul>
        <h2>Retention and sharing</h2>
        <p>Recent GPS points are used for live tracking and are not intended as a permanent travel history. We do not sell personal information or use it for advertising.</p>
        <h2>Your choices</h2>
        <p>Collectors may stop location sharing at any time. To request account data deletion, contact the municipal project administrator with the account username.</p>
    </article>
</main>
</body>
</html>
