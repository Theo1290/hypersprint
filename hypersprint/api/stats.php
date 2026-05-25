<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(0, '/');
    session_start();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    send_json(['error' => 'Authentication required'], 401);
}

try {

    $stmt = $pdo->prepare("
        SELECT username
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        send_json(['error' => 'User not found'], 404);
    }

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

    $stmt = $pdo->prepare("
        SELECT score, result_data
        FROM results
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();

    $totalAcc = 0;
    $countAcc = 0;
    $totalXP = 0;

    foreach ($rows as $r) {
        $data = json_decode($r['result_data'], true);

        if (isset($data['accuracy'])) {
            $totalAcc += $data['accuracy'];
            $countAcc++;
        }

        $acc = isset($data['accuracy']) ? $data['accuracy'] : 0;
        $wpm = isset($r['score']) ? $r['score'] : 0;
        $totalXP += round($wpm * ($acc / 100));
    }

    $avgAcc = $countAcc > 0 ? $totalAcc / $countAcc : 0;

    $stmt = $pdo->query("
        SELECT user_id, SUM(score) AS total_score
        FROM results
        GROUP BY user_id
        ORDER BY total_score DESC
    ");
    $ranking = $stmt->fetchAll();

    $rank = 1;
    $userRank = null;

    foreach ($ranking as $r) {
        if ($r['user_id'] == $user_id) {
            $userRank = $rank;
            break;
        }
        $rank++;
    }

    if (!$userRank) $userRank = '—';

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM user_achievements
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $achievements = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT 
            r.id AS result_id,
            c.title AS challenge_title,
            r.score AS wpm,
            r.result_data,
            r.completed_at
        FROM results r
        JOIN challenges c ON r.challenge_id = c.id
        WHERE r.user_id = ?
        ORDER BY r.completed_at DESC
        LIMIT 50
    ");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll();

    foreach ($history as &$h) {
        $data = json_decode($h['result_data'], true);

        $h['accuracy'] = isset($data['accuracy']) ? $data['accuracy'] : 0;
        $h['duration'] = isset($data['time_taken']) ? $data['time_taken'] : 0;
        $h['completed'] = $h['completed_at'];
    }

    send_json([
        'stats' => [
            'username' => $user['username'],
            'highest_wpm' => (int)(isset($stats['highest_wpm']) ? $stats['highest_wpm'] : 0),
            'average_wpm' => (int)(isset($stats['average_wpm']) ? $stats['average_wpm'] : 0),
            'average_accuracy' => round($avgAcc, 1),
            'challenge_count' => (int)(isset($stats['challenge_count']) ? $stats['challenge_count'] : 0),
            'total_experience' => $totalXP,
            'rank_global' => $userRank,
            'achievements_count' => (int)(isset($achievements['count']) ? $achievements['count'] : 0)
        ],
        'history' => $history
    ]);

} catch (PDOException $e) {
    send_json(['error' => 'Failed to load stats'], 500);
}