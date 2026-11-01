<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container">
    <a href="index.php" class="back-link">← Back to Home</a>
    <article class="section-card policy-page">
        <h1>Terms of Service</h1>
        <p>Use this service only for legitimate municipal waste collection operations.</p>
        <ul>
            <li>Keep account credentials private and accurate.</li>
            <li>Collectors must share location only during authorized work.</li>
            <li>Do not attempt to access another user’s account or records.</li>
            <li>Service information may change when schedules or assignments are updated.</li>
        </ul>
    </article>
</main>
</body>
</html>
