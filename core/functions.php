<?php
session_start();
require_once __DIR__ . '/dbconfig.php';

function escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $loginPage = (strpos($script, '/shops/') !== false || strpos($script, '/menu_items/') !== false)
            ? '../login.php'
            : 'login.php';
        header('Location: ' . $loginPage);
        exit;
    }
}

function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function redirect_home(string $message = ''): void
{
    $url = '../index.php';
    if ($message !== '') {
        $url .= '?message=' . urlencode($message);
    }
    header('Location: ' . $url);
    exit;
}

function log_activity(mysqli $conn, int $userId, string $action, string $recordType, int $recordId): void
{
    $statement = $conn->prepare(
        'INSERT INTO activity_logs (user_id, action, record_type, record_id) VALUES (?, ?, ?, ?)'
    );
    $statement->bind_param('issi', $userId, $action, $recordType, $recordId);
    $statement->execute();
    $statement->close();
}
