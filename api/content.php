<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/content.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$loaders = [
    'profile' => 'portfolioLoadProfile',
    'projects' => 'portfolioLoadProjects',
    'skills' => 'portfolioLoadSkills',
    'education' => 'portfolioLoadEducation',
    'experience' => 'portfolioLoadExperience',
];
$type = $_GET['type'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['error' => 'Only GET requests are allowed.']);
    exit;
}

if (!is_string($type) || !isset($loaders[$type])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid content request.']);
    exit;
}

try {
    $content = $loaders[$type](portfolioDatabase());
    echo json_encode($content, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'Portfolio content is temporarily unavailable.']);
} catch (JsonException $error) {
    error_log('Portfolio content encoding failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Portfolio content could not be displayed.']);
}
