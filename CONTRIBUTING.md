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

## 2. Get the source

Fork the repository on GitHub, then clone your fork:

```sh
git clone https://github.com/<your-github-name>/Waste-Collection.git
cd Waste-Collection
```

To contribute to the original project, add it as the `upstream` remote and get
the latest branch information:

```sh
git remote add upstream https://github.com/lheanilamalapitan522/Waste-Collection.git
git fetch upstream
```

Create a feature branch from the project's default branch before editing:

```sh
git switch -c feature/short-description upstream/main
```

If `upstream` already exists, do not add it again. Use `git remote -v` to check
which repositories `origin` and `upstream` point to.

## 3. Configure a local `.env`

Copy `.env.example` to `.env` in the project root, then replace every
`CHANGE_ME` value with a unique local password. The example admin and developer
passwords meet the application's minimum rule of eight characters with an
uppercase letter, lowercase letter, and number; replace them before use.

Do not reuse production, Aiven, Vercel, or personal account credentials. `.env`
is ignored by Git; never force-add it or paste its contents into a commit,
issue, or pull request.

For local Docker, `MYSQL_*` values configure the MySQL container. Compose maps
them into the app's `DB_*` settings. `SESSION_COOKIE_SECURE=0` is for local HTTP
only; deployments behind HTTPS must use `SESSION_COOKIE_SECURE=1`. Leave the
optional collector bootstrap values blank and register a collector through the
site so its driver record is created correctly.

## 4. Start the application

From the project folder, run:

```sh
docker compose up --build
```

Wait until the app, database, and phpMyAdmin containers are running, then open:

- Application: <http://localhost:8080>
- phpMyAdmin: <http://localhost:8081>

The ports are bound to localhost. In phpMyAdmin, select server `db` and use
`MYSQL_USER` and `MYSQL_PASSWORD` from `.env`.

On its first initialization, the MySQL volume imports `schema.sql`. The
environment-configured admin and developer accounts are created in the users
table after each account's first successful sign-in, if no account with that
username already exists. Existing database accounts take precedence; changing
an environment password does not replace an existing password hash.

Stop the containers with `docker compose down`; this keeps the database volume.
Do not use `docker compose down -v` unless you intend to delete the local
database and all its data.

## 5. Database schema and migrations

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

These SQL migrations are not all safe to rerun. Do not run them just because
they are present in the repository. Never apply development migrations to a
production database without the project owner's review and a verified backup.
If a database has only some columns from a migration, do not run that migration
file as-is; review it and apply only the missing schema changes with a backup.

## 6. Create accounts and prepare tracking data

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

## 7. Manual requirement checklist

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

## 8. Troubleshooting

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

## 9. Pull requests

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
