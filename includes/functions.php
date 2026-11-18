<?php

declare(strict_types=1);

/**
 * Shared helpers for admin CRUD pages and secure file uploads.
 */

require_once __DIR__ . '/security.php';

function portfolioTrimText(array $input, string $field, int $limit): string
{
    $value = $input[$field] ?? '';

    if (!is_string($value)) {
        return '';
    }

    $value = trim($value);

    return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit) : $value;
}

function portfolioTrimDate(array $input, string $field): ?string
{
    $value = $input[$field] ?? '';

    if (!is_string($value) || $value === '') {
        return null;
    }

    $date = DateTime::createFromFormat('Y-m-d', trim($value));

    if ($date === false || $date->format('Y-m-d') !== trim($value)) {
        return null;
    }

    return $date->format('Y-m-d');
}

function portfolioTrimInt(array $input, string $field, int $max): ?int
{
    $value = filter_var($input[$field] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 0, 'max_range' => $max],
    ]);

    return $value === false ? null : $value;
}

function portfolioTrimUrl(array $input, string $field, int $limit): string
{
    $value = portfolioTrimText($input, $field, $limit);

    if ($value === '') {
        return '';
    }

    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        return '';
    }

    return $value;
}

/**
 * Store an uploaded image in uploads/ after validating its real content.
 * Returns [path, error]: path is web-relative ("uploads/xxx.ext") on success.
 */
function portfolioSaveUploadedImage(array $file, string $prefix): array
{
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $maxBytes = 2 * 1024 * 1024;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [null, 'The image upload failed. Please try again.'];
    }

    if (!is_array($file) || !is_string($file['tmp_name'] ?? null) || $file['tmp_name'] === '') {
        return [null, 'The image upload failed. Please try again.'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return [null, 'The image upload was rejected by the server.'];
    }

    if (($file['size'] ?? 0) > $maxBytes) {
        return [null, 'Images must be 2 MB or smaller.'];
    }

    // Validate the actual bytes: both the file signature and MIME detection.
    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        return [null, 'That file is not a valid image.'];
    }

    $detectedMime = $imageInfo['mime'] ?? '';

    if (!isset($allowedTypes[$detectedMime])) {
        return [null, 'Only JPG, PNG, WebP and GIF images are allowed.'];
    }

    $finfoMime = '';

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo !== false) {
            $finfoMime = (string) finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }

    if ($finfoMime !== '' && $finfoMime !== $detectedMime) {
        return [null, 'That file is not a valid image.'];
    }

    $directory = dirname(__DIR__) . '/uploads';

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        return [null, 'Unable to prepare the upload directory.'];
    }

    $filename = sprintf(
        '%s_%s.%s',
        preg_replace('/[^a-z0-9]+/', '_', strtolower($prefix)),
        bin2hex(random_bytes(8)),
        $allowedTypes[$detectedMime]
    );
    $target = $directory . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return [null, 'Unable to store the uploaded image.'];
    }

    return ['uploads/' . $filename, null];
}

