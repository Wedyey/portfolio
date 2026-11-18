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

The database seed includes the four existing portfolio projects and their images and descriptions. Re-running the SQL file adds missing starter projects without replacing existing project records.

For local XAMPP development, these variables can be set in the Apache configuration with `SetEnv` directives. Keep real credentials out of this repository, and use a non-empty, least-privilege database password outside local development.

The connection uses UTF-8, exception-based error reporting, associative fetches, and native prepared statements. Connection details are read from the server environment and are not stored in source code.

## Shared security helpers

Include `includes/security.php` before sending page output. It provides `portfolioEscape()` for HTML output, `portfolioCsrfToken()` and `portfolioVerifyCsrfToken()` for form tokens, and session helpers that set secure cookie options and rotate the session ID after login.

## Administrator setup

1. Import `database/portfolio.sql` into MySQL.
2. Start Apache and MySQL, and configure the `PORTFOLIO_DB_*` environment variables.
3. Open `admin/setup.php` once to create the first administrator account.
4. Open `admin/login.php` to sign in.
5. Use `admin/dashboard.php`, profile, skills, projects, resume, messages, and settings pages to manage content.

Do not put a real password or database password in source files or SQL commits. The setup page accepts a password of at least 12 characters and stores only a PHP password hash.

## Public features

The existing `.html` pages remain available. Projects, skills, education, experience, and achievements can be loaded from the database through `api/content.php`. The contact page stores visitor messages in MySQL after CSRF and server-side validation.

Uploaded project images are limited to JPG, PNG, WebP, and GIF, with a 2 MB maximum, real-content validation, and unique filenames in `uploads/`.
