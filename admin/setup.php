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
    exit('Administrator setup is temporarily unavailable.');
}

if ($adminExists) {
    http_response_code(404);
    exit('Administrator setup is closed.');
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portfolioVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $errorMessage = 'The form expired. Reload the page and try again.';
    } elseif (is_string($_POST['email'] ?? null) && is_string($_POST['password'] ?? null)) {
        try {
            portfolioCreateFirstAdmin($database, $_POST['email'], $_POST['password']);
            header('Location: login.php?setup=complete', true, 303);
            exit;
        } catch (InvalidArgumentException $error) {
            $errorMessage = $error->getMessage();
        } catch (PDOException $error) {
            error_log('Administrator setup failed: ' . $error->getMessage());
            $errorMessage = 'Unable to create the administrator account.';
        } catch (RuntimeException $error) {
            http_response_code(404);
            $errorMessage = 'Administrator setup is closed.';
        }
    } else {
        $errorMessage = 'Enter a valid email address and password.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up administrator</title>
</head>
<body>
    <main>
        <h1>Set up administrator</h1>
        <p>Create the first account to access portfolio management.</p>
        <?php if ($errorMessage !== ''): ?>
            <p role="alert"><?= portfolioEscape($errorMessage) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
            <label>Email <input type="email" name="email" maxlength="254" required></label>
            <label>Password <input type="password" name="password" minlength="12" required></label>
            <button type="submit">Create administrator</button>
        </form>
    </main>
</body>
</html>
