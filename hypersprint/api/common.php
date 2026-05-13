<?php
// Helper methods

function send_json($data, int $status_code = 200): void
{
    header('Content-Type: application/json');
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

function get_json_body(): array
{
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if ($body !== '' && $data === null) {
        send_json(['error' => 'Invalid JSON request body'], 400);
    }
    return is_array($data) ? $data : [];
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        send_json(['error' => 'Method not allowed'], 405);
    }
}

function require_auth(): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        send_json(['error' => 'Authentication required'], 401);
    }

    return (int) $_SESSION['user_id'];
}

function generate_uuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function sanitize_string($value): string
{
    return trim((string) $value);
}
