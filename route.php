<?php
require_once 'auth.php';
require 'config.php';
$canManage = is_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routes — <?php echo SITE_NAME; ?></title>
    
    <link rel="stylesheet" href="leaflet.css" />
    <script src="leaflet.js"></script>
    <link rel="stylesheet" href="style.css">
    
    <style>
        html, body { margin: 0; padding: 0; }
        
        .map-section { position: relative; }
        
        #routes-map {
            width: 100%;
            height: 400px;
            background: #e5e3df;
            border-radius: 8px;
            position: relative;
            z-index: 1;
        }
        
        .leaflet-container { z-index: 10 !important; }
        
        .truck-status-card {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #ffffff;
            padding: 15px 18px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 999;
            min-width: 240px;
        }
        
        .truck-marker-info {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: #ffffff;
            padding: 12px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 999;
            font-size: 0.85rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<?php require 'nav.php'; ?>

<div class="page-wrapper">
    <a href="index.php" class="back-link">← Back to Home</a>

    <div class="page-header">
        <div class="page-title-section">
            <h1>📍 ROUTES & LIVE TRACKING</h1>
            <p><?php echo $canManage ? 'Manage collection routes & monitor vehicle locations' : 'View collection routes and vehicle locations'; ?></p>
        </div>
        <?php if ($canManage): ?>
            <button class="add-btn">➕ Add Route</button>
        <?php endif; ?>
    </div>

    <div class="content-grid">
        <div class="main-col">
            <div class="map-section">
                <div class="map-header">
                    <div class="map-title">🗺️ Live Vehicle Location</div>
                    <div class="live-indicator"><span class="live-dot"></span> LIVE</div>
                </div>
                
                <div id="routes-map"></div>

                <div class="truck-status-card">
                    <h4>🚛 Truck 01 — Route 1</h4>
                    <p><strong>Current:</strong> Brgy. San Isidro</p>
                    <p><strong>Next:</strong> Brgy. Poblacion</p>
                    <p><strong>ETA:</strong> ~15 minutes</p>
                    <span class="status-tag">● In Service</span>
                </div>

                <div class="truck-marker-info">
                    <p><span style="font-size:18px;">🚛</span> Your Vehicle</p>
                    <p><span style="color:#009933; font-weight:bold;">●</span> Route Start</p>
                    <p><span style="color:#f59e0b; font-weight:bold;">●</span> Collection Point</p>
                    <p><span style="color:#ef4444; font-weight:bold;">●</span> Landfill (End)</p>
                </div>
            </div>

            <div class="section-card">
                <div class="section-top">
                    <div class="section-title">📋 ROUTES <span class="section-subtitle">Manage & assign collection routes</span></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ROUTE ID</th>
                            <th>ROUTE NAME</th>
                            <th>AREA/BARANGAY</th>
                            <th>STATUS</th>
                            <?php if ($canManage): ?><th>ACTION</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>R001</td>
                            <td>ROUTE 1</td>
                            <td>BRGY. SAN ISIDRO</td>
                            <td><span class="status-badge status-active">active</span></td>
                            <?php if ($canManage): ?><td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td><?php endif; ?>
                        </tr>
                        <tr>
                            <td>R002</td>
                            <td>ROUTE 2</td>
                            <td>BRGY. BAGONG BAYAN</td>
                            <td><span class="status-badge status-active">active</span></td>
                            <?php if ($canManage): ?><td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td><?php endif; ?>
                        </tr>
                        <tr>
                            <td>R003</td>
                            <td>ROUTE 3</td>
                            <td>BRGY. POBLACION</td>
                            <td><span class="status-badge status-inactive">inactive</span></td>
                            <?php if ($canManage): ?><td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td><?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section-card">
                <div class="section-top">
                    <div class="section-title">📅 UPDATE SCHEDULE <span class="section-subtitle">Set and update collection schedule</span></div>
                    <?php if ($canManage): ?><button class="add-btn">➕ Add Schedule</button><?php endif; ?>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>SCHEDULE ID</th>
                            <th>ROUTE</th>
                            <th>DAY</th>
                            <th>TIME</th>
                            <th>STATUS</th>
                            <?php if ($canManage): ?><th>ACTION</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>S001</td>
                            <td>ROUTE 1</td>
                            <td>MON-WED-FRI</td>
                            <td>8:00AM-11:00AM</td>
                            <td><span class="status-badge status-active">active</span></td>
                            <?php if ($canManage): ?><td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td><?php endif; ?>
                        </tr>
                        <tr>
                            <td>S002</td>
                            <td>ROUTE 2</td>
                            <td>TUES-THURS-SATURDAY</td>
                            <td>9:00AM-12:00PM</td>
                            <td><span class="status-badge status-active">active</span></td>
                            <?php if ($canManage): ?><td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td><?php endif; ?>
                        </tr>
                        <tr>
                            <td>S003</td>
                            <td>ROUTE 3</td>
                            <td>MON-WED-FRI</td>
                            <td>1:00PM-4:00PM</td>
                            <td><span class="status-badge status-inactive">inactive</span></td>
                            <?php if ($canManage): ?><td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td><?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php if ($canManage): ?>
            <div class="section-card">
                <div class="section-top">
                    <div class="section-title">👥 MANAGE AUTHORIZED DRIVERS</div>
                    <button class="add-btn">➕ Add Driver</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>DRIVER ID</th>
                            <th>NAME</th>
                            <th>CONTACT NUMBER</th>
                            <th>LICENSE NUMBER</th>
                            <th>STATUS</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>D001</td>
                            <td>JUAN CRUZ</td>
                            <td>0951-601-8812</td>
                            <td>NCR-1234567</td>
                            <td><span class="status-badge status-active">active</span></td>
                            <td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td>
                        </tr>
                        <tr>
                            <td>D002</td>
                            <td>PEDRO SANTOS</td>
                            <td>0966-220-2586</td>
                            <td>NCR-2345678</td>
                            <td><span class="status-badge status-active">active</span></td>
                            <td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td>
                        </tr>
                        <tr>
                            <td>D003</td>
                            <td>MARK REYES</td>
                            <td>0966-024-5620</td>
                            <td>NCR-6478910</td>
                            <td><span class="status-badge status-inactive">inactive</span></td>
                            <td class="action-icons">
                                <span class="edit-icon">✏️ Edit</span>
                                <span class="delete-icon">🗑️ Delete</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="sidebar">
            <div class="sidebar-box box-schedule">
                <strong>📅 Schedule Reminder</strong>
                Keep your collection schedule up to date for a smoother and more efficient waste management system.
            </div>
            <div class="sidebar-box box-drivers">
                <strong>✅ Driver System</strong>
                Only authorized drivers can access the driver system and collection routes.
            </div>
        </div>
    </div>
</div>

<script>
const routePoints = [
    [14.1852, 121.6835], // San Isidro - Start
    [14.1828, 121.6860], // Collection Point
    [14.1780, 121.6910]  // Poblacion - End
];

document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('routes-map').setView([14.1815, 121.6875], 14);
    
    // ✅ Fixed: OpenStreetMap tiles — works 100% free!
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const truckIcon = L.divIcon({
        html: '<div style="font-size: 28px; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.3));">🚛</div>',
        className: '',
        iconSize: [28, 28],
        iconAnchor: [14, 14]
    });
    L.marker(routePoints[0], { icon: truckIcon }).addTo(map).bindPopup('🚛 Truck 01 — On Route');

    L.circleMarker(routePoints[0], { color: '#009933', radius: 8, fillOpacity: 1 }).addTo(map);
    L.circleMarker(routePoints[1], { color: '#f59e0b', radius: 8, fillOpacity: 1 }).addTo(map);
    L.circleMarker(routePoints[2], { color: '#ef4444', radius: 8, fillOpacity: 1 }).addTo(map);

    L.polyline(routePoints, { color: '#3b82f6', weight: 4, opacity: 0.7 }).addTo(map);
});
</script>

<script src="script.js"></script>
</body>
</html>