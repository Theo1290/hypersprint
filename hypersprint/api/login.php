<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('POST');

start_session();

$data = get_json_body();

$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (!$username || !$password) {
    send_json(['error' => 'Missing username or password'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        send_json(['error' => 'Invalid username or password'], 401);
    }

    $does_match = false;
    if (password_verify($password, $user['password_hash'])) {
        $does_match = true;
    } elseif ($password === $user['password_hash']) {
        $does_match = true;
    }

    if (!$does_match) {
        send_json(['error' => 'Invalid username or password'], 401);
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    send_json([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username']
        ]
    ]);

} catch (PDOException $e) {
    send_json(['error' => 'Database error'], 500);
}
