# Mauban Waste Collection

A PHP and MySQL waste collection management application with route scheduling,
administration, and live tracking.

## Phase 1: Requirements and Scope

The system provides online waste-collection information for residents and
operational tools for authorized municipal personnel in Mauban, Quezon.

### User roles

- **Residents:** View the public landing page, collection schedules, assigned
  routes, and the current location/status of collection vehicles without an
  account.
- **Drivers:** Sign in securely, start or stop location sharing during a
  collection operation, and view their assigned route.
- **Administrators:** Sign in securely, manage drivers and vehicles, maintain
  routes and schedules, and monitor active collection vehicles.
- **Developers:** Sign in with the separately configured developer account to
  access the maintenance dashboard and all administrator-protected pages.

The public homepage is the default entry point. Residents can browse schedules,
routes, and tracking information without signing in or creating an account.
Drivers can register from the Login page. Administrators create administrator
accounts. The developer account is configured separately with
`DEVELOPER_USERNAME` and `DEVELOPER_PASSWORD`; developer is not available in
the registration role list and has administrator-level access.

The collector location-sharing page requires browser location permission and
HTTPS in production. `http://localhost:8080` is permitted for local testing.

### Functional requirements

1. Provide a public, mobile-responsive website.
2. Display collection schedules by service area or barangay.
3. Display collection areas in OpenStreetMap embeds and show route stops alongside the map.
4. Display the latest available GPS position and tracking status of active
   collection vehicles.
5. Allow authorized drivers to sign in and control tracking for their assigned
   vehicle.
6. Allow administrators to manage users, vehicles, routes, schedules, and
   tracking activity.
7. Protect administrative and driver functions with authentication,
   authorization, and secure sessions.
8. Handle unavailable GPS, denied location permission, and interrupted
   internet connections with a clear status message.

### Non-functional requirements

- **Usability:** Residents must be able to reach schedules, routes, and
  tracking from the main navigation on desktop and mobile screens.
- **Performance:** Public pages should remain usable on ordinary mobile
  internet connections, and tracking views should show the timestamp of the
  latest location update.
- **Reliability:** Invalid input, unavailable location data, and failed
  requests must show an explicit error or status rather than silently
  reporting success.
- **Security:** Passwords and session data must not be exposed in source
  control; protected pages must reject unauthenticated users; production
  deployment must use HTTPS.
- **Maintainability:** Route, schedule, vehicle, and user data must be
  separated from presentation code and accessed through validated database
  operations.
- **Compatibility:** The website must support current desktop and mobile
  browsers with JavaScript enabled for live tracking updates.

### In scope

- Public landing page
- Collection schedules
- Existing route display and route monitoring
- GPS-based vehicle location display
- Driver and administrator authentication
- Vehicle, route, schedule, and driver management
- OpenStreetMap embedded map display
- Black-box functional testing
- ISO/IEC 25010-based software quality evaluation

### Out of scope for the initial release

- AI route optimization
- Online payment processing
- Smart-bin sensors or bin-fill monitoring
- Waste segregation or recycling monitoring
- Vehicle maintenance management
- A native Android or iOS application
- Automated health-risk or disease analysis

### Phase 1 acceptance criteria

- Every planned feature is assigned to a user role.
- Every in-scope feature has an observable success condition.
- Out-of-scope features are not required for the initial release.
- GPS and internet limitations are disclosed to users.
- Later development and testing can trace each feature to the requirements
  above.

## Setup

For a copy-ready local environment template, installation prerequisites,
database migration instructions, first-run account setup, and a manual
requirements checklist, see [CONTRIBUTING.md](./CONTRIBUTING.md).

### Docker

1. Start Docker Desktop and wait for its engine to finish starting.
2. Create your local environment file from the template:

   ```powershell
   Copy-Item .env.example .env
   ```

   Set unique, strong values for `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`,
   `ADMIN_PASSWORD`, and `DEVELOPER_PASSWORD` in `.env`. The configured admin
   and developer accounts are created when you first sign in with each account.
   Keep `.env` private; it is excluded from Git.
3. Validate the Compose configuration and start the application:

   ```sh
   docker compose config --quiet
   docker compose up --build
   ```

   In VS Code, you can also run **Terminal → Run Task → Start development
   (Docker Compose)**. This task starts the services in the background.

Maps use OpenStreetMap embeds and do not require a map API key. Location
markers are shown as page overlays while the embedded map recenters on reported
coordinates.

The app container mounts the project folder read-only and watches PHP, CSS,
and JavaScript files during development. Save a change and the open page at
`http://localhost:8080` reloads automatically. Rebuild only when changing the
Dockerfile or Apache configuration. Stop the foreground Compose command with
Ctrl+C, or run `docker compose down` to stop services started in the background.
Both methods keep the database volume.

4. Open `http://localhost:8080`. The Docker port is bound to localhost only.
5. Open `http://localhost:8081` to open phpMyAdmin.

In phpMyAdmin, log in with the MySQL application credentials from `.env`:

