<?php

declare(strict_types=1);

function portfolioEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function portfolioStartSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (session_status() !== PHP_SESSION_NONE) {
        throw new RuntimeException('PHP sessions are not available.');
    }

    if (ini_set('session.use_strict_mode', '1') === false
        || ini_set('session.use_only_cookies', '1') === false) {
        throw new RuntimeException('Unable to apply secure PHP session settings.');
    }

    $httpsValue = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
    $isHttps = $httpsValue !== '' && $httpsValue !== 'off' && $httpsValue !== '0';

    if (!session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ])) {
        throw new RuntimeException('Unable to configure the session cookie.');
    }

    if (!session_start()) {
        throw new RuntimeException('Unable to start the PHP session.');
    }
}

function portfolioCsrfToken(): string
{
    portfolioStartSession();

    if (!isset($_SESSION['portfolio_csrf_token']) || !is_string($_SESSION['portfolio_csrf_token'])) {
        $_SESSION['portfolio_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['portfolio_csrf_token'];
}

function portfolioVerifyCsrfToken(mixed $submittedToken): bool
{
    portfolioStartSession();

    $sessionToken = $_SESSION['portfolio_csrf_token'] ?? null;

    return is_string($submittedToken)
        && is_string($sessionToken)
        && hash_equals($sessionToken, $submittedToken);
}

function portfolioRegenerateSessionId(): void
{
    portfolioStartSession();

    if (!session_regenerate_id(true)) {
        throw new RuntimeException('Unable to regenerate the PHP session ID.');
    }

    unset($_SESSION['portfolio_csrf_token']);
}
