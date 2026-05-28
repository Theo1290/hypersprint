<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/pusher_helper.php';

require_method('POST');
$user_id = require_auth();

$data = get_json_body();
$action = isset($data['action']) ? $data['action'] : 'join';

try {
    if ($action === 'join') {
        // 1. Look for ANY waiting race (removed user1_id check for testing)
        $stmt = $pdo->prepare("
            SELECT id, race_uuid, user1_id, content 
            FROM active_races 
            WHERE status = 'waiting' 
            ORDER BY created_at ASC
            LIMIT 1
        ");
        $stmt->execute();
        $match = $stmt->fetch();

        if ($match) {
            // Found a match! Pair up.
            $stmt = $pdo->prepare("
                UPDATE active_races 
                SET user2_id = ?, status = 'active' 
                WHERE id = ?
            ");
            $stmt->execute([$user_id, $match['id']]);

            pusher_trigger('race-' . $match['race_uuid'], 'match-found', [
                'race_uuid' => $match['race_uuid'],
                'opponent_id' => $user_id,
                'opponent_name' => $_SESSION['username'],
                'content' => $match['content']
            ]);

            send_json([
                'success' => true, 
                'race_uuid' => $match['race_uuid'], 
                'status' => 'active',
                'content' => $match['content'],
                'opponent_name' => 'SYNCED'
            ]);
        } else {
            // Create a new race
            $race_uuid = generate_uuid();
            $content = "The system failed to load words, please try again.";
            
            // Pick words from file
            $filePath = __DIR__ . '/words.txt';
            $words = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($words) {
                shuffle($words);
                $content = implode(' ', array_slice($words, 0, 25));
            }

            $stmt = $pdo->prepare("
                INSERT INTO active_races (race_uuid, user1_id, challenge_uuid, content, status) 
                VALUES (?, ?, ?, ?, 'waiting')
            ");
            $success = $stmt->execute([$race_uuid, $user_id, 'multi-' . time(), $content]);

            if (!$success) {
                $err = $stmt->errorInfo();
                send_json(['error' => 'Database Insert Failed', 'info' => $err], 500);
            }

            send_json([
                'success' => true, 
                'race_uuid' => $race_uuid, 
                'status' => 'waiting',
                'user_id_used' => $user_id
            ]);
        }
    } elseif ($action === 'leave') {
        $race_uuid = isset($data['race_uuid']) ? $data['race_uuid'] : '';
        if ($race_uuid) {
            $stmt = $pdo->prepare("DELETE FROM active_races WHERE race_uuid = ? AND user1_id = ? AND status = 'waiting'");
            $stmt->execute([$race_uuid, $user_id]);
        }
        send_json(['success' => true]);
    }
} catch (PDOException $e) {
    send_json(['error' => 'Lobby error: ' . $e->getMessage()], 500);
}
