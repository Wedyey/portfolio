<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin.php';
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
        $type = $_POST['type'] ?? '';
        $action = $_POST['action'] ?? '';
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        try {
            if ($type === 'education') {
                [$values, $error] = portfolioValidateEducation($_POST);
                if ($action === 'delete' && $id !== false) {
                    portfolioDeleteEducation($database, $id);
                    $message = 'Education record deleted.';
                } elseif ($error !== null) {
                    $message = $error;
                    $isError = true;
                } else {
                    portfolioSaveEducation($database, $values, $id === false ? null : $id);
                    $message = $id === false ? 'Education record added.' : 'Education record updated.';
                }
            } elseif ($type === 'experience') {
                [$values, $error] = portfolioValidateExperience($_POST);
                if ($action === 'delete' && $id !== false) {
                    portfolioDeleteExperience($database, $id);
                    $message = 'Experience record deleted.';
                } elseif ($error !== null) {
                    $message = $error;
                    $isError = true;
                } else {
                    portfolioSaveExperience($database, $values, $id === false ? null : $id);
                    $message = $id === false ? 'Experience record added.' : 'Experience record updated.';
                }
            } elseif ($type === 'achievement') {
                [$values, $error] = portfolioValidateAchievement($_POST);
                if ($action === 'delete' && $id !== false) {
                    portfolioDeleteAchievement($database, $id);
                    $message = 'Achievement deleted.';
                } elseif ($error !== null) {
                    $message = $error;
                    $isError = true;
                } else {
                    portfolioSaveAchievement($database, $values, $id === false ? null : $id);
                    $message = $id === false ? 'Achievement added.' : 'Achievement updated.';
                }
            } else {
                $message = 'Unknown resume section.';
                $isError = true;
            }
        } catch (PDOException $error) {
            error_log('Resume operation failed: ' . $error->getMessage());
            http_response_code(503);
            $message = 'Unable to save the resume change.';
            $isError = true;
        }
    }
}

$education = $database->query('SELECT * FROM education ORDER BY sort_order, id')->fetchAll();
$experience = $database->query('SELECT * FROM experience ORDER BY sort_order, id')->fetchAll();
$achievements = $database->query('SELECT * FROM achievements ORDER BY sort_order, id')->fetchAll();

$recordsByType = [
    'education' => $education,
    'experience' => $experience,
    'achievement' => $achievements,
];
$editParameters = [
    'education' => 'edit_education',
    'experience' => 'edit_experience',
    'achievement' => 'edit_achievement',
];
$editing = [
    'education' => null,
    'experience' => null,
    'achievement' => null,
];

