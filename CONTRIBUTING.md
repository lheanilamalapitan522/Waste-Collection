# Contributor Setup and Development Guide

This guide covers local installation, database setup, first-run accounts,
manual requirement checks, and the pull-request workflow. For the project scope
and user roles, see [README.md](README.md).

## 1. Requirements

### Recommended: Docker

- Git
- Docker Desktop with Docker Compose v2, running before you start the project
- A current desktop or mobile browser
- Internet access for Bootstrap assets and OpenStreetMap embeds

Docker Compose runs PHP 8.2 with Apache, MySQL 8.0, and phpMyAdmin. No Composer
or npm installation is currently required. Maps use OpenStreetMap embeds and
do not need a Google Maps API key.

### Optional: run PHP and MySQL yourself

- PHP 8.2 with `pdo_mysql` enabled
- MySQL 8.0
- Apache or another PHP-capable web server
- A browser

Docker is the documented and recommended local development environment.

## 2. Connect GitHub Desktop

1. Install [GitHub Desktop](https://desktop.github.com/) and sign in to
   GitHub.com from **File → Options → Accounts** (Windows). Approve the browser
   authorization request; never paste a password or access token into the
   project.
2. Make sure you can access the repository on GitHub. A maintainer can invite
   you under **Settings → Collaborators** and grant **Write** access. Accept
   the invitation before cloning. If you do not have write access, fork the
   repository on GitHub and work in your fork instead.
3. In GitHub Desktop, choose **File → Clone repository**, select
   `lheanilamalapitan522/Waste-Collection` (or paste the repository URL), set
   the local path, and select **Clone**. Open the cloned folder in VS Code.
4. In the **Current branch** menu, select `main`, choose **New branch**, and
   create a descriptive branch such as `feature/update-schedules`. Do not
   develop directly on `main`.
5. After making and checking changes, enter a commit summary and select
   **Commit to feature/...**. Select **Publish branch** or **Push origin** to
   upload the branch.
6. Select **Create Pull Request** in GitHub Desktop. Check that the base is
   `main` and the compare branch is your feature branch, then create the pull
   request on GitHub. Reviewers can discuss changes and merge the pull request
   there.

Direct pushes to `main` may be blocked by repository branch protections. Write
access allows a contributor to push branches and merge pull requests when
repository rules permit it; it does not grant administrator access.

## 3. Get the source with Git

If you prefer the command line, fork the repository on GitHub, then clone your
fork:

```sh
git clone https://github.com/<your-github-name>/Waste-Collection.git
cd Waste-Collection
```

For a fork, add the original repository as `upstream` and fetch its branches:

```sh
git remote add upstream https://github.com/lheanilamalapitan522/Waste-Collection.git
git fetch upstream
git switch -c feature/short-description upstream/main
```

If you have Write access and cloned the original repository instead, update
`origin` and create the feature branch from its `main` branch:

```sh
git fetch origin
git switch -c feature/short-description origin/main
```

If `upstream` already exists, do not add it again. Use `git remote -v` to check
which repositories `origin` and `upstream` point to.

## 4. Configure a local `.env`

Copy `.env.example` to `.env` in the project root, then set unique, strong
values for `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `ADMIN_PASSWORD`, and
`DEVELOPER_PASSWORD`. The template intentionally leaves passwords blank so
credentials are not shared between contributors.

Do not reuse production, Aiven, Vercel, or personal account credentials. `.env`
is ignored by Git; never force-add it or paste its contents into a commit,
issue, or pull request.

For local Docker, `MYSQL_*` values configure the MySQL container. Compose maps
them into the app's `DB_*` settings. `SESSION_COOKIE_SECURE=0` is for local HTTP
only; deployments behind HTTPS must use `SESSION_COOKIE_SECURE=1`. Leave the
optional collector bootstrap values blank and register a collector through the
site so its driver record is created correctly.

## 5. Connect Docker Desktop and start the application

1. Install and start Docker Desktop. Wait for the Docker engine to report that
   it is running; keeping the Docker Desktop window open is not required once
   the engine is ready.
2. Open the cloned project folder in VS Code. In the integrated terminal,
   confirm it is at the repository root and create the private environment
   file, replacing the blank password values with unique local passwords:

   ```powershell
   Copy-Item .env.example .env
   ```

3. Check that Docker Compose can read the configuration, then start the services:

   ```sh
   docker compose config --quiet
   docker compose up --build
   ```

Alternatively, in VS Code select **Terminal → Run Task** and run
**Start development (Docker Compose)**. This builds and starts the services in
the background. Run **Stop development (Docker Compose)** or `docker compose
down` to stop them. Both options preserve the database volume.

4. Wait until **Docker Desktop → Containers** or `docker compose ps` shows the
   database as healthy and the app and phpMyAdmin as running. Then open:

   - Application: <http://localhost:8080>
   - phpMyAdmin: <http://localhost:8081>

The ports are bound to this computer. The app connects to MySQL using the
Compose-only hostname `db` on port `3306`; do not change it to `localhost`
inside the app container. To connect phpMyAdmin, select server `db`, then sign
in with `MYSQL_USER` and `MYSQL_PASSWORD` from `.env`. phpMyAdmin is available
at `http://localhost:8081`; the website is at `http://localhost:8080`.

On its first initialization, the MySQL volume imports `schema.sql`. The
environment-configured admin and developer accounts are created in the users
table after each account's first successful sign-in, if no account with that
username already exists. Existing database accounts take precedence; changing
an environment password does not replace an existing password hash.

Stop the containers with `docker compose down`; this keeps the database volume.
Do not use `docker compose down -v` unless you intend to delete the local
database and all its data.

## 6. Database schema and migrations

### New local database

`schema.sql` is the complete baseline schema. It already includes the resident
role, collector-to-route fields, and privacy-consent metadata. Do **not** run
migrations `001`, `002`, or `004` on a database created from the current
`schema.sql`; those migrations add fields that are already present.

Migration `003_enable_daily_route_schedule.sql` is optional test data. The
baseline schema already has weekday schedules; this migration adds any missing
active Route 1 days using the `8:00 AM - 11:00 AM` time window. Use it only if
that daily test schedule is useful for your local database.

### Existing database

Back up the database first. Migrations are manual and are not automatically
applied by the application. Inspect the current schema, then apply only missing
migrations, once each, in numeric order:

| Migration | Apply only if |
| --- | --- |
| `001_add_resident_role.sql` | `users.role` does not include `resident`. |
| `002_assign_collectors_to_routes.sql` | Both `drivers.user_id` and `drivers.route_id` are missing because this migration has not yet been applied. |
| `003_enable_daily_route_schedule.sql` | You want its optional daily Route 1 test schedule. |
| `004_add_privacy_consent_metadata.sql` | Both `users.privacy_consented_at` and `users.privacy_policy_version` are missing because this migration has not yet been applied. |
| `005_add_mysql_sessions.sql` | The `app_sessions` table is missing and the application will use MySQL-backed sessions. |

These SQL migrations are not all safe to rerun. Do not run them just because
they are present in the repository. Never apply development migrations to a
production database without the project owner's review and a verified backup.
If a database has only some columns from a migration, do not run that migration
file as-is; review it and apply only the missing schema changes with a backup.

## 7. Create accounts and prepare tracking data

1. Sign in with the local `ADMIN_USERNAME` and `ADMIN_PASSWORD` from `.env`.
   On a new database, the configured account is created on its first successful
   login.
2. Register a collector from the Login page. Registration requires a username,
   a password of at least eight characters containing uppercase, lowercase,
   and a number, an active route, and acceptance of the Terms and Privacy
   Policy.
3. In the admin sidebar, open **Manage trucks** and edit a truck to assign an
   active driver, that driver's collector login, and an active route.
4. In **Manage schedules**, confirm that the assigned route has an active
   schedule for the current weekday and time. Duty checks use `Asia/Manila`
   server time.
5. Sign in as the collector on `http://localhost:8080` or an HTTPS deployment.
   Allow browser location permission and keep the collector page open while
   testing. Browser geolocation is not reliable after the page or browser is
   closed or suspended in the background.
6. Open **Live Tracking**. A location is accepted only for the active assigned
   driver, truck, route, and schedule. The public map hides locations older
   than 60 seconds and hides the marker when no eligible recent point exists.

Residents do not need accounts. They can view public schedules, routes, and the
tracking page without signing in.

## 8. Manual requirement checklist

There is no automated test suite or package-manager test command in this
repository. Before submitting a change, manually check the areas it affects.

### Public information

- Open the home, schedule, route, and live-tracking pages without signing in.
- Confirm public route and schedule details are readable on desktop and mobile.
- Confirm route-stop information agrees with the selected service data.
- Confirm no truck marker appears for missing, stale, or off-duty locations.

### Authentication and privacy

- Confirm valid admin and collector logins reach their role-appropriate pages.
- Confirm an invalid password is rejected and protected pages redirect
  unauthenticated users to Login.
- Confirm registration rejects weak passwords, duplicate usernames, missing
  routes, and missing privacy consent.
- Confirm credentials are absent from the Git diff.

### Admin service management

- Confirm route, schedule, and truck sections open on separate sidebar pages.
- Confirm details are read-only until **Edit** is selected.
- Confirm editing one truck opens and submits only that truck's form.
- Confirm route stops retain their order and coordinate validation.
- Confirm schedule or assignment changes affect which collector locations are
  accepted and shown on the public map.

### GPS tracking

- On localhost or HTTPS, allow location permission and confirm a valid update
  is accepted during an assigned active schedule.
- Confirm denied permission, unavailable GPS, invalid coordinates, failed
  requests, and off-duty updates do not report successful sharing.
- Confirm the public location endpoint returns a recent eligible location and
  returns no location when the point is stale or the collector is off duty.

For real-phone checks, use a consenting test account and stop sharing when the
test is complete.

## 9. Troubleshooting

- **Database unavailable:** Check `docker compose ps` and `docker compose logs
  app db`. Confirm `.env` values are consistent and the database is healthy.
- **Tables are missing after editing `schema.sql`:** MySQL imports the schema
  only when the data volume is first created. Apply the required migration to
  an existing volume; do not delete a volume that contains data.
- **Login rejects an environment password:** If the username already exists in
  `users`, its database password hash takes precedence over environment
  bootstrap credentials. Reset it through the admin workflow or use a clean
  local database for a fresh setup.
- **Collector cannot share:** Check browser permission, internet access, active
  driver and truck assignments, an active route, and a schedule containing the
  current `Asia/Manila` weekday and time.
- **Location does not appear:** The map only shows a point received in the last
  60 seconds. Keep the collector page open and check its status and app logs.
- **Port already in use:** Stop the process using port 8080 or 8081, or adjust
  the host port in `docker-compose.yml`.

## 10. Connect Vercel to an external MySQL database

Local Docker uses a MySQL container; a deployed Vercel app needs a separately
hosted database reachable over the network. These steps use Aiven for MySQL.

1. In the [Aiven Console](https://console.aiven.io/), create or select a project,
   choose **Create service → MySQL**, select an available plan and region, and
   create the service. Wait until its status is **Running**. See Aiven's
   [MySQL getting-started guide](https://aiven.io/docs/products/mysql/get-started)
   for current console navigation.
2. Open the service's **Overview** or **Quick connect** panel and record the
   hostname, port, database name, username, and generated password. Use the
   service's displayed values; do not assume the default port is `3306`. Keep
   all connection details private.
3. Download the service CA certificate from its connection details. Keep the
   complete PEM certificate, including the `BEGIN CERTIFICATE` and `END
   CERTIFICATE` lines. Aiven documents the connection details in its
   [MySQL connection guide](https://aiven.io/docs/products/mysql/howto/connect-from-cli).
   The application uses this CA to verify the database server's TLS certificate;
   do not turn off certificate verification.
4. Configure Aiven's allowed IP addresses/network access for the app's outbound
   connection. Prefer a fixed egress IP and allow only that address or range.
   If the deployment does not provide a fixed egress address, do not expose
   the database to all IP addresses by default; choose a hosting/network setup
   with a supported restricted connection.
5. Initialize a new database with [schema.sql](schema.sql). For an existing
   database, make a backup and follow the migration checks in
   [Section 6](#6-database-schema-and-migrations). Do not reinitialize or
   overwrite a database that contains data. For an existing database, apply
   [migrations/005_add_mysql_sessions.sql](migrations/005_add_mysql_sessions.sql)
   once after taking a backup; Vercel needs this table to persist logins across
   container instances. You can apply the schema or migration in MySQL
   Workbench: connect to Aiven using its hostname and port, select **Use SSL**
   with the downloaded CA certificate, select the target database in
   **SCHEMAS**, then open the SQL file and execute it.
   Alternatively, from Git Bash opened at the project folder, apply a new
   database schema using the MySQL CLI with TLS verification enabled. Replace
   the example connection values and save Aiven's downloaded CA certificate
   locally:

   ```sh
   mysql --ssl-mode=VERIFY_IDENTITY --ssl-ca="C:/path/to/aiven-ca.pem" \
     --host="your-aiven-host" --port=your-aiven-port \
     --user=avnadmin --password defaultdb < schema.sql
   ```

   The client prompts for the password. Do not put it directly in the command
   or terminal history.
6. In Vercel, import the GitHub repository and select the Container preset so
   Vercel builds the root `Dockerfile`. Under **Project → Settings →
   Environment Variables**, add these values to the **Production** environment
   (and **Preview** only if preview deployments should use this database):

   ```text
   DB_HOST=<aiven hostname>
   DB_PORT=<aiven port>
   DB_NAME=<aiven database name>
   DB_USER=<aiven username>
   DB_PASS=<aiven password>
   DB_SSL_CA_PEM=<paste the complete Aiven CA PEM certificate>
   APP_ENV=production
   SESSION_STORE=mysql
   ADMIN_USERNAME=<production admin username>
   ADMIN_PASSWORD=<unique strong production password>
   DEVELOPER_USERNAME=<production developer username>
   DEVELOPER_PASSWORD=<unique strong production password>
   COLLECTOR_USERNAME=
   COLLECTOR_PASSWORD=
   SESSION_COOKIE_SECURE=1
   ```

   Store these values in Vercel's encrypted environment-variable settings, not
   in Git, the repository `.env`, or deployment logs. Paste the CA certificate
   as a multiline PEM value; it is a public certificate, not a password.
7. Save the variables and redeploy so the app receives them. Open the deployed
   HTTPS URL and verify the homepage and database-backed pages. If the app
   returns a database-unavailable response, verify the host,
   port, database, credentials, provider network allowlist, and the provider's
   CA certificate. Production connections without a valid `DB_SSL_CA_PEM` fail
   closed rather than silently connecting without TLS verification.

Vercel environment variables use the app's `DB_*` names. `MYSQL_*` settings
in `.env` and `docker-compose.yml` are only for the local Compose database.
The deployment uses an external database; its filesystem is ephemeral, so
database records must be stored in MySQL, not in application files. The
`DB_SSL_CA_PEM` setting is also available for other MySQL providers that supply
a PEM CA certificate. MySQL-backed sessions expire after 30 days of
inactivity.

## 11. Pull requests

- Work on a named feature branch; do not commit directly to `main`.
- Keep each pull request focused and explain the changed behavior and manual
  checks performed.
- Include schema migrations when a database change is required, and update
  this guide or README when setup behavior changes.
- Review `git status` and `git diff` before committing. Never include `.env`,
  production database dumps, real GPS traces, or credentials.
- Open a pull request from your fork's feature branch to the original project's
  default branch. Maintainers review and merge it.

For deployment setup, see the Vercel section in [README.md](README.md). Local
`.env` values are separate from Vercel environment variables; never copy local
development passwords into production.
