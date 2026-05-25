<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('POST');

start_session();


$data = get_json_body();

$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';
$confirm  = isset($data['confirm']) ? trim($data['confirm']) : '';

if (!$username || !$password || !$confirm) {
    send_json(['error' => 'Missing fields'], 400);
}

if ($password !== $confirm) {
    send_json(['error' => 'Passwords do not match'], 400);
}

if (strlen($username) < 3 || strlen($username) > 32) {
    send_json(['error' => 'Username must be 3–32 characters'], 400);
}

if (strlen($password) < 6) {
    send_json(['error' => 'Password must be at least 6 characters'], 400);
}

try {
   
    $stmt = $pdo->prepare("
        SELECT id FROM users WHERE username = ?
    ");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        send_json(['error' => 'Username already exists'], 400);
    }

    $uuid = generate_uuid();
    $hash = password_hash($password, 'default');

    $stmt = $pdo->prepare("
        INSERT INTO users (uuid, username, email, password_hash, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $email = $username . '@placeholder.local';

    $stmt->execute([
        $uuid,
        $username,
        $email,
        $hash
    ]);

    $user_id = $pdo->lastInsertId();

    $_SESSION['user_id'] = (int)$user_id;

    send_json([
        'success' => true,
        'user' => [
            'id' => $user_id,
            'username' => $username
        ]
    ]);

} catch (PDOException $e) {
    send_json(['error' => 'Signup failed'], 500);
}