<?php
require_once 'session.php';
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule — <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require 'nav.php'; ?>

<div class="page-wrapper">
    <a href="index.php" class="back-link">← Back to Home</a>
    
    <div class="page-header">
        <div class="page-title-section">
            <h1>📅 COLLECTION SCHEDULE</h1>
            <p>View weekly collection schedules</p>
        </div>
        <a href="monthly-schedule.php" class="add-btn">📆 Monthly View</a>
    </div>

    <div class="section-card">
        <table>
            <thead>
                <tr>
                    <th>ROUTE</th>
                    <th>MONDAY</th>
                    <th>TUESDAY</th>
                    <th>WEDNESDAY</th>
                    <th>THURSDAY</th>
                    <th>FRIDAY</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Route 1</strong><br>Brgy. San Isidro</td>
                    <td>8:00AM - 11:00AM</td>
                    <td>—</td>
                    <td>8:00AM - 11:00AM</td>
                    <td>—</td>
                    <td>8:00AM - 11:00AM</td>
                </tr>
                <tr>
                    <td><strong>Route 2</strong><br>Brgy. Bagong Bayan</td>
                    <td>—</td>
                    <td>9:00AM - 12:00PM</td>
                    <td>—</td>
                    <td>9:00AM - 12:00PM</td>
                    <td>—</td>
                </tr>
                <tr>
                    <td><strong>Route 3</strong><br>Brgy. Poblacion</td>
                    <td>1:00PM - 4:00PM</td>
                    <td>—</td>
                    <td>1:00PM - 4:00PM</td>
                    <td>—</td>
                    <td>1:00PM - 4:00PM</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>