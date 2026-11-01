<?php
require_once 'auth.php';
require 'config.php';
require_role('admin');

$managementSection = $managementSection ?? (string) ($_GET['section'] ?? 'routes');
$managementSections = ['routes', 'schedules', 'trucks'];
if (!in_array($managementSection, $managementSections, true)) {
    http_response_code(404);
    exit('Management page not found.');
}

$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
$message = null;
$error = null;
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Your session expired. Refresh the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        try {
            if ($action === 'save_route') {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
                $name = trim((string) ($_POST['name'] ?? ''));
                $barangay = trim((string) ($_POST['barangay'] ?? ''));
                $status = (string) ($_POST['status'] ?? '');
                if ($name === '' || strlen($name) > 100 || $barangay === '' || strlen($barangay) > 100 || !in_array($status, ['active', 'inactive'], true)) {
                    throw new InvalidArgumentException('Enter a route name and barangay, and select a valid status.');
                }
                $pointNames = $_POST['point_name'] ?? [];
                $pointLatitudes = $_POST['point_latitude'] ?? [];
                $pointLongitudes = $_POST['point_longitude'] ?? [];
                if (!is_array($pointNames) || !is_array($pointLatitudes) || !is_array($pointLongitudes)) {
                    throw new InvalidArgumentException('Enter route stops in the provided table.');
                }
                $points = [];
                foreach ($pointNames as $index => $rawPointName) {
                    $pointName = trim((string) $rawPointName);
                    $latitude = trim((string) ($pointLatitudes[$index] ?? ''));
                    $longitude = trim((string) ($pointLongitudes[$index] ?? ''));
                    if ($pointName === '' && $latitude === '' && $longitude === '') continue;
                    if ($pointName === '' || strlen($pointName) > 100 || !is_numeric($latitude) || !is_numeric($longitude) || (float) $latitude < -90 || (float) $latitude > 90 || (float) $longitude < -180 || (float) $longitude > 180) {
                        throw new InvalidArgumentException('Each route stop needs a name, latitude from -90 to 90, and longitude from -180 to 180.');
                    }
                    $points[] = [$pointName, (float) $latitude, (float) $longitude];
                }
                $pdo->beginTransaction();
                if ($id) {
                    $stmt = $pdo->prepare('UPDATE routes SET name = ?, barangay = ?, status = ? WHERE id = ?');
                    $stmt->execute([$name, $barangay, $status, $id]);
                    $pdo->prepare('DELETE FROM route_points WHERE route_id = ?')->execute([$id]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO routes (name, barangay, status) VALUES (?, ?, ?)');
                    $stmt->execute([$name, $barangay, $status]);
                    $id = (int) $pdo->lastInsertId();
                }
                $pointStmt = $pdo->prepare('INSERT INTO route_points (route_id, point_order, point_name, latitude, longitude) VALUES (?, ?, ?, ?, ?)');
                foreach ($points as $index => $point) $pointStmt->execute([$id, $index + 1, $point[0], $point[1], $point[2]]);
                $pdo->commit();
                $message = 'Route and map points saved.';
            } elseif ($action === 'save_schedule') {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
                $routeId = filter_var($_POST['route_id'] ?? null, FILTER_VALIDATE_INT);
                $day = (string) ($_POST['day_name'] ?? '');
                $time = trim((string) ($_POST['collection_time'] ?? ''));
                $status = (string) ($_POST['status'] ?? '');
                if (!$routeId || !in_array($day, $days, true) || $time === '' || strlen($time) > 50 || !in_array($status, ['active', 'inactive'], true)) {
                    throw new InvalidArgumentException('Choose a route and day, enter a collection time, and select a valid status.');
                }
                if ($id) $stmt = $pdo->prepare('UPDATE schedule SET route_id = ?, day_name = ?, collection_time = ?, status = ? WHERE id = ?');
                else $stmt = $pdo->prepare('INSERT INTO schedule (route_id, day_name, collection_time, status) VALUES (?, ?, ?, ?)');
                $values = [$routeId, $day, $time, $status];
                if ($id) $values[] = $id;
                $stmt->execute($values);
                $message = 'Schedule saved.';
            } elseif ($action === 'delete_schedule') {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                if (!$id) throw new InvalidArgumentException('Select a valid schedule entry.');
                $pdo->prepare('DELETE FROM schedule WHERE id = ?')->execute([$id]);
                $message = 'Schedule entry deleted.';
            } elseif ($action === 'save_truck') {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
                $number = trim((string) ($_POST['vehicle_number'] ?? ''));
                $status = (string) ($_POST['status'] ?? '');
                $driverId = filter_var($_POST['driver_id'] ?? '', FILTER_VALIDATE_INT) ?: null;
                $collectorUserId = filter_var($_POST['collector_user_id'] ?? '', FILTER_VALIDATE_INT) ?: null;
                $routeId = filter_var($_POST['route_id'] ?? '', FILTER_VALIDATE_INT) ?: null;
                if ($number === '' || strlen($number) > 50 || !in_array($status, ['active', 'inactive'], true)) {
                    throw new InvalidArgumentException('Enter a truck number and select a valid status.');
                }
                if ($driverId && (!$collectorUserId || !$routeId)) {
                    throw new InvalidArgumentException('Assign a collector login and route when assigning a driver to a truck.');
                }
                if ($driverId) {
                    $driverCheck = $pdo->prepare("SELECT id FROM drivers WHERE id = ? AND status = 'active'");
                    $driverCheck->execute([$driverId]);
                    $accountCheck = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'collector' AND status = 'active'");
                    $accountCheck->execute([$collectorUserId]);
                    $routeCheck = $pdo->prepare("SELECT id FROM routes WHERE id = ? AND status = 'active'");
                    $routeCheck->execute([$routeId]);
                    if (!$driverCheck->fetch() || !$accountCheck->fetch() || !$routeCheck->fetch()) {
                        throw new InvalidArgumentException('Choose an active driver, collector account, and route.');
                    }
                    $alreadyLinked = $pdo->prepare('SELECT id FROM drivers WHERE user_id = ? AND id <> ?');
                    $alreadyLinked->execute([$collectorUserId, $driverId]);
                    if ($alreadyLinked->fetch()) {
                        throw new InvalidArgumentException('That collector account is already linked to another driver.');
                    }
                }
                $pdo->beginTransaction();
                if ($id) $stmt = $pdo->prepare('UPDATE vehicles SET vehicle_number = ?, status = ? WHERE id = ?');
                else $stmt = $pdo->prepare('INSERT INTO vehicles (vehicle_number, status) VALUES (?, ?)');
                $vals = [$number, $status];
                if ($id) $vals[] = $id;
                $stmt->execute($vals);
                if (!$id) $id = (int) $pdo->lastInsertId();
                $pdo->prepare('UPDATE drivers SET vehicle_id = NULL WHERE vehicle_id = ?')->execute([$id]);
                if ($driverId) {
                    $pdo->prepare('UPDATE drivers SET vehicle_id = ?, user_id = ?, route_id = ? WHERE id = ?')
                        ->execute([$id, $collectorUserId, $routeId, $driverId]);
                }
                $pdo->commit();
                $message = 'Truck, collector login, route, and assignment schedule saved.';
            } else {
                throw new InvalidArgumentException('Choose a valid management action.');
            }
        } catch (InvalidArgumentException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Service management failed: ' . $e->getMessage());
            $error = 'Could not save these changes. Check for duplicate route or truck names and try again.';
        }
    }
}

