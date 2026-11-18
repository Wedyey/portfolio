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
</head>
<body>
    <main class="page-main">
        <section class="page-header">
            <h1>Admin <span>Dashboard</span></h1>
            <p>Signed in as <?= portfolioEscape($adminEmail) ?></p>
        </section>
        <section class="skills-container" aria-label="Portfolio statistics">
            <article class="skill-card"><h2>Projects</h2><p><?= $statistics['projects'] ?></p></article>
            <article class="skill-card"><h2>Skills</h2><p><?= $statistics['skills'] ?></p></article>
            <article class="skill-card"><h2>Messages</h2><p><?= $statistics['messages'] ?></p></article>
            <article class="skill-card"><h2>Experience</h2><p><?= $statistics['experience'] ?></p></article>
        </section>
        <nav aria-label="Content management">
            <p><a href="profile.php">Manage profile</a></p>
            <p><a href="skills.php">Manage skills</a></p>
            <p><a href="projects.php">Manage projects</a></p>
            <p><a href="resume.php">Manage resume</a></p>
            <p><a href="messages.php">Manage contact messages</a></p>
            <p><a href="settings.php">Manage website settings</a></p>
        </nav>
        <p><a href="../index.html">View public portfolio</a></p>
        <form method="post" action="logout.php">
            <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
            <button class="main-btn" type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
