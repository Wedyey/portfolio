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

function portfolioCreateFirstAdmin(PDO $database, string $email, string $password): void
{
    $email = strtolower(trim($email));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
        throw new InvalidArgumentException('Enter a valid email and a password of at least 12 characters.');
    }

    if ((int) $database->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) {
        throw new RuntimeException('An administrator account already exists.');
    }

    $statement = $database->prepare('INSERT INTO admins (email, password_hash) VALUES (:email, :password_hash)');
    $statement->execute(['email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
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
