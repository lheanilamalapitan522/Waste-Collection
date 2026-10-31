# Mauban Waste Collection

A PHP and MySQL waste collection management application with route scheduling,
administration, and live tracking.

## Phase 1: Requirements and Scope

The system provides online waste-collection information for residents and
operational tools for authorized municipal personnel in Mauban, Quezon.

### User roles

- **Residents:** View the public landing page, collection schedules, assigned
  routes, and the current location/status of collection vehicles.
- **Drivers:** Sign in securely, start or stop location sharing during a
  collection operation, and view their assigned route.
- **Administrators:** Sign in securely, manage drivers and vehicles, maintain
  routes and schedules, and monitor active collection vehicles.
- **Developers:** Sign in securely to access the maintenance dashboard and
  system update tools without receiving administrator or collector permissions.

The public homepage is the default entry point. Residents can browse public
schedule, route, and tracking information without signing in. The Login page accepts only a username and password. An administrator creates
each account and assigns its role; the assigned role is stored in the database
and determines which protected pages and actions the account can access.

The collector location-sharing page requires browser location permission and
HTTPS in production. `http://localhost:8080` is permitted for local testing.

### Functional requirements

1. Provide a public, mobile-responsive website.
2. Display collection schedules by service area or barangay.
3. Display assigned collection routes on an interactive map.
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
  browsers with JavaScript enabled for interactive maps and tracking.

### In scope

- Public landing page
- Collection schedules
- Existing route display and route monitoring
- GPS-based vehicle location display
- Driver and administrator authentication
- Vehicle, route, schedule, and driver management
- Interactive map display
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

1. Copy `.env.example` to `.env` and set the database and account credentials.
2. Start the application:

   ```sh
   docker compose up --build
   ```

3. Open `http://localhost:8080`. The Docker port is bound to localhost only.
4. Open `http://localhost:8081` to open phpMyAdmin.

In phpMyAdmin, log in with the MySQL application credentials from `.env`:

```text
Server: db
Username: value of MYSQL_USER
Password: value of MYSQL_PASSWORD
Database: mauban_waste_collection
```

The first database startup loads [schema.sql](./schema.sql). If the database
volume was created before the schema was added, run
`docker compose down -v` once and then start the services again.

The schema creates the foundation tables for users, developer maintenance
tasks, routes, route points, vehicles, drivers, schedules, and GPS locations.
It also inserts development records for the three sample routes and two sample vehicles. These seed
records are safe to re-run because they use idempotent inserts.

For an existing database volume, apply the `developer_maintenance_tasks`
`CREATE TABLE` statement from `schema.sql` once; Docker only runs the schema
automatically when it initializes a new database volume.

Stop the containers with `docker compose down`. Add `-v` when stopping if you
also want to remove the persisted MySQL data.

For production, put the app behind an HTTPS reverse proxy and set
`SESSION_COOKIE_SECURE=1` in `.env`. Do not expose the container directly over
plain HTTP.

### Local web server

1. Place the project in your PHP web server's document root.
2. Create the `mauban_waste_collection` MySQL database and configure the
   connection settings in `config.php`.
3. Configure `ADMIN_USERNAME` and `ADMIN_PASSWORD` for the PHP web server.
   Optionally set `DEVELOPER_USERNAME` and `DEVELOPER_PASSWORD` to bootstrap a
   developer account the same way. For Apache, set these in the appropriate
   virtual host or server configuration, then restart Apache. Use unique,
   strong passwords and do not commit credentials.
4. Open the project through the local web server.

The environment credentials bootstrap the configured admin, collector, or
developer account on its first successful login. After that, accounts should
be created and managed from the Admin Manage Users page; users cannot select or
change their role on the login form. Developer accounts open the protected
Developer Maintenance dashboard, where they can start tasks, record system
updates, and mark work complete. Maintenance history is stored in
`developer_maintenance_tasks`.
