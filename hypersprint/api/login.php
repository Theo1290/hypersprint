<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/db.php';

require_method('POST');

$body = get_json_body();
$username = sanitize_string($body['username'] ?? '');
$password = $body['password'] ?? '';

if (empty($username) || empty($password)) {
    send_json(['success' => false, 'error' => 'Username and password are required'], 400);
}

try {
    // 1. Fetch user by username
    $stmt = $pdo->prepare("SELECT id, uuid, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        send_json(['success' => false, 'error' => 'Invalid username or password'], 401);
    }

    // 2. Verify password
    if (password_verify($password, $user['password_hash'])) {
        // Start session and store user info
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['uuid'] = $user['uuid'];

        send_json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'username' => $user['username'],
                'uuid' => $user['uuid']
            ]
        ]);
    } else {
        send_json(['success' => false, 'error' => 'Invalid username or password'], 401);
    }
} catch (PDOException $e) {
    send_json(['success' => false, 'error' => 'Database error during login'], 500);
}
