<?php
require "db.php";

session_start();

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

// CSRF
if (!isset($_POST['csrf'], $_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    http_response_code(403);
    exit('Invalid CSRF token');
}

// Validate inputs
$allowed_types = ['article', 'category', 'author'];
$type = $_POST['type'] ?? '';
if (!in_array($type, $allowed_types, true)) {
    http_response_code(400);
    exit('Invalid type');
}

$raw_id = $_POST['id'] ?? null;
$id = filter_var($raw_id, FILTER_VALIDATE_INT);
if ($id === false) {
    http_response_code(400);
    exit('Invalid id');
}

// Map table/column/redirect safely
switch ($type) {
    case 'article':
        $table = 'article';
        $idCol = 'article_id';
        $goto  = '../article.php';
        break;
    case 'category':
        $table = 'category';
        $idCol = 'category_id';
        $goto  = '../categories.php';
        break;
    case 'author':
        $table = 'author';
        $idCol = 'author_id';
        $goto  = '../author.php';
        break;
    default:
        http_response_code(400);
        exit('Invalid operation');
}

if (!$conn) {
    error_log("delete.php: DB connection unavailable");
    http_response_code(500);
    exit('Internal error');
}

try {
    $sql = "DELETE FROM `$table` WHERE `$idCol` = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    error_log("delete.php: DB error on $table/$idCol id=$id → " . $e->getMessage());
    http_response_code(500);
    exit('Internal error');
}

// PRG
header("Location: $goto", true, 303);
exit;
