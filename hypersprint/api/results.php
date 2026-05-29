<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('POST');

$user_id = null;

start_session();

if (!empty($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
}

$data = get_json_body();

$challenge_uuid = isset($data['challenge_uuid']) ? $data['challenge_uuid'] : null;

$score = isset($data['wpm']) ? (int)$data['wpm'] : 0;
$accuracy = isset($data['accuracy']) ? (int)$data['accuracy'] : 0;
$time_taken = isset($data['time_taken']) ? (int)$data['time_taken'] : 0;

if (!$challenge_uuid) {
    send_json(['error' => 'Missing challenge_uuid'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id FROM challenges WHERE uuid = ?");
    $stmt->execute([$challenge_uuid]);
    $challenge = $stmt->fetch();

    if (!$challenge) {
        $stmt = $pdo->prepare("INSERT INTO challenges (uuid, title, type) VALUES (?, 'Dynamic Sprint', 'dynamic')");
        $stmt->execute([$challenge_uuid]);
        $challenge_id = $pdo->lastInsertId();
    } else {
        $challenge_id = $challenge['id'];
    }

    if (!$user_id) {
        send_json([
            'success' => true,
            'message' => 'Guest result not stored'
        ]);
    }

    $result_data = json_encode([
        'accuracy' => $accuracy,
        'time_taken' => $time_taken
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO results (user_id, challenge_id, result_data, score, completed_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $user_id,
        $challenge_id,
        $result_data,
        $score
    ]);

    send_json(['success' => true]);

} catch (PDOException $e) {
    send_json(['error' => 'Failed to save result'], 500);
}
