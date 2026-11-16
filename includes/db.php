<?php

declare(strict_types=1);

function portfolioDatabase(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('PORTFOLIO_DB_HOST');
    $database = getenv('PORTFOLIO_DB_NAME');
    $username = getenv('PORTFOLIO_DB_USER');
    $password = getenv('PORTFOLIO_DB_PASSWORD');
    $port = getenv('PORTFOLIO_DB_PORT');

    if ($host === false || $host === '' || $database === false || $database === ''
        || $username === false || $username === '' || $password === false) {
        throw new RuntimeException('Portfolio database configuration is incomplete.');
    }

    if ($port === false || $port === '') {
        $port = '3306';
    }

    $validatedPort = filter_var(
        $port,
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
