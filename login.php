<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'config.php';

$adminUsername = getenv('ADMIN_USERNAME');
$adminPassword = getenv('ADMIN_PASSWORD');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (
        $adminUsername !== false && $adminUsername !== '' &&
        $adminPassword !== false && $adminPassword !== '' &&
        is_string($username) && is_string($password) &&
        hash_equals($adminUsername, $username) &&
        hash_equals($adminPassword, $password)
    ) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<div class="page-wrapper" style="max-width: 450px;">
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <div class="page-card">
        <h2 style="text-align: center;">🔑 Admin Login</h2>
        
        <?php if (isset($error)): ?>
            <p style="color: red; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="submit-btn" style="width: 100%;">Login</button>
        </form>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>