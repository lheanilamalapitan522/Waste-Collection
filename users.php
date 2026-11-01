<?php
require_once 'auth.php';
require 'config.php';

require_role('admin');

$message = null;
$error = null;
$csrfToken = $_SESSION['csrf_token'] ?? null;
if (!is_string($csrfToken)) {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Your session expired. Refresh the page and try again.';
    } elseif (($_POST['action'] ?? 'create') === 'reset_password') {
        $targetUsername = trim((string) ($_POST['target_username'] ?? ''));
        $newPassword = (string) ($_POST['new_password'] ?? '');
        if ($targetUsername === '') {
            $error = 'Select a valid account.';
        } elseif (strlen($newPassword) < 8 ||
            !preg_match('/[A-Z]/', $newPassword) ||
            !preg_match('/[a-z]/', $newPassword) ||
            !preg_match('/[0-9]/', $newPassword)) {
            $error = 'New password must be at least 8 characters and include an uppercase letter, a lowercase letter, and a number.';
        } else {
            $reset = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE username = :username');
            $reset->execute([
                ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                ':username' => $targetUsername
            ]);
            $message = $reset->rowCount() ? 'Password reset successfully.' : 'Account not found.';
        }
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? '');

        if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $username)) {
            $error = 'Username must be 3-100 characters and use only letters, numbers, dots, underscores, or hyphens.';
        } elseif (strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)) {
            $error = 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, and a number.';
        } elseif (!in_array($role, ['admin', 'collector'], true)) {
            $error = 'Select a valid account role.';
        } else {
            $statement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
            $statement->execute([':username' => $username]);

            if ($statement->fetch()) {
                $error = 'That username is already in use.';
            } else {
                $insert = $pdo->prepare(
                    "INSERT INTO users (username, password_hash, role, status)
                     VALUES (:username, :password_hash, :role, 'active')"
                );
                $insert->execute([
                    ':username' => $username,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    ':role' => $role
                ]);
                $message = 'Account created successfully.';
            }
        }
    }
}

$users = $pdo->query(
    'SELECT id, username, role, status, created_at FROM users ORDER BY username'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accounts — <?php echo SITE_NAME; ?></title>
    <?php require 'bootstrap-head.php'; ?>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<div class="page-wrapper">
    <a href="admin.php" class="back-link">← Back to Admin Panel</a>
    <div class="page-header">
        <div class="page-title-section">
            <h1>👥 USER ACCOUNTS</h1>
            <p>Create accounts with fixed role-based access.</p>
        </div>
    </div>

    <div class="section-card" style="padding: 20px; margin-bottom: 25px;">
        <?php if ($message): ?><p style="color: #155724;"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <?php if ($error): ?><p style="color: #b00020;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <form method="POST" action="users.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" type="text" id="username" name="username" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-wrap">
                    <input class="form-control" type="password" id="password" name="password" minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,}" title="Use at least 8 characters, including an uppercase letter, a lowercase letter, and a number." required>
                    <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false" aria-controls="password">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle><line class="eye-slash" x1="4" y1="4" x2="20" y2="20"></line></svg>
                    </button>
                </div>
                <small>At least 8 characters, with an uppercase letter, a lowercase letter, and a number.</small>
            </div>
            <div class="form-group">
                <label for="role">Account role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Select a role</option>
                    <option value="collector">Garbage collector</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Create Account</button>
        </form>
    </div>

    <div class="section-card">
        <div class="section-top"><div class="section-title">CURRENT ACCOUNTS</div></div>
        <div class="table-responsive"><table>
            <thead><tr><th>USERNAME</th><th>ROLE</th><th>STATUS</th><th>CREATED</th><th>RESET PASSWORD</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($user['status']), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form method="POST" action="users.php" class="account-reset-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="target_username" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>">
                            <label class="visually-hidden" for="reset-password-<?php echo (int) $user['id']; ?>">New password for <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></label>
                            <div class="account-reset-controls">
                                <div class="password-input-wrap">
                                    <input class="form-control" type="password" id="reset-password-<?php echo (int) $user['id']; ?>" name="new_password" autocomplete="new-password" minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,}" title="At least 8 characters, with an uppercase letter, a lowercase letter, and a number." placeholder="New password" required>
                                    <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false" aria-controls="reset-password-<?php echo (int) $user['id']; ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M1.5 12s3.5-6.5 10.5-6.5S22.5 12 22.5 12 19 18.5 12 18.5 1.5 12 1.5 12Z"></path><circle cx="12" cy="12" r="3"></circle><path class="eye-slash" d="m4 4 16 16"></path></svg>
                                    </button>
                                </div>
                                <button type="submit" class="add-btn account-reset-submit">Reset password</button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</div>
</body>
</html>
