<?php

declare(strict_types=1);

require_once __DIR__ . '/security.php';

function portfolioAdminLogin(PDO $database, string $email, string $password): bool
{
    $statement = $database->prepare('SELECT id, email, password_hash FROM admins WHERE email = :email');
    $statement->execute(['email' => strtolower(trim($email))]);
    $admin = $statement->fetch();

    if (!is_array($admin) || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    portfolioRegenerateSessionId();
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];

    return true;
}
