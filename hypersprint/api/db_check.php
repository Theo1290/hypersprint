<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/db.php';

try {
    // Check current database
    $res = $pdo->query("SELECT DATABASE() as db")->fetch();
    $dbName = $res['db'];

    // Check if table exists
    $tableExists = false;
    $stmt = $pdo->query("SHOW TABLES LIKE 'active_races'");
    if ($stmt->fetch()) {
        $tableExists = true;
    }

    // Check row count
    $count = 0;
    if ($tableExists) {
        $count = $pdo->query("SELECT COUNT(*) FROM active_races")->fetchColumn();
    }

    send_json([
        'current_database' => $dbName,
        'table_active_races_exists' => $tableExists,
        'row_count' => $count,
        'php_version' => phpversion()
    ]);

} catch (Exception $e) {
    send_json(['error' => $e->getMessage()]);
}
