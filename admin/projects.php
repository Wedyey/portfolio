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
        $action = $_POST['action'] ?? '';
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $removeImage = isset($_POST['remove_image']);

        try {
            if ($action === 'delete' && $id !== false) {
                $statement = $database->prepare('SELECT image_path FROM projects WHERE id = :id');
                $statement->execute(['id' => $id]);
                $imagePath = $statement->fetchColumn();

                portfolioDeleteProject($database, $id);

                if (is_string($imagePath) && $imagePath !== '') {
                    portfolioDeleteUploadedImage($imagePath);
                }

                $message = 'Project deleted.';
            } elseif ($action === 'save') {
                [$values, $error] = portfolioValidateProject($_POST);

                if ($error !== null) {
                    $message = $error;
                    $isError = true;
                } elseif ($id !== false) {
                    $statement = $database->prepare('SELECT image_path FROM projects WHERE id = :id');
                    $statement->execute(['id' => $id]);
                    $currentImage = $statement->fetchColumn();
                    $currentImage = is_string($currentImage) ? $currentImage : '';

                    [$uploadedPath, $uploadError] = portfolioSaveUploadedImage($_FILES['image'] ?? [], 'project');

                    if ($uploadError !== null) {
                        $message = $uploadError;
                        $isError = true;
                    } else {
                        $values['image_path'] = $currentImage;

                        if ($uploadedPath !== null) {
                            if ($currentImage !== '') {
                                portfolioDeleteUploadedImage($currentImage);
                            }
                            $values['image_path'] = $uploadedPath;
                        } elseif ($removeImage && $currentImage !== '') {
                            portfolioDeleteUploadedImage($currentImage);
                            $values['image_path'] = '';
                        }

                        portfolioSaveProject($database, $values, $id);
                        $message = 'Project updated.';
                    }
                } else {
                    [$uploadedPath, $uploadError] = portfolioSaveUploadedImage($_FILES['image'] ?? [], 'project');

                    if ($uploadError !== null) {
                        $message = $uploadError;
                        $isError = true;
                    } else {
                        $values['image_path'] = (string) $uploadedPath;
                        portfolioSaveProject($database, $values);
                        $message = 'Project added.';
                    }
                }
            } else {
                http_response_code(400);
                $message = 'Unknown action.';
                $isError = true;
            }
        } catch (PDOException $error) {
            error_log('Project operation failed: ' . $error->getMessage());
            http_response_code(503);
            $message = 'Unable to save the change.';
            $isError = true;
        }
    }
}

try {
    $projects = portfolioLoadProjects($database);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Project management is temporarily unavailable.');
}

$editing = null;
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($editId !== false) {
    foreach ($projects as $project) {
        if ((int) $project['id'] === $editId) {
            $editing = $project;
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
    <title>Manage projects</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <main class="page-main">
        <section class="page-header">
            <h1>Manage <span>Projects</span></h1>
            <p><a href="dashboard.php">Back to dashboard</a></p>
        </section>
        <?php if ($message !== ''): ?>
            <p role="status"><?= portfolioEscape($message) ?></p>
        <?php endif; ?>
        <section class="skills-container" aria-label="Project editor">
            <article class="skill-card">
                <h2><?= $editing === null ? 'Add a project' : 'Edit project' ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                    <input type="hidden" name="action" value="save">
                    <?php if ($editing !== null): ?>
                        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input id="title" type="text" name="title" maxlength="200" required value="<?= portfolioEscape((string) ($editing['title'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" maxlength="5000" required><?= portfolioEscape((string) ($editing['description'] ?? '')) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="technologies">Technologies</label>
                        <input id="technologies" type="text" name="technologies" maxlength="2000" value="<?= portfolioEscape((string) ($editing['technologies'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input id="category" type="text" name="category" maxlength="100" value="<?= portfolioEscape((string) ($editing['category'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="project_url">Live URL (optional)</label>
                        <input id="project_url" type="url" name="project_url" value="<?= portfolioEscape((string) ($editing['project_url'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="source_url">Source URL (optional)</label>
                        <input id="source_url" type="url" name="source_url" value="<?= portfolioEscape((string) ($editing['source_url'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="image">Image (JPG, PNG, WebP or GIF, max 2 MB)</label>
                        <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                        <?php if (($editing['image_path'] ?? '') !== ''): ?>
                            <img src="../<?= portfolioEscape((string) $editing['image_path']) ?>" alt="Current project image" width="120">
                            <label><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort order</label>
                        <input id="sort_order" type="number" name="sort_order" min="0" max="10000" value="<?= portfolioEscape((string) ($editing['sort_order'] ?? '0')) ?>">
                    </div>
                    <button class="main-btn" type="submit"><?= $editing === null ? 'Add project' : 'Save changes' ?></button>
                    <?php if ($editing !== null): ?>
                        <a href="projects.php">Cancel</a>
                    <?php endif; ?>
                </form>
            </article>
        </section>
        <table>
            <caption>Existing projects</caption>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Image</th>
                    <th>Sort</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($projects === []): ?>
                    <tr>
                        <td colspan="5">No projects yet. Add your first one above.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= portfolioEscape((string) $project['title']) ?></td>
                        <td><?= portfolioEscape((string) $project['category']) ?></td>
                        <td>
                            <?php if ((string) $project['image_path'] !== ''): ?>
                                <img src="../<?= portfolioEscape((string) $project['image_path']) ?>" alt="" width="60">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $project['sort_order'] ?></td>
                        <td>
                            <a href="projects.php?edit=<?= (int) $project['id'] ?>">Edit</a>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= portfolioEscape(portfolioCsrfToken()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
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