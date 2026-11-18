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

function portfolioLoadAchievements(PDO $database): array
{
    $achievements = $database->query('SELECT * FROM achievements ORDER BY sort_order, id')->fetchAll();

    return $achievements;
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

function portfolioSaveProfile(PDO $database, array $input): void
{
    $result = portfolioValidateProfile($input);

    if ($result['errors']) {
        throw new InvalidArgumentException('Profile input is invalid.');
    }

    $profileExists = $database->query('SELECT id FROM profile WHERE id = 1')->fetchColumn() !== false;
    $fields = 'name = :name, headline = :headline, introduction = :introduction, biography = :biography, '
        . 'email = :email, phone = :phone, location = :location';
    $query = $profileExists
        ? 'UPDATE profile SET ' . $fields . ' WHERE id = 1'
        : 'INSERT INTO profile (name, headline, introduction, biography, email, phone, location, id) '
            . 'VALUES (:name, :headline, :introduction, :biography, :email, :phone, :location, 1)';
    $statement = $database->prepare($query);
    $statement->execute($result['values']);
}
