<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/functions.php';

portfolioRequireAdmin();
$database = portfolioDatabase();
$message = '';
$settings = portfolioLoadSettings($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portfolioVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $message = 'The form expired. Reload the page and try again.';
    } else {
        $values = [
            'site_title' => portfolioTrimText($_POST, 'site_title', 150),
            'site_description' => portfolioTrimText($_POST, 'site_description', 300),
            'logo_path' => portfolioTrimText($_POST, 'logo_path', 255),
            'facebook_url' => portfolioTrimUrl($_POST, 'facebook_url', 2048),
        ];

        try {
            portfolioSaveSettings($database, $values);
            $settings = portfolioLoadSettings($database);
            $message = 'Website settings saved.';
        } catch (PDOException $error) {
            error_log('Settings update failed: ' . $error->getMessage());
            http_response_code(503);
            $message = 'Unable to save website settings.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Website settings</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
<main class="page-main">
    <section class="page-header">
        <h1>Website <span>Settings</span></h1>
        <p><a class="admin-back-link" href="dashboard.php">Back to dashboard</a></p>
    </section>
    <?php if ($message !== ''): ?>
        <p role="status"><?= portfolioEscape($message) ?></p>
    <?php endif; ?>
    <form id="contact-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
        <div class="form-group">
            <label for="site_title">Site title</label>
            <input id="site_title" name="site_title" maxlength="150" value="<?= portfolioEscape($settings['site_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="site_description">Site description</label>
            <textarea id="site_description" name="site_description" maxlength="300"><?= portfolioEscape($settings['site_description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="logo_path">Logo path (optional)</label>
            <input id="logo_path" name="logo_path" maxlength="255" value="<?= portfolioEscape($settings['logo_path'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="facebook_url">Facebook URL</label>
            <input id="facebook_url" type="url" name="facebook_url" value="<?= portfolioEscape($settings['facebook_url'] ?? '') ?>">
        </div>
        <button class="main-btn" type="submit">Save settings</button>
    </form>
</main>
</body>
</html>
