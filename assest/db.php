<?php
    /* Database credentials. Assuming you are running MySQL
    server with default setting (user 'root' with no password) */
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '1234');
    define('DB_NAME', 'blog');

    /* Attempt to connect to MySQL database */
  try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $GLOBALS['conn'] = $pdo;

} catch(PDOException $e) {
    // [SECURE FIX] Log the error internally for the admin
    error_log($e->getMessage());

    // [SECURE FIX] Show a generic message to the user/attacker
    die("ERROR: Database connection failed. Please contact the administrator.");
}
?>


