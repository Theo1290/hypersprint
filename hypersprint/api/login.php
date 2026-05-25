<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('POST');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(0, '/');
    session_start();
}

$data = get_json_body();

$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (!$username || !$password) {
    send_json(['error' => 'Missing username or password'], 400);
}

try {
    $stmt = $pdo->prepare("
        SELECT id, username, password_hash
        FROM users
        WHERE username = ?
        LIMIT 1
    ");
    $stmt->execute([$username]);

    // ✅ safer fetch
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        send_json(['error' => 'Invalid username or password'], 401);
    }

    if ($password !== $user['password_hash']) {
        send_json(['error' => 'Invalid username or password'], 401);
    }

    $_SESSION['user_id'] = (int)$user['id'];

    send_json([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username']
        ]
    ]);

} catch (PDOException $e) {
    send_json(['error' => 'Login failed'], 500);
}