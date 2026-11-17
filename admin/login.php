<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin.php';

try {
    $database = portfolioDatabase();
    $adminExists = (int) $database->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Administrator login is temporarily unavailable.');
}

$errorMessage = '';
portfolioStartSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminExists) {
    if (!portfolioVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $errorMessage = 'The form expired. Reload the page and try again.';
    } elseif (portfolioAdminLoginBlocked()) {
        http_response_code(429);
        $errorMessage = 'Too many failed attempts. Wait 15 minutes before trying again.';
    } else {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        try {
            if (is_string($email) && is_string($password)
                && portfolioAdminLogin($database, $email, $password)) {
                portfolioClearAdminLoginFailures();
                header('Location: dashboard.php', true, 303);
                exit;
            }
        } catch (PDOException $error) {
            error_log('Administrator login failed: ' . $error->getMessage());
            http_response_code(503);
            $errorMessage = 'Administrator login is temporarily unavailable.';
        }

        if ($errorMessage === '') {
            portfolioRecordAdminLoginFailure();
            http_response_code(portfolioAdminLoginBlocked() ? 429 : 401);
            $errorMessage = 'Invalid email or password.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrator login</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <main class="page-main">
        <section class="page-header">
            <h1>Administrator <span>Login</span></h1>
            <?php if (!$adminExists): ?>
                <p>No administrator is set up yet. <a href="setup.php">Create the first account</a>.</p>
            <?php else: ?>
                <p>Sign in to manage your portfolio.</p>
            <?php endif; ?>
        </section>
        <?php if ($adminExists): ?>
            <form id="contact-form" method="post">
                <?php if ($errorMessage !== ''): ?>
                    <p role="alert"><?= portfolioEscape($errorMessage) ?></p>
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" maxlength="254" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required>
                </div>
                <button class="main-btn" type="submit">Log in</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