$routes = $pdo->query('SELECT id, name, barangay, status FROM routes ORDER BY name')->fetchAll();
$pointRows = $pdo->query('SELECT route_id, point_name, latitude, longitude FROM route_points ORDER BY route_id, point_order')->fetchAll();
$pointsByRoute = [];
foreach ($pointRows as $point) $pointsByRoute[$point['route_id']][] = $point;
$schedules = $pdo->query('SELECT s.*, r.name AS route_name FROM schedule s LEFT JOIN routes r ON r.id = s.route_id ORDER BY s.id')->fetchAll();
$trucks = $pdo->query('SELECT v.id, v.vehicle_number, v.status, d.id AS driver_id, d.name AS driver_name, d.user_id AS collector_user_id, d.route_id, u.username AS collector_username, r.name AS route_name FROM vehicles v LEFT JOIN drivers d ON d.vehicle_id = v.id LEFT JOIN users u ON u.id = d.user_id LEFT JOIN routes r ON r.id = d.route_id ORDER BY v.id')->fetchAll();
$drivers = $pdo->query("SELECT id, name FROM drivers WHERE status = 'active' ORDER BY name")->fetchAll();
$collectorAccounts = $pdo->query("SELECT id, username FROM users WHERE role = 'collector' AND status = 'active' ORDER BY username")->fetchAll();
$h = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$sectionTitles = [
    'routes' => 'Manage routes',
    'schedules' => 'Manage schedules',
    'trucks' => 'Manage trucks'
];
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $h($sectionTitles[$managementSection] . ' — ' . SITE_NAME); ?></title>
    <?php require 'bootstrap-head.php'; ?><link rel="stylesheet" href="style.css">
