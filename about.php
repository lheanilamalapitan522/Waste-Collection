<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container-xl about-page">
    <a href="index.php" class="back-link">← Back to Home</a>
    <section class="about-hero">
        <p class="eyebrow">ABOUT THE SERVICE</p>
        <h1>Cleaner communities start with clear information.</h1>
        <p>Mauban Waste Collection brings service schedules, route information, and driver-reported truck locations together in one place.</p>
    </section>

    <section class="about-content-grid row g-3">
        <article class="about-feature-card col-12 col-md-6 col-lg-4">
            <span class="about-feature-number">01</span>
            <h2>For residents</h2>
            <p>Check collection days, review the service routes, and see the latest location shared by a driver.</p>
            <a href="schedule.php">View the schedule <span aria-hidden="true">→</span></a>
        </article>
        <article class="about-feature-card col-12 col-md-6 col-lg-4">
            <span class="about-feature-number">02</span>
            <h2>For collection teams</h2>
            <p>Authorized drivers can share their phone location while they are on duty.</p>
            <a href="login.php">Driver login <span aria-hidden="true">→</span></a>
        </article>
        <article class="about-feature-card col-12 col-md-6 col-lg-4">
            <span class="about-feature-number">03</span>
            <h2>For municipal staff</h2>
            <p>Administrators manage service information, while the system developer maintains the application.</p>
            <a href="route.php">Explore service routes <span aria-hidden="true">→</span></a>
        </article>
    </section>

    <section class="about-note">
        <h2>Location sharing</h2>
        <p>Truck positions appear only while a driver shares GPS from the tracking page. Map positions may be delayed by device settings or network availability.</p>
    </section>
    <p class="asset-attribution">Truck icon by <a href="https://www.flaticon.com/free-icon/recycling-truck_11170693" target="_blank" rel="noopener noreferrer">SetitikPixelStudio on Flaticon</a>.</p>
</main>
</body>
</html>
