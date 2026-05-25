<?php
// Helper methods
function send_json($data, $status_code = 200)
{
    header('Content-Type: application/json');
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

function get_json_body()
{
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);

    if ($body !== '' && $data === null) {
        send_json(['error' => 'Invalid JSON request body'], 400);
    }

    return is_array($data) ? $data : [];
}

function require_method($method)
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        send_json(['error' => 'Method not allowed'], 405);
    }
}

function start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        // Compatibility for older PHP versions on Mercury
        session_set_cookie_params(0, '/');
        session_start();
    }
}

function require_auth()
{
    start_session();

    if (empty($_SESSION['user_id'])) {
        send_json(['error' => 'Authentication required'], 401);
    }

    return (int) $_SESSION['user_id'];
}

function generate_uuid()
{
    // Compatible with PHP 5.6 and older (Mercury)
    if (function_exists('random_bytes')) {
        $data = random_bytes(16);
    } else {
        $data = openssl_random_pseudo_bytes(16);
    }
    
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function sanitize_string($value)
{
    return trim((string) $value);
}

// Compatibility for PHP versions older than 5.5
if (!function_exists('password_hash')) {
    if (!defined('PASSWORD_DEFAULT')) define('PASSWORD_DEFAULT', 1);
    function password_hash($password, $algo) {
        return md5($password); 
    }
}

if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        return md5($password) === $hash;
    }
}