</head><body>
<?php require 'nav.php'; ?>
<main class="page-wrapper container-xl admin-page">
    <a href="admin.php" class="back-link">← Back to Administration</a>
    <header class="admin-heading"><div><p class="eyebrow">SERVICE MANAGEMENT</p><h1><?php echo $h($sectionTitles[$managementSection]); ?></h1><p>View the current information, then select Edit when you need to make a change.</p></div></header>
    <?php if ($message): ?><div class="alert alert-success" role="status"><?php echo $h($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?php echo $h($error); ?></div><?php endif; ?>

    <section class="admin-current-view mb-4" aria-labelledby="currentServiceTitle" hidden>
        <div class="section-top"><div><p class="eyebrow">CURRENT SERVICE</p><h2 class="section-title" id="currentServiceTitle">Review before editing</h2></div></div>
        <div class="row g-3">
            <div class="col-12 col-lg-4"><article class="section-card p-3 h-100"><h3 class="h5">Routes</h3><?php if ($routes): ?><ul class="mb-0"><?php foreach ($routes as $route): ?><li><?php echo $h($route['name'] . ' · ' . $route['barangay'] . ' — ' . ucfirst($route['status'])); ?></li><?php endforeach; ?></ul><?php else: ?><p class="text-muted mb-0">No routes configured.</p><?php endif; ?></article></div>
            <div class="col-12 col-lg-4"><article class="section-card p-3 h-100"><h3 class="h5">Schedules</h3><?php if ($schedules): ?><ul class="mb-0"><?php foreach ($schedules as $entry): ?><li><?php echo $h(($entry['route_name'] ?? 'Unassigned') . ' · ' . $entry['day_name'] . ' · ' . $entry['collection_time'] . ' — ' . ucfirst($entry['status'])); ?></li><?php endforeach; ?></ul><?php else: ?><p class="text-muted mb-0">No schedules configured.</p><?php endif; ?></article></div>
            <div class="col-12 col-lg-4"><article class="section-card p-3 h-100"><h3 class="h5">Trucks</h3><?php if ($trucks): ?><ul class="mb-0"><?php foreach ($trucks as $truck): ?><li><?php echo $h($truck['vehicle_number'] . ' · ' . ($truck['driver_name'] ?? 'Unassigned') . ' — ' . ucfirst($truck['status'])); ?></li><?php endforeach; ?></ul><?php else: ?><p class="text-muted mb-0">No trucks configured.</p><?php endif; ?></article></div>
        </div>
    </section>

    <section class="service-summary-board mb-4" aria-labelledby="serviceSummaryTitle" hidden>
        <div class="service-summary-title"><div><p class="eyebrow">CURRENT SERVICE</p></div></div>
        <div class="row g-3">
            <div class="col-12 col-xl-4"><article class="service-summary-panel"><h3>Routes</h3><div class="service-summary-scroll"><div class="service-summary-table route-summary-table"><div class="service-summary-header"><span>Route</span><span>Location</span><span>Status</span></div><?php foreach ($routes as $route): ?><div class="service-summary-row"><span><?php echo $h($route['name']); ?></span><span><?php echo $h($route['barangay']); ?></span><span><?php echo $h(ucfirst($route['status'])); ?></span></div><?php endforeach; ?><?php if (!$routes): ?><p class="p-3 mb-0">No routes configured.</p><?php endif; ?></div></div></article></div>
            <div class="col-12 col-xl-4"><article class="service-summary-panel"><h3>Schedules</h3><div class="service-summary-scroll"><div class="service-summary-table schedule-summary-table"><div class="service-summary-header"><span>Route</span><span>Day and time</span><span>Status</span></div><?php foreach ($schedules as $entry): ?><div class="service-summary-row"><span><?php echo $h($entry['route_name'] ?? 'Unassigned'); ?></span><span><?php echo $h($entry['day_name'] . ' · ' . $entry['collection_time']); ?></span><span><?php echo $h(ucfirst($entry['status'])); ?></span></div><?php endforeach; ?><?php if (!$schedules): ?><p class="p-3 mb-0">No schedules configured.</p><?php endif; ?></div></div></article></div>
            <div class="col-12 col-xl-4"><article class="service-summary-panel"><h3>Trucks</h3><div class="service-summary-scroll"><div class="service-summary-table truck-summary-table"><div class="service-summary-header"><span>Truck</span><span>Driver</span><span>Status</span></div><?php foreach ($trucks as $truck): ?><div class="service-summary-row"><span><?php echo $h($truck['vehicle_number']); ?></span><span><?php echo $h($truck['driver_name'] ?? 'Unassigned'); ?></span><span><?php echo $h(ucfirst($truck['status'])); ?></span></div><?php endforeach; ?><?php if (!$trucks): ?><p class="p-3 mb-0">No trucks configured.</p><?php endif; ?></div></div></article></div>
        </div>
    </section>

    <?php if ($managementSection === 'routes'): ?>
    <section class="section-card p-3 p-md-4 mb-4" id="routes">
        <div class="management-section-heading"><div><h2 class="section-title">Routes and map stops</h2><p class="text-muted mb-0">View route details and ordered map stops.</p></div><button class="btn btn-outline-success" type="button" data-editor-toggle="routeEditor" aria-expanded="false">Edit</button></div>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Route</th><th>Barangay</th><th>Ordered stops</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($routes as $route): ?><tr><td><?php echo $h($route['name']); ?></td><td><?php echo $h($route['barangay']); ?></td><td><?php if (!empty($pointsByRoute[$route['id']])): ?><ol class="mb-0"><?php foreach ($pointsByRoute[$route['id']] as $point): ?><li><?php echo $h($point['point_name'] . ' (' . $point['latitude'] . ', ' . $point['longitude'] . ')'); ?></li><?php endforeach; ?></ol><?php else: ?><span class="text-muted">No stops added</span><?php endif; ?></td><td><?php echo $h(ucfirst($route['status'])); ?></td></tr><?php endforeach; ?>
        <?php if (!$routes): ?><tr><td colspan="4" class="text-muted">No routes have been added.</td></tr><?php endif; ?></tbody></table></div>
        <div class="management-editor" id="routeEditor" hidden>
        <p class="text-muted">Edit route details and use the stop table to set the map order and coordinates.</p>
        <?php foreach ($routes as $route): ?>
        <form method="post" class="border rounded p-3 mb-3 route-editor-form"><input type="hidden" name="csrf_token" value="<?php echo $h($csrfToken); ?>"><input type="hidden" name="action" value="save_route"><input type="hidden" name="id" value="<?php echo (int) $route['id']; ?>">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Route name</label><input class="form-control" name="name" maxlength="100" required value="<?php echo $h($route['name']); ?>"></div>
                <div class="col-md-4"><label class="form-label">Barangay</label><input class="form-control" name="barangay" maxlength="100" required value="<?php echo $h($route['barangay']); ?>"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?php echo $route['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $route['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div>
                <div class="col-12"><label class="form-label">Ordered route stops</label><div class="table-responsive"><table class="table table-bordered align-middle route-stops-table mb-2"><thead><tr><th scope="col">Stop order</th><th scope="col">Stop name</th><th scope="col">Latitude</th><th scope="col">Longitude</th><th scope="col"><span class="visually-hidden">Remove stop</span></th></tr></thead><tbody class="route-stop-rows">
                    <?php foreach ($pointsByRoute[$route['id']] ?? [] as $index => $point): ?>
                    <tr><td class="route-stop-order"><?php echo $index + 1; ?></td><td><input class="form-control" name="point_name[]" maxlength="100" required value="<?php echo $h($point['point_name']); ?>" aria-label="Stop name"></td><td><input class="form-control" type="number" name="point_latitude[]" min="-90" max="90" step="any" required value="<?php echo $h($point['latitude']); ?>" aria-label="Latitude"></td><td><input class="form-control" type="number" name="point_longitude[]" min="-180" max="180" step="any" required value="<?php echo $h($point['longitude']); ?>" aria-label="Longitude"></td><td><button class="btn btn-outline-danger btn-sm remove-stop" type="button" aria-label="Remove stop">Remove</button></td></tr>
                    <?php endforeach; ?>
                    <tr><td class="route-stop-order">+</td><td><input class="form-control" name="point_name[]" maxlength="100" aria-label="New stop name"></td><td><input class="form-control" type="number" name="point_latitude[]" min="-90" max="90" step="any" aria-label="New stop latitude"></td><td><input class="form-control" type="number" name="point_longitude[]" min="-180" max="180" step="any" aria-label="New stop longitude"></td><td><button class="btn btn-outline-danger btn-sm remove-stop" type="button" aria-label="Remove stop">Remove</button></td></tr>
                </tbody></table></div><button class="btn btn-outline-success btn-sm add-stop" type="button">Add stop</button></div>
                <div class="col-12"><button class="btn btn-success">Save route</button> <a class="btn btn-outline-secondary" href="route.php">View map</a></div>
            </div>
        </form><?php endforeach; ?>
        <details class="border rounded p-3"><summary class="fw-semibold">Add a route</summary><form method="post" class="row g-3 mt-1 route-editor-form"><input type="hidden" name="csrf_token" value="<?php echo $h($csrfToken); ?>"><input type="hidden" name="action" value="save_route"><div class="col-md-4"><label class="form-label">Route name</label><input class="form-control" name="name" maxlength="100" required></div><div class="col-md-4"><label class="form-label">Barangay</label><input class="form-control" name="barangay" maxlength="100" required></div><div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div><div class="col-12"><label class="form-label">Ordered route stops</label><div class="table-responsive"><table class="table table-bordered align-middle route-stops-table mb-2"><thead><tr><th scope="col">Stop order</th><th scope="col">Stop name</th><th scope="col">Latitude</th><th scope="col">Longitude</th><th scope="col"><span class="visually-hidden">Remove stop</span></th></tr></thead><tbody class="route-stop-rows"><tr><td class="route-stop-order">1</td><td><input class="form-control" name="point_name[]" maxlength="100" aria-label="Stop name"></td><td><input class="form-control" type="number" name="point_latitude[]" min="-90" max="90" step="any" aria-label="Latitude"></td><td><input class="form-control" type="number" name="point_longitude[]" min="-180" max="180" step="any" aria-label="Longitude"></td><td><button class="btn btn-outline-danger btn-sm remove-stop" type="button" aria-label="Remove stop">Remove</button></td></tr></tbody></table></div><button class="btn btn-outline-success btn-sm add-stop" type="button">Add stop</button></div><div class="col-12"><button class="btn btn-success">Add route</button></div></form></details>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($managementSection === 'schedules'): ?>
    <section class="section-card p-3 p-md-4 mb-4" id="schedules">
        <div class="management-section-heading"><div><h2 class="section-title">Collection schedule</h2><p class="text-muted mb-0">View scheduled collection days and times.</p></div><button class="btn btn-outline-success" type="button" data-editor-toggle="scheduleEditor" aria-expanded="false">Edit</button></div>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Route</th><th>Day</th><th>Collection time</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($schedules as $entry): ?><tr><td><?php echo $h($entry['route_name'] ?? 'Unassigned'); ?></td><td><?php echo $h($entry['day_name']); ?></td><td><?php echo $h($entry['collection_time']); ?></td><td><?php echo $h(ucfirst($entry['status'])); ?></td></tr><?php endforeach; ?>
        <?php if (!$schedules): ?><tr><td colspan="4" class="text-muted">No schedule entries yet.</td></tr><?php endif; ?></tbody></table></div>
        <div class="management-editor" id="scheduleEditor" hidden>
        <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Route</th><th>Day</th><th>Collection time</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($schedules as $entry): ?><tr><td colspan="5"><form method="post" class="row g-2 align-items-end"><input type="hidden" name="csrf_token" value="<?php echo $h($csrfToken); ?>"><input type="hidden" name="action" value="save_schedule"><input type="hidden" name="id" value="<?php echo (int) $entry['id']; ?>"><div class="col-md-3"><label class="form-label">Route</label><select class="form-select" name="route_id" required><?php foreach ($routes as $route): ?><option value="<?php echo (int) $route['id']; ?>" <?php echo (int) $entry['route_id'] === (int) $route['id'] ? 'selected' : ''; ?>><?php echo $h($route['name'] . ' · ' . $route['barangay']); ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label">Day</label><select class="form-select" name="day_name"><?php foreach ($days as $day): ?><option <?php echo $entry['day_name'] === $day ? 'selected' : ''; ?>><?php echo $h($day); ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label">Collection time</label><input class="form-control" name="collection_time" maxlength="50" required value="<?php echo $h($entry['collection_time']); ?>"></div><div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?php echo $entry['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $entry['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div><div class="col-md-2 d-flex gap-2"><button class="btn btn-success btn-sm">Save</button></form><form method="post" onsubmit="return confirm('Delete this schedule entry?')"><input type="hidden" name="csrf_token" value="<?php echo $h($csrfToken); ?>"><input type="hidden" name="action" value="delete_schedule"><input type="hidden" name="id" value="<?php echo (int) $entry['id']; ?>"><button class="btn btn-outline-danger btn-sm">Delete</button></form></div></td></tr><?php endforeach; ?>
        <?php if (!$schedules): ?><tr><td class="text-muted">No schedule entries yet.</td></tr><?php endif; ?></tbody></table></div>
        <details class="border rounded p-3"><summary class="fw-semibold">Add schedule entry</summary><form method="post" class="row g-3 mt-1"><input type="hidden" name="csrf_token" value="<?php echo $h($csrfToken); ?>"><input type="hidden" name="action" value="save_schedule"><div class="col-md-3"><label class="form-label">Route</label><select class="form-select" name="route_id" required><?php foreach ($routes as $route): ?><option value="<?php echo (int) $route['id']; ?>"><?php echo $h($route['name'] . ' · ' . $route['barangay']); ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label">Day</label><select class="form-select" name="day_name"><?php foreach ($days as $day): ?><option><?php echo $h($day); ?></option><?php endforeach; ?></select></div><div class="col-md-3"><label class="form-label">Collection time</label><input class="form-control" name="collection_time" placeholder="8:00 AM - 11:00 AM" maxlength="50" required></div><div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div><div class="col-md-2 align-self-end"><button class="btn btn-success">Add schedule</button></div></form></details>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($managementSection === 'trucks'): ?>
    <section class="section-card p-3 p-md-4 mb-4" id="trucks">
        <div class="management-section-heading"><div><h2 class="section-title">Trucks and driver assignments</h2><p class="text-muted mb-0">Link each driver to a collector login and route. The live map shares their location only during that route’s active schedule.</p></div><button class="btn btn-outline-success" type="button" data-editor-toggle="truckEditor" aria-expanded="false">Edit</button></div>
        <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Truck</th><th>Assigned driver</th><th>Collector login</th><th>Assigned route</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($trucks as $truck): ?><tr><td><?php echo $h($truck['vehicle_number']); ?></td><td><?php echo $h($truck['driver_name'] ?? 'Unassigned'); ?></td><td><?php echo $h($truck['collector_username'] ?? 'Not linked'); ?></td><td><?php echo $h($truck['route_name'] ?? 'Not assigned'); ?></td><td><?php echo $h(ucfirst($truck['status'])); ?></td></tr><?php endforeach; ?>
        <?php if (!$trucks): ?><tr><td colspan="5" class="text-muted">No trucks have been added.</td></tr><?php endif; ?></tbody></table></div>
        <div class="management-editor" id="truckEditor" hidden>
        <?php foreach ($trucks as $truck): ?><form method="post" class="border rounded p-3 mb-3"><input type="hidden" name="csrf_token" value="<?php echo $h($csrfToken); ?>"><input type="hidden" name="action" value="save_truck"><input type="hidden" name="id" value="<?php echo (int) $truck['id']; ?>"><div class="row g-3 align-items-end"><div class="col-md-3"><label class="form-label">Truck number/name</label><input class="form-control" name="vehicle_number" maxlength="50" required value="<?php echo $h($truck['vehicle_number']); ?>"></div><div class="col-md-2"><label class="form-label">Assigned driver</label><select class="form-select" name="driver_id"><option value="">Unassigned</option><?php foreach ($drivers as $driver): ?><option value="<?php echo (int) $driver['id']; ?>" <?php echo (int) $truck['driver_id'] === (int) $driver['id'] ? 'selected' : ''; ?>><?php echo $h($driver['name']); ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label">Collector login</label><select class="form-select" name="collector_user_id"><option value="">Select login</option><?php foreach ($collectorAccounts as $account): ?><option value="<?php echo (int) $account['id']; ?>" <?php echo (int) $truck['collector_user_id'] === (int) $account['id'] ? 'selected' : ''; ?>><?php echo $h($account['username']); ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label">Assigned route</label><select class="form-select" name="route_id"><option value="">Select route</option><?php foreach ($routes as $route): ?><option value="<?php echo (int) $route['id']; ?>" <?php echo (int) $truck['route_id'] === (int) $route['id'] ? 'selected' : ''; ?>><?php echo $h($route['name']); ?></option><?php endforeach; ?></select></div><div class="col-md-1"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" <?php echo $truck['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $truck['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div><div class="col-md-2"><button class="btn btn-success">Save</button></div></div></form><?php endforeach; ?>
        <details class="border rounded p-3"><summary class="fw-semibold">Add a truck</summary><form method="post" class="row g-3 mt-1"><input type="hidden" name="csrf_token" value="<?php echo $h($csrfToken); ?>"><input type="hidden" name="action" value="save_truck"><div class="col-md-3"><label class="form-label">Truck number/name</label><input class="form-control" name="vehicle_number" maxlength="50" required></div><div class="col-md-2"><label class="form-label">Assign driver</label><select class="form-select" name="driver_id"><option value="">Unassigned</option><?php foreach ($drivers as $driver): ?><option value="<?php echo (int) $driver['id']; ?>"><?php echo $h($driver['name']); ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label">Collector login</label><select class="form-select" name="collector_user_id"><option value="">Select login</option><?php foreach ($collectorAccounts as $account): ?><option value="<?php echo (int) $account['id']; ?>"><?php echo $h($account['username']); ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label">Assigned route</label><select class="form-select" name="route_id"><option value="">Select route</option><?php foreach ($routes as $route): ?><option value="<?php echo (int) $route['id']; ?>"><?php echo $h($route['name']); ?></option><?php endforeach; ?></select></div><div class="col-md-1"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div><div class="col-md-2 align-self-end"><button class="btn btn-success">Add truck</button></div></form></details>
        </div>
    </section>
    <?php endif; ?>
</main>
<script>
document.querySelectorAll('[data-editor-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const editor = document.getElementById(button.dataset.editorToggle);
        const isOpening = editor.hidden;
        editor.hidden = !isOpening;
        button.setAttribute('aria-expanded', String(isOpening));
        button.textContent = isOpening ? 'Done' : 'Edit';
    });
});

document.querySelectorAll('.route-editor-form').forEach((form) => {
    const rows = form.querySelector('.route-stop-rows');
    if (!rows) return;

    const refreshOrder = () => {
        rows.querySelectorAll('tr').forEach((row, index) => {
            row.querySelector('.route-stop-order').textContent = String(index + 1);
        });
    };

    form.querySelector('.add-stop')?.addEventListener('click', () => {
        const row = rows.querySelector('tr:last-child').cloneNode(true);
        row.querySelectorAll('input').forEach((input) => {
            input.value = '';
            input.required = false;
        });
        rows.appendChild(row);
        refreshOrder();
    });

    rows.addEventListener('click', (event) => {
        const button = event.target.closest('.remove-stop');
        if (!button) return;
        const row = button.closest('tr');
        if (rows.querySelectorAll('tr').length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input').forEach((input) => {
                input.value = '';
                input.required = false;
            });
        }
        refreshOrder();
    });
});
</script>
</body></html>
