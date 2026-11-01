<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Policy — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container">
    <a href="index.php" class="back-link">← Back to Home</a>
    <article class="section-card policy-page">
        <h1>Cookie Policy</h1>
        <p><strong>Effective date: November 1, 2026</strong></p>
        <p>This Cookie Policy explains how Mauban Waste Collection uses cookies and similar browser storage when you access this service. We use only the storage needed to provide secure account access, protect forms, and support location-sharing features.</p>

        <h2>What we store</h2>
        <h3>Necessary session cookie</h3>
        <p>When you sign in, the application creates a PHP session cookie. This cookie contains an identifier that allows the server to recognize your active session. It supports authentication, role-based access, CSRF protection, and secure navigation. It does not contain your password or GPS coordinates.</p>
        <p>The session cookie is configured with <code>HttpOnly</code>, <code>SameSite=Lax</code>, and the <code>Secure</code> flag in production. These settings help prevent browser scripts from reading the cookie, limit cross-site requests, and require HTTPS when deployed securely.</p>

        <h3>Local browser storage</h3>
        <p>The browser may store two local-only preferences: acknowledgement that this notice was viewed and, for an authorized collector, that location permission was previously granted. These values remain in your browser and are not sent to the application as personal records.</p>

        <h2>How information is used</h2>
        <ul>
            <li>Session data is used to keep you signed in and enforce your account role.</li>
            <li>CSRF session data is used to help prevent unauthorized form submissions.</li>
            <li>Collector location is sent to the server only after the collector permits browser geolocation and starts sharing.</li>
            <li>GPS coordinates are used to display the current collection vehicle during an assigned active schedule.</li>
            <li>Location data is filtered to recent records and is not presented as a permanent public travel history.</li>
        </ul>

        <h2>Where information is stored and protected</h2>
        <p>Session and operational records are processed by the application server and stored in its configured MySQL database. The database connection is configured through protected server environment variables. Passwords are stored as one-way password hashes, not plaintext passwords.</p>
        <p>Access to account, route, schedule, vehicle, and location records is restricted by server-side authentication and role checks. State-changing forms use CSRF validation, database operations use parameterized queries, and user-provided content is escaped before it is displayed.</p>

        <h2>Third-party services</h2>
        <p>The site may load Bootstrap interface assets from jsDelivr and display maps from OpenStreetMap. These services may receive standard technical request information when your browser loads their resources. The application does not use advertising cookies or third-party analytics cookies.</p>

        <h2>Retention and your choices</h2>
        <p>The session cookie expires when the session ends or the server invalidates it. You can clear cookies and local storage through your browser settings, but doing so may sign you out and require location permission again. Collectors can stop location sharing at any time.</p>
        <p>For account access, data deletion, or privacy questions, contact the municipal project administrator. Requests should identify the relevant account without including a password or other secret.</p>

        <h2>Policy updates</h2>
        <p>We may update this policy when the service or its data practices change. The effective date above will be updated when a new version is published.</p>
    </article>
</main>
</body>
</html>
