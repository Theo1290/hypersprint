<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/db.php';

require_method('POST');

$body = get_json_body();
$username = sanitize_string(isset($body['username']) ? $body['username'] : '');
$email = sanitize_string(isset($body['email']) ? $body['email'] : '');
$password = isset($body['password']) ? $body['password'] : '';

// 1. Basic Validation
if (empty($username) || empty($email) || empty($password)) {
    send_json(['success' => false, 'error' => 'All fields are required'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_json(['success' => false, 'error' => 'Invalid email format'], 400);
}

try {
    // 2. Check for existing username or email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        send_json(['success' => false, 'error' => 'Username or email already exists'], 409);
    }

    // 3. Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $uuid = generate_uuid();
    $now = date('Y-m-d H:i:s');

    // 4. Insert User
    $stmt = $pdo->prepare("INSERT INTO users (uuid, username, email, password_hash, join_date, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$uuid, $username, $email, $password_hash, $now, $now]);

    send_json([
        'success' => true,
        'message' => 'User registered successfully',
        'user' => [
            'username' => $username,
            'uuid' => $uuid
        ]
    ]);

} catch (PDOException $e) {
    // Check for duplicate key error specifically just in case
    if ($e->getCode() == 23000) {
        send_json(['success' => false, 'error' => 'Username or email already exists'], 409);
    }
    send_json(['success' => false, 'error' => 'Database error during registration: ' . $e->getMessage()], 500);
}