foreach ($editParameters as $type => $parameter) {
    $editId = filter_var($_GET[$parameter] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($editId === false) {
        continue;
    }

    foreach ($recordsByType[$type] as $record) {
        if ((int) $record['id'] === $editId) {
            $editing[$type] = $record;
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
    <title>Manage resume</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
<main class="page-main">
    <section class="page-header">
        <h1>Manage <span>Resume</span></h1>
        <p><a class="admin-back-link" href="dashboard.php">Back to dashboard</a></p>
    </section>
    <?php if ($message !== ''): ?>
        <p role="status"><?= portfolioEscape($message) ?></p>
    <?php endif; ?>

    <section class="resume-container">
        <div class="resume-section" id="education">
            <h2>Education</h2>
            <form class="admin-form-grid" method="post">
                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                <input type="hidden" name="type" value="education">
                <input type="hidden" name="action" value="save">
                <?php if ($editing['education'] !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editing['education']['id'] ?>">
                <?php endif; ?>
                <label>Institution <input type="text" name="institution" maxlength="200" required value="<?= portfolioEscape((string) ($editing['education']['institution'] ?? '')) ?>"></label>
                <label>Qualification <input type="text" name="qualification" maxlength="200" required value="<?= portfolioEscape((string) ($editing['education']['qualification'] ?? '')) ?>"></label>
                <label>Field of study <input type="text" name="field_of_study" maxlength="200" value="<?= portfolioEscape((string) ($editing['education']['field_of_study'] ?? '')) ?>"></label>
                <label>Start date <input type="date" name="start_date" value="<?= portfolioEscape((string) ($editing['education']['start_date'] ?? '')) ?>"></label>
                <label>End date <input type="date" name="end_date" value="<?= portfolioEscape((string) ($editing['education']['end_date'] ?? '')) ?>"></label>
                <label class="form-group-wide">Description <textarea name="description" maxlength="5000"><?= portfolioEscape((string) ($editing['education']['description'] ?? '')) ?></textarea></label>
                <label>Sort order <input type="number" name="sort_order" min="0" max="10000" value="<?= portfolioEscape((string) ($editing['education']['sort_order'] ?? '0')) ?>"></label>
                <div class="form-actions">
                    <button class="main-btn" type="submit"><?= $editing['education'] === null ? 'Add education' : 'Save education' ?></button>
                    <?php if ($editing['education'] !== null): ?>
                        <a href="resume.php#education">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php foreach ($education as $record): ?>
                <article>
                    <h3><?= portfolioEscape((string) $record['qualification']) ?></h3>
                    <p><?= portfolioEscape((string) $record['institution']) ?></p>
                    <div class="resume-record-actions">
                        <a href="resume.php?edit_education=<?= (int) $record['id'] ?>#education">Edit</a>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                            <input type="hidden" name="type" value="education">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                            <button class="button-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="resume-section" id="experience">
            <h2>Experience</h2>
            <form class="admin-form-grid" method="post">
                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                <input type="hidden" name="type" value="experience">
                <input type="hidden" name="action" value="save">
                <?php if ($editing['experience'] !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editing['experience']['id'] ?>">
                <?php endif; ?>
                <label>Organization <input type="text" name="organization" maxlength="200" required value="<?= portfolioEscape((string) ($editing['experience']['organization'] ?? '')) ?>"></label>
                <label>Role <input type="text" name="role" maxlength="200" required value="<?= portfolioEscape((string) ($editing['experience']['role'] ?? '')) ?>"></label>
                <label>Start date <input type="date" name="start_date" value="<?= portfolioEscape((string) ($editing['experience']['start_date'] ?? '')) ?>"></label>
                <label>End date <input type="date" name="end_date" value="<?= portfolioEscape((string) ($editing['experience']['end_date'] ?? '')) ?>"></label>
                <label class="form-group-wide">Description <textarea name="description" maxlength="5000"><?= portfolioEscape((string) ($editing['experience']['description'] ?? '')) ?></textarea></label>
                <label>Sort order <input type="number" name="sort_order" min="0" max="10000" value="<?= portfolioEscape((string) ($editing['experience']['sort_order'] ?? '0')) ?>"></label>
                <div class="form-actions">
                    <button class="main-btn" type="submit"><?= $editing['experience'] === null ? 'Add experience' : 'Save experience' ?></button>
                    <?php if ($editing['experience'] !== null): ?>
                        <a href="resume.php#experience">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php foreach ($experience as $record): ?>
                <article>
                    <h3><?= portfolioEscape((string) $record['role']) ?></h3>
                    <p><?= portfolioEscape((string) $record['organization']) ?></p>
                    <div class="resume-record-actions">
                        <a href="resume.php?edit_experience=<?= (int) $record['id'] ?>#experience">Edit</a>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                            <input type="hidden" name="type" value="experience">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                            <button class="button-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="resume-section" id="achievements">
            <h2>Achievements</h2>
            <form class="admin-form-grid" method="post">
                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                <input type="hidden" name="type" value="achievement">
                <input type="hidden" name="action" value="save">
                <?php if ($editing['achievement'] !== null): ?>
                    <input type="hidden" name="id" value="<?= (int) $editing['achievement']['id'] ?>">
                <?php endif; ?>
                <label>Title <input type="text" name="title" maxlength="200" required value="<?= portfolioEscape((string) ($editing['achievement']['title'] ?? '')) ?>"></label>
                <label>Issuer <input type="text" name="issuer" maxlength="200" value="<?= portfolioEscape((string) ($editing['achievement']['issuer'] ?? '')) ?>"></label>
                <label>Awarded date <input type="date" name="awarded_on" value="<?= portfolioEscape((string) ($editing['achievement']['awarded_on'] ?? '')) ?>"></label>
                <label class="form-group-wide">Description <textarea name="description" maxlength="5000"><?= portfolioEscape((string) ($editing['achievement']['description'] ?? '')) ?></textarea></label>
                <label>Sort order <input type="number" name="sort_order" min="0" max="10000" value="<?= portfolioEscape((string) ($editing['achievement']['sort_order'] ?? '0')) ?>"></label>
                <div class="form-actions">
                    <button class="main-btn" type="submit"><?= $editing['achievement'] === null ? 'Add achievement' : 'Save achievement' ?></button>
                    <?php if ($editing['achievement'] !== null): ?>
                        <a href="resume.php#achievements">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php foreach ($achievements as $record): ?>
                <article>
                    <h3><?= portfolioEscape((string) $record['title']) ?></h3>
                    <p><?= portfolioEscape((string) $record['issuer']) ?></p>
                    <div class="resume-record-actions">
                        <a href="resume.php?edit_achievement=<?= (int) $record['id'] ?>#achievements">Edit</a>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                            <input type="hidden" name="type" value="achievement">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $record['id'] ?>">
                            <button class="button-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
