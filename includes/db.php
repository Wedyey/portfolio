<?php

declare(strict_types=1);

function portfolioDatabase(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $localConfig = [];
    $localConfigPath = __DIR__ . '/db.local.php';

    if (is_file($localConfigPath)) {
        $loadedConfig = require $localConfigPath;

        if (!is_array($loadedConfig)) {
            throw new RuntimeException('Local database configuration must return an array.');
        }

        $localConfig = $loadedConfig;
    }

    $host = getenv('PORTFOLIO_DB_HOST');
    $database = getenv('PORTFOLIO_DB_NAME');
    $username = getenv('PORTFOLIO_DB_USER');
    $password = getenv('PORTFOLIO_DB_PASSWORD');
    $port = getenv('PORTFOLIO_DB_PORT');

    if ($host === false || $host === '') {
        $host = $localConfig['host'] ?? $host;
    }
    if ($database === false || $database === '') {
        $database = $localConfig['database'] ?? $database;
    }
    if ($username === false || $username === '') {
        $username = $localConfig['username'] ?? $username;
    }
    if ($password === false) {
        $password = $localConfig['password'] ?? $password;
    }
    if ($port === false || $port === '') {
        $port = $localConfig['port'] ?? '3306';
    }

    if (!is_string($host) || $host === ''
        || !is_string($database) || $database === ''
        || !is_string($username) || $username === ''
        || !is_string($password)) {
        throw new RuntimeException('Portfolio database configuration is incomplete.');
    }

    if (!is_string($port) && !is_int($port)) {
        throw new RuntimeException('Portfolio database port must be between 1 and 65535.');
    }

    $validatedPort = filter_var(
        (string) $port,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 65535]]
    );

    if ($validatedPort === false) {
        throw new RuntimeException('Portfolio database port must be between 1 and 65535.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $host,
        $validatedPort,
        $database
    );

    try {
        $connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $error) {
        error_log('Portfolio database connection failed: ' . $error->getMessage());
        throw new RuntimeException('Unable to connect to the portfolio database.');
    }

    return $connection;
}
