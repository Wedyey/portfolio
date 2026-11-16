# Roejie Damaso Portfolio

This repository contains the existing responsive portfolio website and its MySQL schema. The public pages are currently static HTML; PHP features are being added incrementally.

## Requirements

- PHP 8.0 or later with PDO and the PDO MySQL driver enabled
- MySQL 8.0 or later
- Apache or another PHP-capable web server

## Database setup

1. Create/import the database by running [`database/portfolio.sql`](database/portfolio.sql) in MySQL or phpMyAdmin.
2. Configure these environment variables for the PHP web-server process:
   - `PORTFOLIO_DB_HOST`
   - `PORTFOLIO_DB_PORT` (optional; defaults to `3306`)
   - `PORTFOLIO_DB_NAME`
   - `PORTFOLIO_DB_USER`
   - `PORTFOLIO_DB_PASSWORD`
3. Include `includes/db.php` from a PHP page and call `portfolioDatabase()` to get the PDO connection.

For local XAMPP development, these variables can be set in the Apache configuration with `SetEnv` directives. Keep real credentials out of this repository, and use a non-empty, least-privilege database password outside local development.

The connection uses UTF-8, exception-based error reporting, associative fetches, and native prepared statements. Connection details are read from the server environment and are not stored in source code.
