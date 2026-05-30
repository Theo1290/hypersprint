<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

start_session();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    send_json(['error' => 'Authentication required'], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // SEARCH USERS
    if (!empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    u.id as user_id,
                    u.username,
                    u.profile_url
                FROM users u
                WHERE u.username LIKE ?
                AND u.id != ?
                LIMIT 10
            ");
            $stmt->execute([$search, $user_id]);
            $results = $stmt->fetchAll();

            foreach ($results as &$r) {

                $stmt = $pdo->prepare("
                    SELECT 1 FROM friends 
                    WHERE (user_id = ? AND friend_user_id = ?)
                       OR (user_id = ? AND friend_user_id = ?)
                    LIMIT 1
                ");
                $stmt->execute([$user_id, $r['user_id'], $r['user_id'], $user_id]);

                $r['is_friend'] = (bool)$stmt->fetch();
                $r['request_sent'] = false;
                $r['level'] = null;
            }

            send_json(['results' => $results]);

        } catch (PDOException $e) {
            send_json(['error' => 'Search failed'], 500);
        }
    }

    try {
        // FRIEND LIST
        $stmt = $pdo->prepare("
            SELECT u.id as user_id, u.username, u.profile_url
            FROM friends f
            JOIN users u ON u.id = f.friend_user_id
            WHERE f.user_id = ?
            AND EXISTS (
                SELECT 1 FROM friends f2 
                WHERE f2.user_id = f.friend_user_id AND f2.friend_user_id = f.user_id
            )
        ");
        $stmt->execute([$user_id]);
        $friends = $stmt->fetchAll();

        // INCOMING REQUESTS
        $stmt = $pdo->prepare("
            SELECT u.id as from_user_id, u.username, u.profile_url, f.created_at as requested_at
            FROM friends f
            JOIN users u ON u.id = f.user_id
            WHERE f.friend_user_id = ?
            AND NOT EXISTS (
                SELECT 1 FROM friends f2 
                WHERE f2.user_id = ? AND f2.friend_user_id = f.user_id
            )
        ");
        $stmt->execute([$user_id, $user_id]);
        $incoming = $stmt->fetchAll();

        // SENT REQUESTS
        $stmt = $pdo->prepare("
            SELECT u.id as to_user_id, u.username, 'pending' as status
            FROM friends f
            JOIN users u ON u.id = f.friend_user_id
            WHERE f.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $sent = $stmt->fetchAll();

        send_json([
            'friends' => $friends,
            'friend_requests' => $incoming,
            'sent_requests' => $sent
        ]);

    } catch (PDOException $e) {
        send_json(['error' => 'Failed to load friends'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = get_json_body();

    $action = isset($data['action']) ? $data['action'] : null;

    try {

        if ($action === 'send_request') {

            $to = isset($data['to_user_id']) ? (int)$data['to_user_id'] : 0;

            if (!$to) {
                send_json(['error' => 'Invalid user'], 400);
            }

            $stmt = $pdo->prepare("
                INSERT IGNORE INTO friends (user_id, friend_user_id, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$user_id, $to]);

            send_json(['success' => true]);
        }

        if ($action === 'accept') {

            $from = isset($data['from_user_id']) ? (int)$data['from_user_id'] : 0;

            $stmt = $pdo->prepare("
                INSERT IGNORE INTO friends (user_id, friend_user_id)
                VALUES (?, ?), (?, ?)
            ");
            $stmt->execute([
                $user_id, $from,
                $from, $user_id
            ]);

            send_json(['success' => true]);
        }

        if ($action === 'decline') {

            $from = isset($data['from_user_id']) ? (int)$data['from_user_id'] : 0;

            $stmt = $pdo->prepare("
                DELETE FROM friends
                WHERE user_id = ? AND friend_user_id = ?
            ");
            $stmt->execute([$from, $user_id]);

            send_json(['success' => true]);
        }

        if ($action === 'remove') {

            $friend = isset($data['friend_id']) ? (int)$data['friend_id'] : 0;

            $stmt = $pdo->prepare("
                DELETE FROM friends
                WHERE (user_id = ? AND friend_user_id = ?)
                   OR (user_id = ? AND friend_user_id = ?)
            ");
            $stmt->execute([
                $user_id, $friend,
                $friend, $user_id
            ]);

            send_json(['success' => true]);
        }

        send_json(['error' => 'Invalid action'], 400);

    } catch (PDOException $e) {
        send_json(['error' => 'Action failed'], 500);
    }
}