/** Delete an image that was previously stored inside uploads/. */
function portfolioDeleteUploadedImage(?string $path): void
{
    if (!is_string($path)
        || !str_starts_with($path, 'uploads/')
        || str_contains($path, '..')
        || substr_count($path, '/') !== 1) {
        return;
    }

    $fullPath = dirname(__DIR__) . '/' . $path;

    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

// ---------------------------------------------------------------------------
// Skills CRUD
// ---------------------------------------------------------------------------

function portfolioValidateSkill(array $input): array
{
    $values = [
        'name' => portfolioTrimText($input, 'name', 100),
        'category' => portfolioTrimText($input, 'category', 100),
        'proficiency' => portfolioTrimInt($input, 'proficiency', 100),
        'sort_order' => portfolioTrimInt($input, 'sort_order', 10000) ?? 0,
    ];

    if ($values['name'] === '') {
        return [null, 'Skill name is required.'];
    }

    return [$values, null];
}

function portfolioSaveSkill(PDO $database, array $values, ?int $id = null): void
{
    if ($id === null) {
        $query = 'INSERT INTO skills (name, category, proficiency, sort_order) '
            . 'VALUES (:name, :category, :proficiency, :sort_order)';
    } else {
        $query = 'UPDATE skills SET name = :name, category = :category, proficiency = :proficiency, '
            . 'sort_order = :sort_order WHERE id = :id';
        $values['id'] = $id;
    }

    $statement = $database->prepare($query);
    $statement->execute($values);
}

function portfolioDeleteSkill(PDO $database, int $id): void
{
    $statement = $database->prepare('DELETE FROM skills WHERE id = :id');
    $statement->execute(['id' => $id]);
}

// ---------------------------------------------------------------------------
// Projects CRUD
// ---------------------------------------------------------------------------

function portfolioValidateProject(array $input): array
{
    $values = [
        'title' => portfolioTrimText($input, 'title', 200),
        'description' => portfolioTrimText($input, 'description', 5000),
        'technologies' => portfolioTrimText($input, 'technologies', 2000),
        'category' => portfolioTrimText($input, 'category', 100),
        'project_url' => portfolioTrimUrl($input, 'project_url', 2048),
        'source_url' => portfolioTrimUrl($input, 'source_url', 2048),
        'sort_order' => portfolioTrimInt($input, 'sort_order', 10000) ?? 0,
    ];

    if ($values['title'] === '' || $values['description'] === '') {
        return [null, 'Project title and description are required.'];
    }

    return [$values, null];
}

function portfolioSaveProject(PDO $database, array $values, ?int $id = null): void
{
    if ($id === null) {
        $query = 'INSERT INTO projects (title, description, technologies, category, project_url, source_url, sort_order) '
            . 'VALUES (:title, :description, :technologies, :category, :project_url, :source_url, :sort_order)';
    } else {
        $query = 'UPDATE projects SET title = :title, description = :description, technologies = :technologies, '
            . 'category = :category, project_url = :project_url, source_url = :source_url, sort_order = :sort_order '
            . 'WHERE id = :id';
        $values['id'] = $id;
    }

    $statement = $database->prepare($query);
    $statement->execute($values);
}

function portfolioDeleteProject(PDO $database, int $id): void
{
    $statement = $database->prepare('DELETE FROM projects WHERE id = :id');
    $statement->execute(['id' => $id]);
}

// ---------------------------------------------------------------------------
// Education CRUD
// ---------------------------------------------------------------------------

function portfolioValidateEducation(array $input): array
{
    $values = [
        'institution' => portfolioTrimText($input, 'institution', 200),
        'qualification' => portfolioTrimText($input, 'qualification', 200),
        'field_of_study' => portfolioTrimText($input, 'field_of_study', 200),
        'start_date' => portfolioTrimDate($input, 'start_date'),
        'end_date' => portfolioTrimDate($input, 'end_date'),
        'description' => portfolioTrimText($input, 'description', 5000),
        'sort_order' => portfolioTrimInt($input, 'sort_order', 10000) ?? 0,
    ];

    if ($values['institution'] === '' || $values['qualification'] === '') {
        return [null, 'Institution and qualification are required.'];
    }

    return [$values, null];
}

function portfolioSaveEducation(PDO $database, array $values, ?int $id = null): void
{
    if ($id === null) {
        $query = 'INSERT INTO education (institution, qualification, field_of_study, start_date, end_date, description, sort_order) '
            . 'VALUES (:institution, :qualification, :field_of_study, :start_date, :end_date, :description, :sort_order)';
    } else {
        $query = 'UPDATE education SET institution = :institution, qualification = :qualification, '
            . 'field_of_study = :field_of_study, start_date = :start_date, end_date = :end_date, '
            . 'description = :description, sort_order = :sort_order WHERE id = :id';
        $values['id'] = $id;
    }

    $statement = $database->prepare($query);
    $statement->execute($values);
}

function portfolioDeleteEducation(PDO $database, int $id): void
{
    $statement = $database->prepare('DELETE FROM education WHERE id = :id');
    $statement->execute(['id' => $id]);
}

// ---------------------------------------------------------------------------
// Experience CRUD
// ---------------------------------------------------------------------------

function portfolioValidateExperience(array $input): array
{
    $values = [
        'organization' => portfolioTrimText($input, 'organization', 200),
        'role' => portfolioTrimText($input, 'role', 200),
        'start_date' => portfolioTrimDate($input, 'start_date'),
        'end_date' => portfolioTrimDate($input, 'end_date'),
        'description' => portfolioTrimText($input, 'description', 5000),
        'sort_order' => portfolioTrimInt($input, 'sort_order', 10000) ?? 0,
    ];

    if ($values['organization'] === '' || $values['role'] === '') {
        return [null, 'Organization and role are required.'];
    }

    return [$values, null];
}

function portfolioSaveExperience(PDO $database, array $values, ?int $id = null): void
{
    if ($id === null) {
        $query = 'INSERT INTO experience (organization, role, start_date, end_date, description, sort_order) '
            . 'VALUES (:organization, :role, :start_date, :end_date, :description, :sort_order)';
    } else {
        $query = 'UPDATE experience SET organization = :organization, role = :role, start_date = :start_date, '
            . 'end_date = :end_date, description = :description, sort_order = :sort_order WHERE id = :id';
        $values['id'] = $id;
    }

    $statement = $database->prepare($query);
    $statement->execute($values);
}

function portfolioDeleteExperience(PDO $database, int $id): void
{
    $statement = $database->prepare('DELETE FROM experience WHERE id = :id');
    $statement->execute(['id' => $id]);
}

// ---------------------------------------------------------------------------
// Achievements CRUD
// ---------------------------------------------------------------------------

function portfolioValidateAchievement(array $input): array
{
    $values = [
        'title' => portfolioTrimText($input, 'title', 200),
        'issuer' => portfolioTrimText($input, 'issuer', 200),
        'awarded_on' => portfolioTrimDate($input, 'awarded_on'),
        'description' => portfolioTrimText($input, 'description', 5000),
        'sort_order' => portfolioTrimInt($input, 'sort_order', 10000) ?? 0,
    ];

    if ($values['title'] === '') {
        return [null, 'Achievement title is required.'];
    }

    return [$values, null];
}

function portfolioSaveAchievement(PDO $database, array $values, ?int $id = null): void
{
    if ($id === null) {
        $query = 'INSERT INTO achievements (title, issuer, awarded_on, description, sort_order) '
            . 'VALUES (:title, :issuer, :awarded_on, :description, :sort_order)';
    } else {
        $query = 'UPDATE achievements SET title = :title, issuer = :issuer, awarded_on = :awarded_on, '
            . 'description = :description, sort_order = :sort_order WHERE id = :id';
        $values['id'] = $id;
    }

    $statement = $database->prepare($query);
    $statement->execute($values);
}

function portfolioDeleteAchievement(PDO $database, int $id): void
{
    $statement = $database->prepare('DELETE FROM achievements WHERE id = :id');
    $statement->execute(['id' => $id]);
}

// ---------------------------------------------------------------------------
// Messages
// ---------------------------------------------------------------------------

function portfolioLoadMessages(PDO $database): array
{
    return $database->query('SELECT * FROM messages ORDER BY is_read, created_at DESC, id DESC')->fetchAll();
}

function portfolioSetMessageRead(PDO $database, int $id, bool $isRead): void
{
    $statement = $database->prepare('UPDATE messages SET is_read = :is_read WHERE id = :id');
    $statement->execute(['is_read' => $isRead ? 1 : 0, 'id' => $id]);
}

function portfolioDeleteMessage(PDO $database, int $id): void
{
    $statement = $database->prepare('DELETE FROM messages WHERE id = :id');
    $statement->execute(['id' => $id]);
}

// ---------------------------------------------------------------------------
// Website settings
// ---------------------------------------------------------------------------

function portfolioLoadSettings(PDO $database): array
{
    $rows = $database->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
    $settings = [];

    foreach ($rows as $row) {
        $settings[(string) $row['setting_key']] = (string) $row['setting_value'];
    }

    return $settings;
}

function portfolioSaveSettings(PDO $database, array $values): void
{
    $statement = $database->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) '
        . 'ON DUPLICATE KEY UPDATE setting_value = :value2'
    );

    foreach ($values as $key => $value) {
        $statement->execute(['key' => $key, 'value' => $value, 'value2' => $value]);
    }
}
