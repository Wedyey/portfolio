<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin.php';

portfolioRequireAdmin();

try {
    $statistics = portfolioDashboardStats(portfolioDatabase());
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('The dashboard is temporarily unavailable.');
}

$adminEmail = (string) ($_SESSION['admin_email'] ?? '');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portfolio dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
    <main class="page-main">
        <section class="page-header">
            <h1>Admin <span>Dashboard</span></h1>
            <p>Signed in as <?= portfolioEscape($adminEmail) ?></p>
        </section>
        <section class="admin-stats" aria-label="Portfolio statistics">
            <article class="admin-stat-card"><h2>Projects</h2><p><?= $statistics['projects'] ?></p></article>
            <article class="admin-stat-card"><h2>Skills</h2><p><?= $statistics['skills'] ?></p></article>
            <article class="admin-stat-card"><h2>Messages</h2><p><?= $statistics['messages'] ?></p></article>
            <article class="admin-stat-card"><h2>Experience</h2><p><?= $statistics['experience'] ?></p></article>
        </section>
        <nav class="admin-nav" aria-label="Content management">
            <a href="profile.php">Profile<span>Edit your public details</span></a>
            <a href="skills.php">Skills<span>Organize your expertise</span></a>
            <a href="projects.php">Projects<span>Manage your portfolio work</span></a>
            <a href="resume.php">Resume<span>Update education and experience</span></a>
            <a href="messages.php">Messages<span>Review visitor messages</span></a>
            <a href="settings.php">Settings<span>Update site details</span></a>
        </nav>
        <p><a class="admin-back-link" href="../index.html">View public portfolio</a></p>
        <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
            <button class="main-btn" type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
