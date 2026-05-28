<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/db.php';

require_method('GET');

// Function to get random words from words.txt
function get_dynamic_content($count = 25) {
    $filePath = __DIR__ . '/words.txt';
    if (!file_exists($filePath)) {
        return "The system could not locate the word database.";
    }
    
    $words = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$words) return "Error reading word list.";
    
    shuffle($words);
    $selected = array_slice($words, 0, $count);
    return implode(' ', $selected);
}

$race_uuid = isset($_GET['race_uuid']) ? $_GET['race_uuid'] : null;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'words';
$value = isset($_GET['value']) ? (int)$_GET['value'] : 25;

try {
    if ($race_uuid) {
        // Fetch specific content for a multiplayer race
        $stmt = $pdo->prepare("SELECT content, challenge_uuid FROM active_races WHERE race_uuid = ?");
        $stmt->execute([$race_uuid]);
        $race = $stmt->fetch();
        
        if ($race && !empty($race['content'])) {
            send_json([
                'challenge' => [
                    'challenge_id' => $race['challenge_uuid'],
                    'title' => 'MULTIPLAYER RACE',
                    'content_to_type' => $race['content']
                ]
            ]);
        }
    }

    // Default/Singleplayer: Generate random content
    $content = get_dynamic_content($value);
    
    send_json([
        'challenge' => [
            'challenge_id' => 'dynamic-' . time(),
            'title' => ($mode === 'time' ? $value . 's Sprint' : $value . ' Words'),
            'content_to_type' => $content
        ]
    ]);

} catch (PDOException $e) {
    send_json(['error' => 'Failed to load challenge: ' . $e->getMessage()], 500);
}
