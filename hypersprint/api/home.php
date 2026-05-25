<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('GET');

start_session();

// ✅ FIXED (removed ??)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$response = [
    'is_authenticated' => !empty($user_id),
];

if ($user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                MAX(score) AS highest_wpm,
                AVG(score) AS average_wpm,
                COUNT(*) AS challenge_count
            FROM results
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch();

        // ✅ FIXED (removed ??)
        $response['user_stats'] = [
            'highest_wpm' => (int)(isset($stats['highest_wpm']) ? $stats['highest_wpm'] : 0),
            'average_wpm' => (int)(isset($stats['average_wpm']) ? $stats['average_wpm'] : 0),
            'challenge_count' => (int)(isset($stats['challenge_count']) ? $stats['challenge_count'] : 0),
        ];
    } catch (PDOException $e) {
        $response['user_stats'] = null;
    }
}

try {
    $stmt = $pdo->query("
        SELECT
            title,
            difficulty AS level,
            category AS topic,
            type AS gamemode
        FROM challenges
        ORDER BY created_at DESC
        LIMIT 6
    ");

    $response['featured_challenges'] = $stmt->fetchAll();
} catch (PDOException $e) {
    $response['featured_challenges'] = [];
}

try {
    $stmt = $pdo->query("
        SELECT 
            u.username, 
            MAX(r.score) AS wpm, 
            COUNT(r.id) AS games_played,
            COALESCE(MAX(r.score), 0) DIV 10 AS level
        FROM users u
        LEFT JOIN results r ON r.user_id = u.id
        GROUP BY u.id
        ORDER BY wpm DESC
        LIMIT 5
    ");

    $response['leaderboard_preview'] = $stmt->fetchAll();
} catch (PDOException $e) {
    $response['leaderboard_preview'] = [];
}

try {
    $stmt = $pdo->query("
        SELECT
            u.username,
            c.title AS challenge_title,
            r.score AS wpm,
            r.completed_at
        FROM results r
        JOIN users u ON r.user_id = u.id
        JOIN challenges c ON r.challenge_id = c.id
        ORDER BY r.completed_at DESC
        LIMIT 8
    ");

    $response['recent_results'] = $stmt->fetchAll();
} catch (PDOException $e) {
    $response['recent_results'] = [];
}

send_json($response);