```text
Server: db
Username: value of MYSQL_USER
Password: value of MYSQL_PASSWORD
Database: mauban_waste_collection
```

The first database startup loads [schema.sql](./schema.sql). For an existing
database that predates collector-to-route scheduling, apply
[migrations/002_assign_collectors_to_routes.sql](./migrations/002_assign_collectors_to_routes.sql)
once. Then use **Admin → Manage trucks** to link each active driver to a
collector login and route. The route must have an active schedule for location
sharing to be accepted and shown on the public map. GPS uploads are accepted
only during that route's active weekday/time window in the Philippines time
zone; the public map hides locations older than 60 seconds and hides the marker
when it cannot verify an active assignment.

The schema creates the foundation tables for users, routes, route points,
vehicles, drivers, schedules, and GPS locations. It also inserts development
records for the three sample routes and two sample vehicles. These seed
records are safe to re-run because they use idempotent inserts.

Stop the containers with `docker compose down`. This keeps the persisted MySQL
data.

For production, put the app behind an HTTPS reverse proxy and set
`SESSION_COOKIE_SECURE=1` in `.env`. Do not expose the container directly over
plain HTTP.

### Local web server

1. Place the project in your PHP web server's document root.
2. Create the `mauban_waste_collection` MySQL database and configure the
   connection settings in `config.php`.
3. Configure the `ADMIN_USERNAME`, `ADMIN_PASSWORD`, `DEVELOPER_USERNAME`, and
   `DEVELOPER_PASSWORD` environment variables for the PHP web server. For
   Apache, set them in the appropriate virtual
   host or server configuration, then restart Apache. Use a unique, strong
   password and do not commit credentials.
4. Open the project through the local web server.

The environment credentials bootstrap configured administrator and developer accounts. Drivers can register from the Login page, and administrators can create administrator or driver accounts from Manage Accounts. Residents use the public pages without signing in.

### Vercel deployment

Follow these steps in order. The Vercel deployment runs the PHP/Apache
application from the root [Dockerfile](./Dockerfile); it does not run
`docker-compose.yml` or a MySQL server. Aiven stores the application data and
PHP sessions. Do not put database passwords or other secrets in this README,
GitHub, or screenshots.

#### 1. Prepare Aiven MySQL

