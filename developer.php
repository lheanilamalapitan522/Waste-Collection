<?php
require_once 'auth.php';
require 'config.php';

require_role('developer');

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $details = trim((string) ($_POST['details'] ?? ''));

        if ($title === '' || strlen($title) > 150) {
            $error = 'Enter a task title of up to 150 characters.';
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO developer_maintenance_tasks (title, details, created_by)
                 VALUES (:title, :details, :created_by)'
            );
            $statement->execute([
                ':title' => $title,
                ':details' => $details !== '' ? $details : null,
                ':created_by' => $_SESSION['username']
            ]);
            $message = 'Maintenance task started.';
        }
    } elseif ($action === 'complete') {
        $taskId = filter_var($_POST['task_id'] ?? null, FILTER_VALIDATE_INT);
        if ($taskId) {
            $statement = $pdo->prepare(
                'UPDATE developer_maintenance_tasks
                 SET status = "completed", completed_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND status = "in_progress"'
            );
            $statement->execute([':id' => $taskId]);
            $message = $statement->rowCount() ? 'Maintenance task completed.' : 'That task is no longer in progress.';
        } else {
            $error = 'Select a valid maintenance task.';
        }
    } else {
        $error = 'Unknown maintenance action.';
    }
}

$tasks = $pdo->query(
    'SELECT id, title, details, status, created_by, created_at, completed_at
     FROM developer_maintenance_tasks ORDER BY created_at DESC, id DESC'
)->fetchAll();
$openTasks = (int) $pdo->query(
    'SELECT COUNT(*) FROM developer_maintenance_tasks WHERE status = "in_progress"'
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Maintenance — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>
<main class="page-wrapper">
    <div class="page-header">
        <div class="page-title-section">
            <h1>DEVELOPER MAINTENANCE</h1>
            <p>Start maintenance work, track system updates, and record completed tasks.</p>
        </div>
    </div>

    <div class="stat-card" style="margin-bottom: 24px;">
        <h3>Open maintenance tasks</h3>
        <p><?php echo $openTasks; ?></p>
    </div>

    <section class="section-card" style="padding: 20px; margin-bottom: 24px;">
        <h2>Start maintenance task</h2>
        <?php if ($message): ?><p style="color: #155724;"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <?php if ($error): ?><p style="color: #b00020;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
        <form method="POST" action="developer.php">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label for="title">Task title</label>
                <input type="text" id="title" name="title" maxlength="150" required>
            </div>
            <div class="form-group">
                <label for="details">Work details or system update</label>
                <textarea id="details" name="details" rows="4" style="width: 100%;"></textarea>
            </div>
            <button type="submit" class="submit-btn">Start Task</button>
        </form>
    </section>

    <section class="section-card" style="margin-bottom: 24px;">
        <div class="section-top"><div class="section-title">MAINTENANCE HISTORY</div></div>
        <table>
            <thead><tr><th>TASK</th><th>DETAILS</th><th>STATUS</th><th>STARTED BY</th><th>STARTED</th><th>COMPLETED</th><th>ACTION</th></tr></thead>
            <tbody>
            <?php if (!$tasks): ?>
                <tr><td colspan="7">No maintenance tasks have been recorded.</td></tr>
            <?php endif; ?>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($task['details'] ?? '', ENT_QUOTES, 'UTF-8')); ?></td>
                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $task['status'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($task['created_by'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($task['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($task['completed_at'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php if ($task['status'] === 'in_progress'): ?>
                            <form method="POST" action="developer.php">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="task_id" value="<?php echo (int) $task['id']; ?>">
                                <button type="submit" class="add-btn">Mark complete</button>
                            </form>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="section-card" style="padding: 20px;">
        <h2>System workspace</h2>
        <p>Review public system pages while carrying out maintenance.</p>
        <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px;">
            <a href="about.php" class="add-btn" style="display: inline-flex; text-decoration: none;">System Information</a>
            <a href="route.php" class="add-btn" style="display: inline-flex; text-decoration: none;">Check Routes</a>
            <a href="schedule.php" class="add-btn" style="display: inline-flex; text-decoration: none;">Check Schedules</a>
        </div>
    </section>
</main>
</body>
</html>
