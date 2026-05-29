<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('POST');

// Ensure common.php handles CORS headers!
// header("Access-Control-Allow-Origin: http://localhost:5173");
// header("Access-Control-Allow-Credentials: true");

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

    // Does the user exist?
    if (!$user) {
        send_json([
            'debug_error' => 'Database lookup failed!',
            'reason' => "No user found with username: '" . htmlspecialchars($username) . "'"
        ], 401);
    }

    // Is the password a secure hash?
    // Secure hashes always start with '$2y$' or '$2a$' or '$2x$'
    $is_hashed = (strpos($user['password_hash'], '$2') === 0);
    if (!$is_hashed) {
        send_json([
            'debug_error' => 'Password format error!',
            'reason' => 'Your database contains a plain-text password or old MD5 hash. password_verify() will always fail this.',
            'database_value_preview' => substr($user['password_hash'], 0, 5) . '...'
        ], 401);
    }

    // Does the password pass verification?
    $does_match = password_verify($password, $user['password_hash']);
    if (!$does_match) {
        send_json([
            'debug_error' => 'Password mismatch!',
            'reason' => 'The password you typed does not match the hash in the database.'
        ], 401);
    }

    // Password matches database!
    send_json(['success' => true, 'message' => 'Debug passed! Password matches.']);

} catch (PDOException $e) {
    send_json(['error' => $e->getMessage()], 500);
}