1. In the [Aiven Console](https://console.aiven.io/), create or select a MySQL
   service and wait until its status is **Running**.
2. Open the service's connection information. Copy the hostname, port, database
   name, username, and password into a private password manager or temporary
   private note. Use the values Aiven shows; the port and database name may not
   be the examples in this guide.
3. Download the Aiven CA certificate. Keep the complete PEM text, including
   the `BEGIN CERTIFICATE` and `END CERTIFICATE` lines. Do not disable TLS
   verification.
4. Configure Aiven's network access for the Vercel app. Allow only the outbound
   IP address or range supported by your Vercel plan. If you cannot determine
   or restrict the Vercel egress address, check your hosting/network options
   before continuing; do not make the database publicly accessible to all IPs.

**Check before continuing:** The Aiven service is **Running**, you have the
connection details and CA certificate saved privately, and the database can
accept connections from the Vercel deployment's restricted network.

#### 2. Connect MySQL Workbench and prepare the schema

1. In MySQL Workbench, create a connection using Aiven's hostname, port,
   username, and password. In the connection's **SSL** settings, enable SSL
   and select the downloaded Aiven CA certificate. Select **Test Connection**.
2. If this is a **new, empty database**, open [schema.sql](./schema.sql) in
   Workbench, select the target database/schema in the **SCHEMAS** panel, and
   execute the script once. It creates the application tables and sample route
   and vehicle records.
3. If this is an **existing database**, do not execute `schema.sql` over it.
   Back up the database first, inspect the existing tables, and apply only the
   migrations it still needs. For Vercel's persistent logins, execute
   [migration 005](./migrations/005_add_mysql_sessions.sql) once if the
   `app_sessions` table is missing. See the
   [migration checklist](./CONTRIBUTING.md#6-database-schema-and-migrations)
   before applying any other migration.
4. In Workbench, refresh **SCHEMAS** and confirm the database contains at least
   `users`, `routes`, `vehicles`, `drivers`, `schedule`, `gps_locations`, and
   `app_sessions`.

**Check before continuing:** Workbench's connection test succeeds and all
required tables are visible. Do not continue if Workbench reports an SSL,
permission, or database-selection error.

#### 3. Import the project into Vercel

1. Push the latest project changes to the GitHub branch you want to deploy.
   Ensure the Vercel deployment will include the MySQL session handler and
   [migration 005](./migrations/005_add_mysql_sessions.sql).
2. In Vercel, select **Add New → Project**, import this GitHub repository, and
   choose the correct repository and production branch.
3. Select the **Container** framework preset. Keep the project root as the
   **Root Directory** so Vercel can find the repository's root `Dockerfile`.
   Vercel runs the application container only; it does not start the local
   database from Docker Compose.

**Check before continuing:** The Vercel project is linked to the intended
repository and branch, and its settings show the Container preset and the
repository root as the root directory.

#### 4. Add Vercel environment variables

In Vercel, open **Project → Settings → Environment Variables**. Add each
variable below separately to **Production**. Copy Aiven's actual connection
values; do not use the example text as credentials.

| Variable | Value |
| --- | --- |
| `DB_HOST` | Aiven hostname |
| `DB_PORT` | Aiven port |
| `DB_NAME` | Aiven database name |
| `DB_USER` | Aiven username |
| `DB_PASS` | Aiven password |
| `DB_SSL_CA_PEM` | Complete Aiven CA PEM certificate, including both certificate lines |
| `APP_ENV` | `production` |
| `SESSION_STORE` | `mysql` |
| `SESSION_COOKIE_SECURE` | `1` |
| `ADMIN_USERNAME` | A unique production admin username |
| `ADMIN_PASSWORD` | A strong, unique production admin password |
| `DEVELOPER_USERNAME` | A unique production developer username |
| `DEVELOPER_PASSWORD` | A strong, unique production developer password |
| `COLLECTOR_USERNAME` | Leave blank unless you intentionally use a bootstrap collector |
| `COLLECTOR_PASSWORD` | Leave blank unless you intentionally use a bootstrap collector |

`DB_SSL_CA_PEM` is a certificate, not a password; paste the full PEM without
adding quotes. Keep passwords in Vercel's encrypted environment-variable
settings. `MYSQL_*` values in `.env` and `docker-compose.yml` are for local
Docker only; the deployed app reads the `DB_*` values above. Do not add
production database credentials to **Preview** deployments unless you have
intentionally chosen to let preview builds use that database.

**Check before continuing:** Each variable is saved under the correct name and
Production environment. Reveal/check values privately in Vercel; do not paste
them into chat, GitHub, or logs. Redeploy after changing variables so the new
deployment receives them.

#### 5. Deploy and check the first response

1. Select **Deploy** in Vercel. Wait for the build and deployment to finish.
2. If the build fails, open the Vercel deployment's **Build Logs** and fix the
   reported error before retrying.
3. Open the deployment URL over **HTTPS**. Check that the home page loads, then
   open the schedule, routes, and live-tracking pages.
4. If a page shows the generic service-unavailable message, check the
   deployment's **Runtime Logs**. Confirm the Aiven hostname, port, database,
   username, password, SSL CA, and network allowlist. Production fails closed
   when the CA certificate is missing or invalid.

**Check before continuing:** The deployment reports **Ready**, the home page
loads over HTTPS, and database-backed public pages display without a
service-unavailable error.

#### 6. Check admin login and persistent sessions

1. Open the login page and sign in with the production admin username and
   password configured in Vercel. On the first successful login, the app
   creates the configured account in the `users` table. Existing database
   accounts take precedence over bootstrap credentials.
2. Open an admin-only page, then navigate to another protected page. Refresh
   the page and confirm you remain signed in.
3. In Workbench, refresh the `app_sessions` table and confirm a session row
   exists. Do not share or publish session data.
4. Sign out and confirm a protected page sends you back to login.

**Check before continuing:** Admin login succeeds, protected pages retain the
login between requests, the database has a session row, and logout ends the
session. If login fails, check Vercel **Runtime Logs**, the configured
credentials, and whether that username already has an account in `users`.

#### 7. Prepare real service data and test tracking

The new-database schema includes sample route and vehicle records. Before
announcing the site, sign in as the admin and review **Manage routes**,
**Manage schedules**, and **Manage trucks**. Replace sample information with
approved service data, assign a collector account and vehicle to an active
route, and ensure the route has an active schedule for the current Philippines
time.

Then test from a phone over the HTTPS deployment:

- Register or configure a collector account and assign it to the correct
  vehicle and route.
- Sign in as that collector, allow location access, and start sharing while
  the route's schedule is active.
- Open **Live Tracking** and check that the latest location appears.
- Stop sharing and confirm the collector page reports that tracking stopped.

**Final check:** Public information is correct, admin and collector logins
work, the site is HTTPS, and tracking only appears for a currently active,
assigned collector. Browser location sharing requires HTTPS and permission.

Vercel's application filesystem is ephemeral. Do not rely on local files or
PHP's default file sessions for persistent data; this deployment uses Aiven
MySQL for both application data and sessions. MySQL-backed sessions expire
after 30 days of inactivity.

### Privacy and security

The application uses a necessary secure session cookie, CSRF protection on
state-changing forms, password hashing, server-side input validation, and
recent-only GPS retention for live tracking. Self-registered collectors must
accept the [Privacy Policy](./privacy.php) and [Terms of Service](./terms.php).
The [Cookie Policy](./cookies.php) explains the necessary session cookie and
the local-only notice acknowledgement.

For older databases, apply
[migrations/004_add_privacy_consent_metadata.sql](./migrations/004_add_privacy_consent_metadata.sql)
only if its columns are missing. See the [migration guide](./CONTRIBUTING.md#5-database-schema-and-migrations).
Do not commit `.env` files, passwords, database credentials, or API tokens.
