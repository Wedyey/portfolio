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

function portfolioAdminIsLoggedIn(): bool
{
    portfolioStartSession();

    return filter_var(
        $_SESSION['admin_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    ) !== false;
}

function portfolioRequireAdmin(): void
{
    if (!portfolioAdminIsLoggedIn()) {
        header('Location: login.php', true, 303);
        exit;
    }
}

function portfolioAdminLogout(): void
{
    portfolioStartSession();
    $_SESSION = [];
    $cookie = session_get_cookie_params();

    if (!setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'],
    ]) || !session_destroy()) {
        throw new RuntimeException('Unable to complete administrator logout.');
    }
}
