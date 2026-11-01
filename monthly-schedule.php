<?php
require_once 'session.php';
require 'config.php';
$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Manila'));
$monthStart = $now->modify('first day of this month')->setTime(0, 0);
$daysInMonth = (int) $monthStart->format('t');
$scheduleRows = $pdo->query(
    "SELECT r.name AS route_name, r.barangay, s.day_name, s.collection_time
     FROM schedule s JOIN routes r ON r.id = s.route_id
     WHERE s.status = 'active' AND r.status = 'active'
     ORDER BY FIELD(s.day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), r.name"
)->fetchAll();
$byWeekday = [];
foreach ($scheduleRows as $row) $byWeekday[$row['day_name']][] = $row;
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Schedule — <?php echo htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php require 'bootstrap-head.php'; ?><link rel="stylesheet" href="style.css">
</head><body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container-xl">
    <a href="schedule.php" class="back-link">← Back to Weekly Schedule</a>
    <header class="page-header"><div class="page-title-section"><h1>Monthly collection calendar</h1><p>Scheduled collections for <?php echo htmlspecialchars($monthStart->format('F Y'), ENT_QUOTES, 'UTF-8'); ?>.</p></div></header>
    <section class="section-card p-3 p-md-4">
        <div class="table-responsive"><table class="table table-striped align-middle mb-0">
            <thead><tr><th>Date</th><th>Day</th><th>Collection route and time</th></tr></thead><tbody>
            <?php for ($dayNumber = 1; $dayNumber <= $daysInMonth; $dayNumber++): $date = $monthStart->setDate((int) $monthStart->format('Y'), (int) $monthStart->format('m'), $dayNumber); $entries = $byWeekday[$date->format('l')] ?? []; ?>
            <tr><td><?php echo $date->format('M j'); ?></td><td><?php echo $date->format('l'); ?></td><td>
                <?php if (!$entries): ?><span class="text-muted">No scheduled collection</span>
                <?php else: foreach ($entries as $entry): ?><div><strong><?php echo htmlspecialchars($entry['route_name'] . ' · ' . $entry['barangay'], ENT_QUOTES, 'UTF-8'); ?></strong><span class="text-muted"> — <?php echo htmlspecialchars($entry['collection_time'], ENT_QUOTES, 'UTF-8'); ?></span></div><?php endforeach; endif; ?>
            </td></tr>
            <?php endfor; ?>
            </tbody></table></div>
    </section>
</main></body></html>
