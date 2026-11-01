<?php
require_once 'auth.php';
require 'config.php';

$registrationNotice = $_SESSION['registration_notice'] ?? null;
unset($_SESSION['registration_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string) ($_SESSION['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (isset($_SESSION['login_locked_until']) && time() < (int) $_SESSION['login_locked_until']) {
        $error = 'Too many failed sign-in attempts. Please wait a few minutes and try again.';
    } else {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $account = null;

    if ($username !== '' && $password !== '') {
        $statement = $pdo->prepare(
            "SELECT username, password_hash, role FROM users
             WHERE username = :username AND status = 'active' LIMIT 1"
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
                        "INSERT INTO users (username, password_hash, role)
                         VALUES (:username, :password_hash, :role)
                         ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), status = 'active'"
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
        in_array($account['role'], ['admin', 'collector', 'developer', 'resident'], true)) {
        session_regenerate_id(true);
        $_SESSION['username'] = $account['username'];
        $_SESSION['role'] = $account['role'];
        $_SESSION['admin_logged_in'] = is_admin();
        unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
        $destination = match ($account['role']) {
            'admin' => 'admin.php',
            'collector' => 'collector.php',
            'developer' => 'developer.php',
            'resident' => 'index.php'
        };
        header('Location: ' . $destination);
        exit;
    } else {
        $_SESSION['login_attempts'] = ((int) ($_SESSION['login_attempts'] ?? 0)) + 1;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_locked_until'] = time() + 300;
        }
        $error = "Invalid username or password";
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo SITE_NAME; ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<main class="page-wrapper login-page container">
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <section class="login-panel">
        <div class="login-brand-header">
            <img class="login-truck-icon" src="assets/recycling-truck.png" alt="Recycling truck">
            <p class="eyebrow">MAUBAN WASTE COLLECTION</p>
        </div>
        <h1>Welcome back</h1>
        <p class="login-intro">Sign in to continue to your account.</p>
        <?php if (is_string($registrationNotice)): ?>
            <div class="alert alert-success" role="status"><?php echo htmlspecialchars($registrationNotice, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <h2 style="text-align: center;">🔑 Authorized Login</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" type="text" id="username" name="username" placeholder="Username" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="password-input-wrap">
                    <input class="form-control" type="password" id="password" name="password" placeholder="Password" autocomplete="off" required>
                    <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false" aria-controls="password">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle><line class="eye-slash" x1="4" y1="4" x2="20" y2="20"></line></svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="submit-btn btn btn-success w-100">Log in</button>
        </form>
        <p class="forgot-password-link"><a href="forgot-password.php">Forgot password?</a></p>
        <div class="login-register-link"><span>Garbage collector?</span> <a href="register.php">Create a collector account</a></div>
        <p class="login-footnote">Residents can view public schedules and routes without signing in.</p>
    </section>
</main>

<script src="script.js"></script>
</body>
</html>
