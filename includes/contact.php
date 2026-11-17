<?php

declare(strict_types=1);

function portfolioSaveContactMessage(PDO $database, array $input): void
{
    $result = portfolioValidateContactMessage($input);

    if ($result['errors']) {
        throw new InvalidArgumentException('Contact message input is invalid.');
    }

    $statement = $database->prepare(
        'INSERT INTO messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)'
    );
    $statement->execute($result['values']);
}

function portfolioValidateContactMessage(array $input): array
{
    $values = [];
    $errors = [];

    foreach (['name' => 150, 'email' => 254, 'subject' => 200, 'message' => 5000] as $field => $limit) {
        $value = $input[$field] ?? '';
        $values[$field] = is_string($value) ? trim($value) : '';

        if ($field !== 'subject' && $values[$field] === '') {
            $errors[$field] = 'This field is required.';
        } elseif (mb_strlen($values[$field]) > $limit) {
            $errors[$field] = 'This field is too long.';
        }
    }

    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    return ['values' => $values, 'errors' => $errors];
}
