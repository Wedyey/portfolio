<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin.php';

portfolioRequireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the logout form to sign out.');
}

if (!portfolioVerifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('The logout form expired. Reload the page and try again.');
}

portfolioAdminLogout();
header('Location: login.php', true, 303);
exit;
