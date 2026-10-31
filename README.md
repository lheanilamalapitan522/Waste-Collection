# Mauban Waste Collection

A PHP and MySQL waste collection management application with route scheduling,
administration, and live tracking.

## Setup

1. Place the project in your PHP web server's document root.
2. Create the `mauban_waste_collection` MySQL database and configure the
   connection settings in `config.php`.
3. Configure the `ADMIN_USERNAME` and `ADMIN_PASSWORD` environment variables
   for the PHP web server. For Apache, set them in the appropriate virtual
   host or server configuration, then restart Apache. Use a unique, strong
   password and do not commit credentials.
4. Open the project through the local web server.

The admin login is disabled until both environment variables are set.
