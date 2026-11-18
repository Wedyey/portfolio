<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/contact.php';

portfolioRequireAdmin();
$database = portfolioDatabase();
$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portfolioVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $message = 'The form expired. Reload the page and try again.';
        $isError = true;
    } else {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $action = $_POST['action'] ?? '';

        try {
            if ($id !== false && $action === 'read') {
                portfolioSetMessageRead($database, $id, true);
                $message = 'Message marked as read.';
            } elseif ($id !== false && $action === 'unread') {
                portfolioSetMessageRead($database, $id, false);
                $message = 'Message marked as unread.';
            } elseif ($id !== false && $action === 'delete') {
                portfolioDeleteMessage($database, $id);
                $message = 'Message deleted.';
            } else {
                http_response_code(400);
                $message = 'Unknown message action.';
                $isError = true;
            }
        } catch (PDOException $error) {
            error_log('Message operation failed: ' . $error->getMessage());
            http_response_code(503);
            $message = 'Unable to update the message.';
            $isError = true;
        }
    }
}

$messages = portfolioLoadMessages($database);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact messages</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<main class="page-main">
    <section class="page-header">
        <h1>Contact <span>Messages</span></h1>
        <p><a href="dashboard.php">Back to dashboard</a></p>
    </section>
    <?php if ($message !== ''): ?>
        <p role="status"><?= portfolioEscape($message) ?></p>
    <?php endif; ?>
    <?php foreach ($messages as $item): ?>
        <article class="skill-card">
            <h2><?= portfolioEscape((string) $item['subject']) ?></h2>
            <p><strong>From:</strong> <?= portfolioEscape((string) $item['name']) ?> (<?= portfolioEscape((string) $item['email']) ?>)</p>
            <p><?= nl2br(portfolioEscape((string) $item['message'])) ?></p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <input type="hidden" name="action" value="<?= (int) $item['is_read'] === 1 ? 'unread' : 'read' ?>">
                <button type="submit"><?= (int) $item['is_read'] === 1 ? 'Mark unread' : 'Mark read' ?></button>
            </form>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit">Delete</button>
            </form>
        </article>
    <?php endforeach; ?>
    <?php if ($messages === []): ?>
        <p>No contact messages yet.</p>
    <?php endif; ?>
</main>
</body>
</html>
