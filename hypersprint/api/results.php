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

// Normalise dynamic timestamp UUIDs to consistent mode-based ones
if (strpos($challenge_uuid, 'dynamic-') === 0 && is_numeric(substr($challenge_uuid, 8))) {
    if ($time_taken === 15) {
        $challenge_uuid = 'dynamic-time-15';
    } elseif ($time_taken === 30) {
        $challenge_uuid = 'dynamic-time-30';
    } elseif ($time_taken === 60) {
        $challenge_uuid = 'dynamic-time-60';
    } else {
        $challenge_uuid = 'dynamic-words-25';
    }
}

try {
    $stmt = $pdo->prepare("SELECT id, title FROM challenges WHERE uuid = ?");
    $stmt->execute([$challenge_uuid]);
    $challenge = $stmt->fetch();

    if (!$challenge) {
        if (strpos($challenge_uuid, 'dynamic-time-') === 0) {
            $seconds = str_replace('dynamic-time-', '', $challenge_uuid);
            $title = $seconds . 's Sprint';
        } elseif (strpos($challenge_uuid, 'dynamic-words-') === 0) {
            $words = str_replace('dynamic-words-', '', $challenge_uuid);
            $title = $words . ' Words';
        } else {
            $title = 'Dynamic Sprint';
        }
        $stmt = $pdo->prepare("INSERT INTO challenges (uuid, title, type, created_at) VALUES (?, ?, 'dynamic', NOW())");
        $stmt->execute([$challenge_uuid, $title]);
        $challenge = ['id' => $pdo->lastInsertId(), 'title' => $title];
    }

    if (!$user_id) {
        send_json(['success' => true, 'message' => 'Guest result not stored']);
    }

    // Get previous best BEFORE inserting
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as challenge_count, MAX(score) as previous_best
        FROM results
        WHERE user_id = ? AND challenge_id = ?
    ");
    $stmt->execute([$user_id, $challenge['id']]);
    $stats = $stmt->fetch();

    $challenge_count = (int)$stats['challenge_count'];
    $previous_best = (int)$stats['previous_best'];

    // Insert the result
    $result_data = json_encode([
        'accuracy' => $accuracy,
        'time_taken' => $time_taken
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO results (user_id, challenge_id, result_data, score, completed_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $challenge['id'], $result_data, $score]);

    // Post to feed if personal best
    if ($challenge_count >= 1 && $score > $previous_best) {
        $improvement = $score - $previous_best;
        $post_text = json_encode([
            'type' => 'new_record',
            'challenge_title' => $challenge['title'],
            'new_wpm' => $score,
            'previous_wpm' => $previous_best,
            'improvement' => $improvement,
            'accuracy' => $accuracy
        ]);

        $stmt = $pdo->prepare("
            INSERT INTO feed_posts (user_id, post_text, created_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$user_id, $post_text]);
    }

    send_json(['success' => true]);

} catch (PDOException $e) {
    send_json(['error' => 'Failed to save result'], 500);
}