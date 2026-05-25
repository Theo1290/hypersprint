<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('GET');

// ✅ FIXED (removed ??)
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'words';
$value = isset($_GET['value']) ? (int)$_GET['value'] : 25;

if ($value <= 0) {
    $value = 25;
}

try {
    $stmt = $pdo->query("
        SELECT id, uuid, title, description
        FROM challenges
        ORDER BY RAND()
        LIMIT 1
    ");

    $challenge = $stmt->fetch();

    if (!$challenge) {
        send_json(['error' => 'No challenges found'], 500);
    }

    // ✅ safer than ?: on old PHP edge cases
    $baseText = (!empty($challenge['description'])) ? $challenge['description'] : 'default typing text';

    $words = preg_split('/\s+/', trim($baseText));

    if ($mode === 'words') {
        $targetCount = $value;

        while (count($words) < $targetCount) {
            $words = array_merge($words, $words);
        }

        $words = array_slice($words, 0, $targetCount);
    }

    if ($mode === 'time') {
        while (count($words) < 200) {
            $words = array_merge($words, $words);
        }
    }

    $content = implode(' ', $words);

    send_json([
        'challenge' => [
            'challenge_id' => $challenge['uuid'],
            'title' => $challenge['title'],
            'content_to_type' => $content
        ]
    ]);

} catch (PDOException $e) {
    send_json(['error' => 'Failed to load challenge'], 500);
}