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

### Docker

1. Create a `.env` file in the project root and set `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `ADMIN_USERNAME`, and `ADMIN_PASSWORD`. Set `DEVELOPER_USERNAME` and `DEVELOPER_PASSWORD` to enable developer login. Optional driver bootstrap credentials are `COLLECTOR_USERNAME` and `COLLECTOR_PASSWORD`.
2. Maps on the home, route, and live-tracking pages use OpenStreetMap embeds and do not require a map API key. Location markers are shown as page overlays while the embedded map recenters on the reported coordinates.
3. Start the application:

   ```sh
   docker compose up --build
   ```

The app container mounts the project folder read-only and watches PHP, CSS,
and JavaScript files during development. Save a change and the open page at
`http://localhost:8080` reloads automatically. Rebuild only when changing the
Dockerfile or Apache configuration.

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

### Vercel test deployment

This project can be deployed to Vercel with the Container runtime because the
repository includes a root `Dockerfile`. The Vercel deployment runs only the
PHP/Apache application; it does not run `docker-compose.yml` or a local MySQL
container.

1. Create a MySQL database with an external provider that allows connections
   from Vercel.
2. Import [schema.sql](./schema.sql) into that database. Apply
   [migrations/002_assign_collectors_to_routes.sql](./migrations/002_assign_collectors_to_routes.sql)
   as well when upgrading an existing database.
3. Import this repository into Vercel and select **Container** as the framework
   preset. The root `Dockerfile` is used automatically.
4. Add these Vercel environment variables for the deployment:

   ```text
   DB_HOST=your-mysql-host
   DB_PORT=your-mysql-port
   DB_NAME=your-database-name
   DB_USER=your-database-user
   DB_PASS=your-database-password
   ADMIN_USERNAME=your-admin-username
   ADMIN_PASSWORD=your-strong-admin-password
   DEVELOPER_USERNAME=your-developer-username
   DEVELOPER_PASSWORD=your-strong-developer-password
   COLLECTOR_USERNAME=
   COLLECTOR_PASSWORD=
   SESSION_COOKIE_SECURE=1
   ```

   Use Vercel's encrypted environment-variable settings instead of committing
   these values to the repository. `MYSQL_*` variables in `.env` and
   `docker-compose.yml` configure local Docker only; the deployed application
   reads the `DB_*` variables above.
5. Deploy and open the generated Vercel URL. Use the Vercel URL over HTTPS so
   secure session cookies and browser geolocation work correctly.

Vercel deployments use an ephemeral, read-only application filesystem and do
not persist uploaded files or a local database. Application data must remain
in the external MySQL database.
