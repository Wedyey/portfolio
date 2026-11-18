<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/contact.php';
require_once __DIR__ . '/../includes/security.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST requests are allowed.']);
    exit;
}

$token = $_POST['csrf_token'] ?? null;

if (!portfolioVerifyCsrfToken($token)) {
    http_response_code(400);
    echo json_encode(['error' => 'The form expired. Reload the page and try again.']);
    exit;
}

$result = portfolioValidateContactMessage($_POST);

if ($result['errors']) {
    http_response_code(422);
    echo json_encode(['error' => reset($result['errors'])]);
    exit;
}

try {
    portfolioSaveContactMessage(portfolioDatabase(), $result['values']);
    echo json_encode(['success' => 'Your message was sent.']);
} catch (PDOException $error) {
    error_log('Contact message save failed: ' . $error->getMessage());
    http_response_code(503);
    echo json_encode(['error' => 'Your message could not be saved. Please try again.']);
}
