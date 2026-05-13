<?php
// Database connection helper for the Hypersprint backend.
//
// This file should remain server-side only. Do not include
// your real credentials in frontend code or expose them in HTML.

$host = 'feenix-mariadb.swin.edu.au';
$dbname = 's103982457_db'; // Replace with your actual Swinburne DB name
$user = 's103982457'; // Your MariaDB username, usually your Swinburne ID
$pass = 'your_db_password_here'; // Replace with your MariaDB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Do not expose the raw error message to users.
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
