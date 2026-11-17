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
