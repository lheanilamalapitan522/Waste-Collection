# Changes Report

Updated: 2026-11-01

This file is a handoff summary of the current project changes in the shared
working tree. Check `git status` and `git diff` before editing; the tree may
contain changes from multiple contributors.

## Map and live tracking

- Home, route, and live-tracking maps now use responsive OpenStreetMap embeds.
- Removed the Google Maps JavaScript loader and Google Maps API-key wiring from
  `config.php` and `docker-compose.yml`; README setup instructions now state
  that maps do not need a Google API key.
- The live location endpoint is polled every 10 seconds. On-duty, recent
  collector coordinates recenter the OSM embed and a truck icon overlay is
  placed at the center. The larger-map link follows the latest coordinates.
- The home and route maps use the same location feed and centered truck icon.
  The route page still lists route stops and schedules.
- Location sharing is accepted and shown only for an active collector/truck/
  route assignment during an active schedule. Old or unverifiable locations
  are hidden.
- **Embed limitation:** the OSM iframe cannot draw custom route polylines or
  place arbitrary pins inside the map. Truck icons are HTML overlays; route
  stops remain in the adjacent list. A richer route overlay would need a map
  library using OpenStreetMap tiles rather than the iframe embed.

## Accounts and authentication

- Residents use public pages without accounts. Resident registration was
  removed; public registration and administrator account creation allow
  garbage collector accounts, while administrators can also create admin
  accounts.
- Existing resident rows in the database were not deleted. Login still
  recognizes existing resident accounts for backward compatibility.
- Developer access is configured separately and is not a selectable
  registration role.
- Passwords require at least eight characters, including uppercase, lowercase,
  and a number. Password fields have visibility toggles.
- Admin account rows now have an inline new-password field, working eye toggle,
  and **Reset password** button. The old **Set temporary password** button was
  removed. Reset requests retain server-side validation and CSRF checks.
- Login, registration, forgot-password, and role-based access pages are present.

## Admin tools and interface

- Admins have a separate sidebar for overview, route, schedule, truck, and
  account management. Public navigation remains available at the top.
- Routes, schedules, and trucks are displayed read-only by default, with edit
  controls to open their management forms.
- Route stops use editable ordered rows for stop name and coordinates.
- Service summary panels show routes, schedules, and trucks in scrollable lists
  with the first three rows visible by default.
- Account tables scroll horizontally on narrow screens to keep reset controls
  usable.
- Bootstrap is included through `bootstrap-head.php` and used for layout and
  controls.

## Data and runtime

- `schema.sql` defines users, routes, route points, vehicles, drivers,
  schedules, and GPS locations, with development seed data.
- `migrations/002_assign_collectors_to_routes.sql` supports linking driver
  accounts to routes for schedule-based tracking. Existing databases need the
  migration once; admins then link collector logins and routes in Manage trucks.
- Docker Compose runs the PHP app, MySQL, and phpMyAdmin. Development reload
  support is provided by `dev-reload.js` and `dev-version.php`.
- `config.php` builds its PDO MySQL connection from `DB_HOST`, `DB_PORT`,
  `DB_NAME`, `DB_USER`, and `DB_PASS`. This supports Aiven's custom port; the
  Vercel deployment reads these values from deployment environment variables.
- `.env.example` was removed at the user's request. Keep actual credentials in
  the local ignored `.env` file and do not put secrets in this report.

## How the main flows work

### Collector location to public map

1. A collector signs in and enables browser location permission once from
   `collector.php`. Browsers save this site permission; when it is already
   granted, `collector.js` resumes location sharing automatically on later
   visits. The page keeps a local marker of prior successful permission for
   browsers without a geolocation Permissions API. The collector can stop
   sharing on the current page.
2. `collector.js` requests a fresh, high-accuracy GPS reading every five
   seconds (`enableHighAccuracy: true`, `maximumAge: 0`) while the page remains
   open. Accuracy depends on the device's GPS and surroundings. Mobile browsers
   may pause web pages in the background, so this is not guaranteed background
   tracking.
3. The browser posts coordinates to `location-update.php`. The server checks
   the authenticated collector, linked driver and truck, active route, and
   current weekday/time against the active schedule before storing a point.
   Coordinates are saved in `gps_locations` with a UTC timestamp.
4. Public map pages request `latest-location.php` every 10 seconds. That
   endpoint returns a recent location only when the collector remains eligible
   under the active schedule/assignment rules; otherwise it returns a null
   location and status message.
5. The page validates the returned coordinates and rebuilds the OSM iframe URL
   with a bounding box around them. A truck icon is HTML positioned at the
   center of the iframe container. With no valid location, the icon is hidden
   and the map returns to the Mauban or route default center.

### Admin service updates

1. An administrator opens **Manage routes**, **Manage schedules**, or
   **Manage trucks** from the admin sidebar.
2. Existing records are shown read-only first. The **Edit** button reveals the
   related forms; each form submits to `manage-service.php` with a CSRF token.
3. Route edits save the route and its ordered stop rows (name, latitude,
   longitude). Schedule edits save the route, weekday, collection-time text,
   and active state. Truck edits save the vehicle/driver information and link a
   collector login and route.
4. These database records drive the collector assignment checks and public
   schedule/route views. Changing a schedule or assignment therefore affects
   which GPS points can be accepted and displayed.

### Account creation and password reset

- A garbage collector can register from `register.php`; the server validates
  the username, password policy, role, and CSRF token before inserting a
  password hash in `users`. Resident visitors use public pages without creating
  accounts. Admins create admin or collector accounts in `users.php`.
- For account resets, an admin enters the replacement password in that
  account's row, can use the eye toggle to inspect it, and selects **Reset
  password**. `users.php` checks admin access, CSRF token, account name, and
  password rules before saving a new password hash.
- Login looks up an active username, verifies its password hash, stores the
  role in the session, and redirects to the corresponding admin, collector,
  developer, or public page.

### Existing database setup

- A new Docker database loads `schema.sql` on its first initialization.
- An existing database that predates collector-to-route links must apply
  `migrations/002_assign_collectors_to_routes.sql` once. Then an admin links
  each collector login and route from **Manage trucks**.
- The migration is not automatically rerun against an existing MySQL data
  volume. Keep that volume when restarting Compose; `docker compose down -v`
  would delete the persisted database volume.

## Main files to coordinate around

| Area | Files |
| --- | --- |
| Maps and location feed | `index.php`, `route.php`, `live-tracking.php`, `latest-location.php`, `location-update.php`, `tracking.php`, `collector.js` |
| Authentication and accounts | `auth.php`, `session.php`, `login.php`, `register.php`, `users.php`, `forgot-password.php`, `nav.php` |
| Admin service management | `admin.php`, `manage-service.php`, `schema.sql`, `migrations/002_assign_collectors_to_routes.sql` |
| Shared UI and runtime | `style.css`, `bootstrap-head.php`, `script.js`, `docker-compose.yml`, `Dockerfile`, `dev-reload.js`, `dev-version.php` |
| Project notes | `README.md`, `changes-report.md` |

## Integration notes

- Keep map polling aligned with `latest-location.php` and its schedule checks;
  do not show raw/stale GPS coordinates directly from the database.
- Account creation and reset actions must retain role authorization, CSRF
  validation, and the password policy.
- Do not reintroduce Google Maps API-key requirements for the current embedded
  OSM map pages unless the map implementation is deliberately changed.
- The report records current behavior, not a guarantee that every deployment
  has applied the database migration or configured runtime credentials.
