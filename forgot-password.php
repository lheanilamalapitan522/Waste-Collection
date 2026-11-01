<?php
require_once 'auth.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper" style="max-width: 600px;">
    <a href="login.php" class="back-link">← Back to Login</a>
    <section class="page-card">
        <h1>Forgot password?</h1>
        <p>Self-service password recovery is not configured. Contact your system administrator and ask them to set a temporary password for your account.</p>
        <p>For security, the administrator should share the temporary password with you directly.</p>
    </section>
</main>
</body>
</html>
