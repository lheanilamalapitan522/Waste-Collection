<?php
require_once 'auth.php';
require 'config.php';

$adminUsername = getenv('ADMIN_USERNAME');
$adminPassword = getenv('ADMIN_PASSWORD');
$collectorUsername = getenv('COLLECTOR_USERNAME');
$collectorPassword = getenv('COLLECTOR_PASSWORD');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $validCredentials = false;

    if (is_string($username) && is_string($password) && $role === 'admin' &&
        is_string($adminUsername) && $adminUsername !== '' &&
        is_string($adminPassword) && $adminPassword !== '') {
        $validCredentials = hash_equals($adminUsername, $username) &&
            hash_equals($adminPassword, $password);
    } elseif (is_string($username) && is_string($password) && $role === 'collector' &&
        is_string($collectorUsername) && $collectorUsername !== '' &&
        is_string($collectorPassword) && $collectorPassword !== '') {
        $validCredentials = hash_equals($collectorUsername, $username) &&
            hash_equals($collectorPassword, $password);
    }

    if ($validCredentials) {
        session_regenerate_id(true);
        $_SESSION['role'] = $role;
        $_SESSION['admin_logged_in'] = $role === 'admin';
        header('Location: ' . ($role === 'admin' ? 'admin.php' : 'collector.php'));
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
        <h2 style="text-align: center;">🔑 Authorized Login</h2>
        
        <?php if (isset($error)): ?>
            <p style="color: red; text-align: center;"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="role">Access type</label>
                <select id="role" name="role" required>
                    <option value="">Select access type</option>
                    <option value="collector">Garbage collector</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
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