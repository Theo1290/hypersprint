<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/pusher_helper.php';

require_method('POST');
$user_id = require_auth();

$data = get_json_body();
$action = isset($data['action']) ? $data['action'] : '';
$race_uuid = isset($data['race_uuid']) ? $data['race_uuid'] : '';

if (!$race_uuid) {
    send_json(['error' => 'Missing race_uuid'], 400);
}

try {
    if ($action === 'ready') {
        // Mark the current user as ready
        $stmt = $pdo->prepare("SELECT id, user1_id, user2_id, user1_ready, user2_ready FROM active_races WHERE race_uuid = ?");
        $stmt->execute([$race_uuid]);
        $race = $stmt->fetch();

        if (!$race) {
            send_json(['error' => 'Race not found'], 404);
        }

        if ($user_id == $race['user1_id']) {
            $stmt = $pdo->prepare("UPDATE active_races SET user1_ready = 1 WHERE id = ?");
            $stmt->execute([$race['id']]);
            $race['user1_ready'] = 1;
        } elseif ($user_id == $race['user2_id']) {
            $stmt = $pdo->prepare("UPDATE active_races SET user2_ready = 1 WHERE id = ?");
            $stmt->execute([$race['id']]);
            $race['user2_ready'] = 1;
        }

        // If BOTH are ready, trigger the start countdown
        if ($race['user1_ready'] && $race['user2_ready']) {
            // Fetch names for both users
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ? OR id = ?");
            $stmt->execute([$race['user1_id'], $race['user2_id']]);
            $rows = $stmt->fetchAll();
            
            $players = array();
            foreach ($rows as $row) {
                $players[(string)$row['id']] = $row['username'];
            }

            pusher_trigger('race-' . $race_uuid, 'start-race', [
                'start' => true,
                'players' => $players
            ]);
        }

        send_json(['success' => true]);

    } elseif ($action === 'progress') {
        // Broadcast progress to opponent
        $word_index = isset($data['word_index']) ? (int)$data['word_index'] : 0;
        $finished = isset($data['finished']) ? (bool)$data['finished'] : false;

        pusher_trigger('race-' . $race_uuid, 'progress-update', [
            'user_id' => $user_id,
            'username' => $_SESSION['username'],
            'word_index' => $word_index,
            'finished' => $finished
        ]);

        send_json(['success' => true]);
    } elseif ($action === 'abort') {
        // Trigger abort event
        pusher_trigger('race-' . $race_uuid, 'opponent-aborted', [
            'user_id' => $user_id,
            'username' => $_SESSION['username']
        ]);
        
        // Update DB to mark race as finished
        $stmt = $pdo->prepare("UPDATE active_races SET status = 'finished' WHERE race_uuid = ?");
        $stmt->execute([$race_uuid]);

        send_json(['success' => true]);
    }

} catch (PDOException $e) {
    send_json(['error' => 'Sync error: ' . $e->getMessage()], 500);
}
