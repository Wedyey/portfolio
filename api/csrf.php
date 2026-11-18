<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['token' => portfolioCsrfToken()], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
