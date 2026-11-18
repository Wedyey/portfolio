<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin.php';

$destination = portfolioAdminIsLoggedIn() ? 'dashboard.php' : 'login.php';

header('Location: ' . $destination, true, 303);
exit;
