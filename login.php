<?php
require_once 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $account = null;

    if ($username !== '' && $password !== '') {
        $statement = $pdo->prepare(
            'SELECT username, password_hash, role FROM users
             WHERE username = :username AND status = "active" LIMIT 1'
        );
        $statement->execute([':username' => $username]);
        $account = $statement->fetch();

        if (!$account) {
            $configuredAccounts = [
                [
                    'username' => getenv('ADMIN_USERNAME'),
                    'password' => getenv('ADMIN_PASSWORD'),
                    'role' => 'admin'
                ],
                [
                    'username' => getenv('COLLECTOR_USERNAME'),
                    'password' => getenv('COLLECTOR_PASSWORD'),
                    'role' => 'collector'
                ],
                [
                    'username' => getenv('DEVELOPER_USERNAME'),
                    'password' => getenv('DEVELOPER_PASSWORD'),
                    'role' => 'developer'
                ]
            ];

            foreach ($configuredAccounts as $configuredAccount) {
                if (is_string($configuredAccount['username']) &&
                    is_string($configuredAccount['password']) &&
                    $configuredAccount['username'] !== '' &&
                    $configuredAccount['password'] !== '' &&
                    hash_equals($configuredAccount['username'], $username) &&
                    hash_equals($configuredAccount['password'], $password)) {
                    $account = $configuredAccount;
                    $insert = $pdo->prepare(
                        'INSERT INTO users (username, password_hash, role)
                         VALUES (:username, :password_hash, :role)
                         ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), status = "active"'
                    );
                    $insert->execute([
                        ':username' => $username,
                        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        ':role' => $configuredAccount['role']
                    ]);
                    $account['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                    break;
                }
            }
        }
    }

    if ($account && password_verify($password, $account['password_hash'] ?? '') &&
        in_array($account['role'], ['admin', 'collector', 'developer'], true)) {
        session_regenerate_id(true);
        $_SESSION['username'] = $account['username'];
        $_SESSION['role'] = $account['role'];
        $_SESSION['admin_logged_in'] = $account['role'] === 'admin';
        $destination = match ($account['role']) {
            'admin' => 'admin.php',
            'collector' => 'collector.php',
            'developer' => 'developer.php'
        };
        header('Location: ' . $destination);
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
