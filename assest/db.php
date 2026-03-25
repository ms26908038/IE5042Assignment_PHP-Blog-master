<?php
require_once __DIR__ . '/../env_loader.php';
loadEnv(__DIR__ . '/../.env');

// fallback to original values IF .env is missing
$server   = $_ENV['DB_SERVER']   ?? 'localhost';
$user     = $_ENV['DB_USERNAME'] ?? 'root';
$pass     = $_ENV['DB_PASSWORD'] ?? '1234'; // friend's password stays as fallback
$name     = $_ENV['DB_NAME']     ?? 'blog';

try {
    $pdo = new PDO("mysql:host=$server;dbname=$name", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $GLOBALS['conn'] = $pdo;

} catch(PDOException $e){
    $GLOBALS['e'] = $e;
    die("ERROR: Could not connect. " . $e->getMessage());
}
