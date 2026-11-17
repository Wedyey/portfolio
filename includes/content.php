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
