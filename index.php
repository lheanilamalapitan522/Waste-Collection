<?php
require_once 'session.php';
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
    <section class="landing-hero">
        <div class="hero">
            <p class="eyebrow">MAUBAN MUNICIPAL SERVICE</p>
            <h1>A cleaner Mauban<br><span>starts with all of us.</span></h1>
            <p class="hero-description">Find your collection schedule, track vehicles in real time, and help keep every barangay clean and green.</p>
            <div class="hero-buttons">
                <a href="schedule.php" class="btn-primary">📅 Check Your Schedule</a>
                <a href="live-tracking.php" class="btn-secondary">📍 Track a Vehicle</a>
            </div>
            <p class="hero-note">Reliable collection services for residents across Mauban, Quezon.</p>
        </div>

        <div class="hero-panel">
            <div class="hero-panel-icon">♻</div>
            <p class="hero-panel-label">OUR PROMISE</p>
            <h2>Cleaner streets.<br>Healthier communities.</h2>
            <p>Simple, accessible information to help you manage your household waste responsibly.</p>
        </div>
    </section>

    <section class="truck-overview">
        <div class="truck-overview-heading">
            <p class="eyebrow">WASTE COLLECTION TRUCK</p>
            <h2>Truck 01 service overview</h2>
            <p>Follow the collection truck and prepare your waste before it arrives.</p>
        </div>
        <div class="truck-overview-grid">
            <div class="truck-overview-card truck-status">
                <span class="truck-card-icon">🚛</span>
                <div>
                    <span class="truck-card-label">CURRENT STATUS</span>
                    <strong><span class="live-dot"></span> On route</strong>
                    <small>Collection is active today</small>
                </div>
            </div>
            <div class="truck-overview-card">
                <span class="truck-card-icon">📍</span>
                <div>
                    <span class="truck-card-label">CURRENT AREA</span>
                    <strong>Brgy. San Isidro</strong>
                    <small>Next stop: Brgy. Poblacion</small>
                </div>
            </div>
            <div class="truck-overview-card">
                <span class="truck-card-icon">📅</span>
                <div>
                    <span class="truck-card-label">TODAY'S SCHEDULE</span>
                    <strong>8:00 AM - 11:00 AM</strong>
                    <small>Route 1 collection window</small>
                </div>
            </div>
            <a href="live-tracking.php" class="truck-overview-card truck-card-action">
                <span class="truck-card-icon">🗺</span>
                <div>
                    <span class="truck-card-label">TRACK THE TRUCK</span>
                    <strong>Open live map</strong>
                    <small>View the latest location →</small>
                </div>
            </a>
        </div>
    </section>

    <section class="landing-section">
        <div class="section-heading">
            <p class="eyebrow">HOW WE CAN HELP</p>
            <h2>Everything you need in one place</h2>
            <p>Access the information and tools you need for a smoother waste collection experience.</p>
        </div>
        <div class="service-grid">
            <a href="schedule.php" class="service-card">
                <span class="service-icon">📅</span>
                <h3>Collection Schedule</h3>
                <p>See when collection services are coming to your area.</p>
                <span class="service-link">View schedule →</span>
            </a>
            <a href="live-tracking.php" class="service-card">
                <span class="service-icon">📍</span>
                <h3>Live Tracking</h3>
                <p>Follow collection vehicles and see their current routes.</p>
                <span class="service-link">Track vehicles →</span>
            </a>
            <a href="route.php" class="service-card">
                <span class="service-icon">🗺</span>
                <h3>Collection Routes</h3>
                <p>Explore the routes serving the barangays of Mauban.</p>
                <span class="service-link">Explore routes →</span>
            </a>
        </div>
    </section>

    <section class="map-preview-section">
        <div class="section-heading">
            <p class="eyebrow">LIVE SERVICE AREA</p>
            <h2>See collection activity in Mauban</h2>
            <p>Explore the current service route and collection points.</p>
        </div>
        <div class="map-preview">
            <iframe
                title="Map of Mauban, Quezon"
                src="https://www.google.com/maps?q=Mauban%2C+Quezon%2C+Philippines&output=embed"
                loading="lazy"
                allowfullscreen></iframe>
            <div class="map-preview-card">
                <span class="live-dot"></span>
                <div>
                    <strong>Truck 01 is on route</strong>
                    <p>San Isidro → Poblacion</p>
                </div>
                <a href="live-tracking.php" class="map-link">Open live map →</a>
            </div>
        </div>
    </section>

    <section class="community-strip">
        <div>
            <p class="eyebrow">SERVING OUR COMMUNITY</p>
            <h2>Working together for a better tomorrow.</h2>
        </div>
        <div class="community-stats">
            <div><strong>22</strong><span>Barangays</span></div>
            <div><strong>12,500+</strong><span>Households</span></div>
            <div><strong>2023</strong><span>Serving since</span></div>
        </div>
    </section>
</main>

<script src="script.js"></script>
</body>
</html>