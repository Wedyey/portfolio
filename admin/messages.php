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
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
<main class="page-main">
    <section class="page-header">
        <h1>Contact <span>Messages</span></h1>
        <p><a class="admin-back-link" href="dashboard.php">Back to dashboard</a></p>
    </section>
    <?php if ($message !== ''): ?>
        <p role="status"><?= portfolioEscape($message) ?></p>
    <?php endif; ?>
    <section class="message-list" aria-label="Received messages">
        <?php foreach ($messages as $item): ?>
            <?php $isRead = (int) $item['is_read'] === 1; ?>
            <article class="message-card<?= $isRead ? '' : ' message-card-unread' ?>">
                <header class="message-card-header">
                    <div>
                        <h2><?= portfolioEscape((string) ($item['subject'] ?: 'No subject')) ?></h2>
                        <p class="message-sender"><?= portfolioEscape((string) $item['name']) ?> · <?= portfolioEscape((string) $item['email']) ?></p>
                    </div>
                    <span class="message-badge<?= $isRead ? '' : ' message-badge-unread' ?>"><?= $isRead ? 'Read' : 'New' ?></span>
                </header>
                <p class="message-date"><?= portfolioEscape(date('M j, Y · g:i a', strtotime((string) $item['created_at']))) ?></p>
                <p class="message-body"><?= nl2br(portfolioEscape((string) $item['message'])) ?></p>
                <div class="message-actions">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="action" value="<?= $isRead ? 'unread' : 'read' ?>">
                        <button class="button-secondary" type="submit"><?= $isRead ? 'Mark unread' : 'Mark read' ?></button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="button-danger" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if ($messages === []): ?>
            <div class="message-empty">
                <h2>You're all caught up</h2>
                <p>New messages from the contact form will appear here.</p>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
