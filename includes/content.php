<?php

declare(strict_types=1);

function portfolioLoadProfile(PDO $database): ?array
{
    $profile = $database->query('SELECT * FROM profile WHERE id = 1')->fetch();

    return is_array($profile) ? $profile : null;
}

function portfolioLoadProjects(PDO $database): array
{
    $projects = $database->query('SELECT * FROM projects ORDER BY sort_order, id')->fetchAll();

    return $projects;
}

function portfolioLoadSkills(PDO $database): array
{
    $skills = $database->query('SELECT * FROM skills ORDER BY sort_order, id')->fetchAll();

    return $skills;
}

function portfolioLoadEducation(PDO $database): array
{
    $education = $database->query('SELECT * FROM education ORDER BY sort_order, id')->fetchAll();

    return $education;
}

function portfolioLoadExperience(PDO $database): array
{
    $experience = $database->query('SELECT * FROM experience ORDER BY sort_order, id')->fetchAll();

    return $experience;
}

function portfolioValidateProfile(array $input): array
{
    $values = [];
    $errors = [];
    $limits = [
        'name' => 150,
        'headline' => 200,
        'introduction' => 5000,
        'biography' => 5000,
        'email' => 254,
        'phone' => 50,
        'location' => 150,
    ];

    foreach ($limits as $field => $limit) {
        $value = $input[$field] ?? '';
        $values[$field] = is_string($value) ? trim($value) : '';

        if (in_array($field, ['name', 'introduction', 'biography'], true) && $values[$field] === '') {
            $errors[$field] = 'This field is required.';
        } elseif (mb_strlen($values[$field]) > $limit) {
            $errors[$field] = 'This field is too long.';
        }
    }

    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    return ['values' => $values, 'errors' => $errors];
}
