<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

require_method('GET');

try {
    $stmt = $pdo->query('SELECT u.uuid, u.username, SUM(r.score) AS total_score, COUNT(r.id) AS completed_count
        FROM results r
        JOIN users u ON r.user_id = u.id
        GROUP BY u.id, u.uuid, u.username
        ORDER BY total_score DESC, completed_count DESC
        LIMIT 25');
    $leaderboard = $stmt->fetchAll();
    send_json(['success' => true, 'leaderboard' => $leaderboard]);
} catch (PDOException $e) {
    send_json(['error' => 'Unable to load leaderboard'], 500);
}
