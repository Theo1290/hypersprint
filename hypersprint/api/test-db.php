<?php
require __DIR__ . '/common.php';
require __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT 'Hello DB!' AS msg");
    $row = $stmt->fetch();
    send_json(['success' => true, 'message' => isset($row['msg']) ? $row['msg'] : 'Hello DB!']);
} catch (PDOException $e) {
    send_json(['error' => 'Database query failed'], 500);
}
