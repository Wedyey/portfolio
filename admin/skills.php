<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/functions.php';

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
        $action = $_POST['action'] ?? '';
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        try {
            if ($action === 'save' && $id === false) {
                [$values, $error] = portfolioValidateSkill($_POST);

                if ($error !== null) {
                    $message = $error;
                    $isError = true;
                } else {
                    portfolioSaveSkill($database, $values);
                    $message = 'Skill added.';
                }
            } elseif ($action === 'save') {
                [$values, $error] = portfolioValidateSkill($_POST);

                if ($error !== null) {
                    $message = $error;
                    $isError = true;
                } else {
                    portfolioSaveSkill($database, $values, $id);
                    $message = 'Skill updated.';
                }
            } elseif ($action === 'delete' && $id !== false) {
                portfolioDeleteSkill($database, $id);
                $message = 'Skill deleted.';
            } else {
                http_response_code(400);
                $message = 'Unknown action.';
                $isError = true;
            }
        } catch (PDOException $error) {
            error_log('Skill operation failed: ' . $error->getMessage());
            http_response_code(503);
            $message = 'Unable to save the change.';
            $isError = true;
        }
    }
}

try {
    $skills = portfolioLoadSkills($database);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Skills management is temporarily unavailable.');
}

$editing = null;
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($editId !== false) {
    foreach ($skills as $skill) {
        if ((int) $skill['id'] === $editId) {
            $editing = $skill;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage skills</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body class="admin-page">
    <main class="page-main">
        <section class="page-header">
            <h1>Manage <span>Skills</span></h1>
            <p><a class="admin-back-link" href="dashboard.php">Back to dashboard</a></p>
        </section>
        <?php if ($message !== ''): ?>
            <p role="status"><?= portfolioEscape($message) ?></p>
        <?php endif; ?>
        <section class="skills-container" aria-label="Existing skills">
            <article class="skill-card">
                <h2><?= $editing === null ? 'Add a skill' : 'Edit skill' ?></h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                    <input type="hidden" name="action" value="save">
                    <?php if ($editing !== null): ?>
                        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" type="text" name="name" maxlength="100" required value="<?= portfolioEscape((string) ($editing['name'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input id="category" type="text" name="category" maxlength="100" value="<?= portfolioEscape((string) ($editing['category'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="proficiency">Proficiency (0-100, optional)</label>
                        <input id="proficiency" type="number" name="proficiency" min="0" max="100" value="<?= portfolioEscape((string) ($editing['proficiency'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort order</label>
                        <input id="sort_order" type="number" name="sort_order" min="0" max="10000" value="<?= portfolioEscape((string) ($editing['sort_order'] ?? '0')) ?>">
                    </div>
                    <button class="main-btn" type="submit"><?= $editing === null ? 'Add skill' : 'Save changes' ?></button>
                    <?php if ($editing !== null): ?>
                        <a href="skills.php">Cancel</a>
                    <?php endif; ?>
                </form>
            </article>
        </section>
        <table>
            <caption>Existing skills</caption>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Proficiency</th>
                    <th>Sort</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($skills === []): ?>
                    <tr>
                        <td colspan="5">No skills yet. Add your first one above.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($skills as $skill): ?>
                    <tr>
                        <td><?= portfolioEscape((string) $skill['name']) ?></td>
                        <td><?= portfolioEscape((string) $skill['category']) ?></td>
                        <td><?= $skill['proficiency'] === null ? '—' : (int) $skill['proficiency'] ?></td>
                        <td><?= (int) $skill['sort_order'] ?></td>
                        <td>
                            <a href="skills.php?edit=<?= (int) $skill['id'] ?>">Edit</a>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $skill['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>