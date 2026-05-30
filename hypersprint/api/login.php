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

    $hash = $user['password_hash'];
    $is_bcrypt = (strpos($hash, '$2') === 0);
    $is_md5 = (strlen($hash) === 32 && ctype_xdigit($hash)); // 32 hex characters

    if ($is_bcrypt) {
        // Native or polyfilled Bcrypt verification
        $does_match = password_verify($password, $hash);
    } elseif ($is_md5) {
        // Compatibility for polyfill MD5 hashes generated on older PHP (Mercury)
        $does_match = (md5($password) === $hash);
    } else {
        // Fallback for legacy plain-text passwords seeded in standard tables on Mercury
        $does_match = ($password === $hash);
    }

    if (!$does_match) {
        send_json([
            'debug_error' => 'Password mismatch!',
            'reason' => 'The password you typed does not match the credentials in the system database.'
        ], 401);
    }

    // Password matches database! Establish user session.
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
    send_json(['error' => $e->getMessage()], 500);
}
