<?php
require_once 'auth.php';
require 'config.php';

$error = null;
$csrfToken = $_SESSION['csrf_token'] ?? null;
if (!is_string($csrfToken)) {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? '');
        $routeId = filter_var($_POST['route_id'] ?? null, FILTER_VALIDATE_INT);

        if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $username)) {
            $error = 'Username must be 3–100 characters and use only letters, numbers, dots, underscores, or hyphens.';
        } elseif (strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)) {
            $error = 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, and a number.';
        } elseif ($role !== 'collector') {
            $error = 'Only garbage collector accounts can be registered here.';
        } elseif ((string) ($_POST['privacy_consent'] ?? '') !== '1') {
            $error = 'You must agree to the Terms of Service and Privacy Policy.';
        } elseif (!$routeId) {
            $error = 'Select an active collection route.';
        } else {
            $existing = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
            $existing->execute([':username' => $username]);
            if ($existing->fetch()) {
                $error = 'That username is already in use.';
            } else {
                try {
                    $routeCheck = $pdo->prepare("SELECT id FROM routes WHERE id = :route_id AND status = 'active'");
                    $routeCheck->execute([':route_id' => $routeId]);
                    if (!$routeCheck->fetch()) {
                        throw new InvalidArgumentException('Select an active collection route.');
                    }

                    $pdo->beginTransaction();
                    $insert = $pdo->prepare(
                        "INSERT INTO users
                         (username, password_hash, role, status, privacy_consented_at, privacy_policy_version)
                         VALUES (:username, :password_hash, :role, 'active', UTC_TIMESTAMP(), :privacy_policy_version)"
                    );
                    $insert->execute([
                        ':username' => $username,
                        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        ':role' => $role,
                        ':privacy_policy_version' => '2026-11-01'
                    ]);
                    $driver = $pdo->prepare(
                        "INSERT INTO drivers (name, route_id, status, user_id)
                         VALUES (:name, :route_id, 'active', :user_id)"
                    );
                    $driver->execute([
                        ':name' => $username,
                        ':route_id' => $routeId,
                        ':user_id' => $pdo->lastInsertId()
                    ]);
                    $pdo->commit();
                    session_regenerate_id(true);
                    $_SESSION['registration_notice'] = 'Your account was created and assigned to the selected route. An administrator must link a truck before location sharing can start.';
                    header('Location: login.php');
                    exit;
                } catch (InvalidArgumentException $exception) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $error = $exception->getMessage();
                } catch (PDOException $exception) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    if ((string) $exception->getCode() === '23000') {
                        $error = 'That username is already in use.';
                    } else {
                        throw $exception;
                    }
                }
            }
        }
    }
}

$routes = $pdo->query("SELECT id, name, barangay FROM routes WHERE status = 'active' ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper login-page container">
    <a href="login.php" class="back-link">Back to Login</a>
    <section class="login-panel register-panel">
        <div class="login-mark" aria-hidden="true">MW</div>
        <p class="eyebrow">MAUBAN WASTE COLLECTION</p>
        <h1>Create an account</h1>
        <p class="login-intro">Create a garbage collector account.</p>
        <?php if ($error): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label class="form-label" for="register-username">Username</label>
                <input class="form-control" type="text" id="register-username" name="username" placeholder="Username" autocomplete="username" minlength="3" maxlength="100" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="register-password">Password</label>
                <div class="password-input-wrap">
                    <input class="form-control" type="password" id="register-password" name="password" placeholder="Password" autocomplete="new-password" minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,}" title="Use at least 8 characters, including an uppercase letter, a lowercase letter, and a number." required>
                    <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false" aria-controls="register-password">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle><line class="eye-slash" x1="4" y1="4" x2="20" y2="20"></line></svg>
                    </button>
                </div>
                <small class="form-text">At least 8 characters, with an uppercase letter, a lowercase letter, and a number.</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="register-role">Account role</label>
                <select class="form-select" id="register-role" name="role" required>
                    <option value="">Choose a role</option>
                    <option value="collector">Garbage collector</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="register-route">Assigned collection route</label>
                <select class="form-select" id="register-route" name="route_id" required>
                    <option value="">Choose a route</option>
                    <?php foreach ($routes as $route): ?>
                        <option value="<?php echo (int) $route['id']; ?>" <?php echo ((string) ($_POST['route_id'] ?? '') === (string) $route['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($route['name'] . ' — ' . $route['barangay'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="register-consent" name="privacy_consent" value="1" required>
                <label class="form-check-label" for="register-consent">
                    I agree to the <a href="terms.php" target="_blank" rel="noopener">Terms of Service</a> and
                    <a href="privacy.php" target="_blank" rel="noopener">Privacy Policy</a>.
                </label>
            </div>
            <button type="submit" class="submit-btn btn btn-success w-100">Create account</button>
        </form>
        <p class="login-register-link">Already have an account? <a href="login.php">Log in</a></p>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
