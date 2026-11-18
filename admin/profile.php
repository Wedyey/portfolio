<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/content.php';

portfolioRequireAdmin();

try {
    $database = portfolioDatabase();
    $profile = portfolioLoadProfile($database) ?? [
        'name' => '',
        'headline' => '',
        'introduction' => '',
        'biography' => '',
        'email' => '',
        'phone' => '',
        'location' => '',
    ];
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Profile settings are temporarily unavailable.');
}

$message = '';
$isSaved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portfolioVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $message = 'The form expired. Reload the page and try again.';
    } else {
        $result = portfolioValidateProfile($_POST);
        $profile = $result['values'];

        if ($result['errors']) {
            $message = reset($result['errors']);
        } else {
            try {
                portfolioSaveProfile($database, $profile);
                $message = 'Profile saved.';
                $isSaved = true;
            } catch (PDOException $error) {
                error_log('Profile update failed: ' . $error->getMessage());
                http_response_code(503);
                $message = 'Unable to save profile changes.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit portfolio profile</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
    <main class="page-main">
        <section class="page-header">
            <h1>Edit <span>Profile</span></h1>
            <p><a class="admin-back-link" href="dashboard.php">Back to dashboard</a></p>
        </section>
        <form id="contact-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
            <?php if ($message !== ''): ?>
                <p role="status"><?= portfolioEscape($message) ?></p>
            <?php endif; ?>
            <?php foreach (['name' => ['Name', 150], 'headline' => ['Headline', 200], 'email' => ['Email', 254], 'phone' => ['Phone', 50], 'location' => ['Location', 150]] as $field => $details): ?>
                <div class="form-group">
                    <label for="<?= portfolioEscape($field) ?>"><?= portfolioEscape($details[0]) ?></label>
                    <input id="<?= portfolioEscape($field) ?>" type="<?= $field === 'email' ? 'email' : 'text' ?>" name="<?= portfolioEscape($field) ?>" value="<?= portfolioEscape((string) $profile[$field]) ?>" maxlength="<?= $details[1] ?>" <?= $field === 'name' ? 'required' : '' ?>>
                </div>
            <?php endforeach; ?>
            <div class="form-group">
                <label for="introduction">Introduction</label>
                <textarea id="introduction" name="introduction" rows="4" maxlength="5000" required><?= portfolioEscape((string) $profile['introduction']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="biography">Biography</label>
                <textarea id="biography" name="biography" rows="6" maxlength="5000" required><?= portfolioEscape((string) $profile['biography']) ?></textarea>
            </div>
            <button class="main-btn" type="submit">Save profile</button>
        </form>
        <?php if ($isSaved): ?>
            <p>Changes will appear on the public pages when they load database content.</p>
        <?php endif; ?>
    </main>
</body>
</html>
