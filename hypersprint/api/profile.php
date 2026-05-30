<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

start_session();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    send_json(['error' => 'Authentication required'], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {

        $stmt = $pdo->prepare("
            SELECT id, username, email, profile_url, created_at
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
            SELECT result_data, score
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

        $avgAcc = $countAcc > 0 ? $totalAcc / $countAcc : null;
        $level = floor(sqrt($totalXP / 100)) + 1;

        $stmt = $pdo->prepare("
            SELECT 
                r.id as result_id,
                c.title as challenge_title,
                r.score as wpm,
                r.result_data,
                r.completed_at
            FROM results r
            JOIN challenges c ON r.challenge_id = c.id
            WHERE r.user_id = ?
            ORDER BY r.completed_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $recent = $stmt->fetchAll();

        foreach ($recent as &$r) {
            $data = json_decode($r['result_data'], true);

            $r['accuracy'] = isset($data['accuracy']) ? $data['accuracy'] : null;
            $r['duration'] = isset($data['time_taken']) ? $data['time_taken'] : null;
            $r['completed'] = $r['completed_at'];
        }

        send_json([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'profile_url' => $user['profile_url'],
                'joined' => $user['created_at'],
                'level' => $level,
                'highest_wpm' => (float)(isset($stats['highest_wpm']) ? $stats['highest_wpm'] : 0),
                'average_wpm' => (float)(isset($stats['average_wpm']) ? $stats['average_wpm'] : 0),
                'average_accuracy' => $avgAcc,
                'challenge_count' => (int)(isset($stats['challenge_count']) ? $stats['challenge_count'] : 0)
            ],
            'recent_results' => $recent
        ]);

    } catch (PDOException $e) {
        send_json(['error' => 'Failed to load profile'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = get_json_body();

    $username = isset($data['username']) ? trim($data['username']) : null;
    $email = isset($data['email']) ? trim($data['email']) : null;

    if (!$username || !$email) {
        send_json(['error' => 'Missing fields'], 400);
    }

    try {

        $stmt = $pdo->prepare("
            SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?
        ");
        $stmt->execute([$username, $email, $user_id]);

        if ($stmt->fetch()) {
            send_json(['error' => 'Username or email already in use'], 400);
        }

        $stmt = $pdo->prepare("
            UPDATE users
            SET username = ?, email = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$username, $email, $user_id]);

        send_json(['success' => true]);

    } catch (PDOException $e) {
        send_json(['error' => 'Failed to update profile'], 500);
    }
}
