<?php
require_once 'auth.php';
require 'config.php';

require_role('admin');

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $role = (string) ($_POST['role'] ?? '');

    if (!preg_match('/^[A-Za-z0-9._-]{3,100}$/', $username)) {
        $error = 'Username must be 3-100 characters and use only letters, numbers, dots, underscores, or hyphens.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!in_array($role, ['admin', 'collector', 'developer'], true)) {
        $error = 'Select a valid account role.';
    } else {
        $statement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $statement->execute([':username' => $username]);

        if ($statement->fetch()) {
            $error = 'That username is already in use.';
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO users (username, password_hash, role, status)
                 VALUES (:username, :password_hash, :role, "active")'
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

$users = $pdo->query(
    'SELECT username, role, status, created_at FROM users ORDER BY username'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accounts — <?php echo SITE_NAME; ?></title>
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
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>
            <div class="form-group">
                <label for="role">Account role</label>
                <select id="role" name="role" required>
                    <option value="">Select a role</option>
                    <option value="collector">Garbage collector</option>
                    <option value="admin">Administrator</option>
                    <option value="developer">Developer</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Create Account</button>
        </form>
    </div>

    <div class="section-card">
        <div class="section-top"><div class="section-title">CURRENT ACCOUNTS</div></div>
        <table>
            <thead><tr><th>USERNAME</th><th>ROLE</th><th>STATUS</th><th>CREATED</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($user['status']), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
