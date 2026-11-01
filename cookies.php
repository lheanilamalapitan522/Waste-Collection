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
        <p>The application uses one necessary PHP session cookie for authentication, CSRF protection, and secure navigation.</p>
        <p>The cookie is HttpOnly, SameSite=Lax, and Secure in production. The optional notice acknowledgement is stored only in your browser’s local storage; it is not sent to the server.</p>
    </article>
</main>
</body>
</html>
