<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Schedule — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<div class="page-wrapper">
    <a href="schedule.php" class="back-link">← Back to Weekly Schedule</a>
    
    <div class="page-header">
        <div class="page-title-section">
            <h1>📆 MONTHLY SCHEDULE</h1>
            <p>October 2026 Collection Calendar</p>
        </div>
    </div>

    <div class="section-card">
        <h3 style="padding: 15px 18px; border-bottom: 1px solid #eee;">October 2026</h3>
        <table>
            <thead>
                <tr>
                    <th>WEEK</th>
                    <th>DATES</th>
                    <th>ROUTE 1</th>
                    <th>ROUTE 2</th>
                    <th>ROUTE 3</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Week 1</td>
                    <td>Oct 1-4</td>
                    <td>Mon, Wed, Fri</td>
                    <td>Tue, Thu</td>
                    <td>Mon, Wed, Fri</td>
                </tr>
                <tr>
                    <td>Week 2</td>
                    <td>Oct 6-11</td>
                    <td>Mon, Wed, Fri</td>
                    <td>Tue, Thu, Sat</td>
                    <td>Mon, Wed, Fri</td>
                </tr>
                <tr>
                    <td>Week 3</td>
                    <td>Oct 13-18</td>
                    <td>Mon, Wed, Fri</td>
                    <td>Tue, Thu, Sat</td>
                    <td>Mon, Wed, Fri</td>
                </tr>
                <tr>
                    <td>Week 4</td>
                    <td>Oct 20-25</td>
                    <td>Mon, Wed, Fri</td>
                    <td>Tue, Thu, Sat</td>
                    <td>Mon, Wed, Fri</td>
                </tr>
                <tr>
                    <td>Week 5</td>
                    <td>Oct 27-31</td>
                    <td>Mon, Wed, Fri</td>
                    <td>Tue, Thu, Sat</td>
                    <td>Mon, Wed, Fri</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>