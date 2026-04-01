<?php require "db.php"; ?>

<?php
/***********************************************************************
 * INSERT ENDPOINT — SECURITY HARDENED + CSRF PROTECTED
 ***********************************************************************/

// ✅ CSRF VALIDATION FOR ALL POST REQUESTS
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf'])) {

        http_response_code(403);
        exit("Invalid CSRF token");
    }
}

/* ---------------------------- INPUT GATE ---------------------------- */
// ✅ FIX: Whitelist the insert type (block tampering like ?type=users)
$allowed_types = ['article', 'category', 'author', 'comment'];
$type = $_GET['type'] ?? '';
if (!in_array($type, $allowed_types, true)) {
    http_response_code(400);
    exit('ERROR: Invalid Type');
}

if (!$conn) {
    error_log("DB connection not available in insert.php");
    http_response_code(500);
    exit("An internal connection error occurred.");
}

/* ---------------------------- MAIN FLOW ---------------------------- */
if (isset($_POST["submit"])) {
    switch ($type) {

        /* ============================= ARTICLE ============================= */
        case "article":

            $catId   = filter_var($_POST["arCategory"] ?? null, FILTER_VALIDATE_INT);
            $authId  = filter_var($_POST["arAuthor"]   ?? null, FILTER_VALIDATE_INT);
            if (!$catId || !$authId) {
                http_response_code(400);
                exit('Invalid category/author');
            }

            $title   = test_input($_POST["arTitle"] ?? '');
            $content = $_POST["arContent"] ?? '';

            $imageName = '';
            if (isset($_FILES["arImage"]) && $_FILES["arImage"]["error"] === 0) {
                $imageName = secureUpload("arImage", "../img/article/");
            }

            $data = [
                "article_title"        => $title,
                "article_content"      => $content,
                "article_image"        => $imageName,
                "article_created_time" => date('Y-m-d H:i:s'),
                "id_categorie"         => $catId,
                "id_author"            => $authId
            ];

            insertToDB($conn, 'article', $data);
            header("Location: ../index.php", true, 301);
            exit;


        /* ============================ CATEGORY ============================= */
        case "category":

            $name  = test_input($_POST["catName"]  ?? '');
            $color = test_input($_POST["catColor"] ?? '');

            $imageName = '';
            if (isset($_FILES["catImage"]) && $_FILES["catImage"]["error"] === 0) {
                $imageName = secureUpload("catImage", "../img/category/");
            }

            $data = [
                "category_name"  => $name,
                "category_image" => $imageName,
                "category_color" => $color
            ];

            insertToDB($conn, 'category', $data);
            header("Location: ../categories.php", true, 301);
            exit;


        /* ============================== AUTHOR ============================= */
        case "author":

            $fullname = test_input($_POST["authName"]     ?? '');
            $desc     = test_input($_POST["authDesc"]     ?? '');
            $email    = test_input($_POST["authEmail"]    ?? '');
            $twitter  = test_input($_POST["authTwitter"]  ?? '');
            $github   = test_input($_POST["authGithub"]   ?? '');
            $linkedin = test_input($_POST["authLinkedin"] ?? '');

            $imageName = '';
            if (isset($_FILES["authImage"]) && $_FILES["authImage"]["error"] === 0) {
                $imageName = secureUpload("authImage", "../img/avatar/");
            }

            $data = [
                "author_fullname" => $fullname,
                "author_desc"     => $desc,
                "author_email"    => $email,
                "author_twitter"  => $twitter,
                "author_github"   => $github,
                "author_link"     => $linkedin,
                "author_avatar"   => $imageName
            ];

            insertToDB($conn, 'author', $data);
            header("Location: ../author.php", true, 301);
            exit;


        /* ============================= COMMENT ============================= */
        case "comment":

            $id = filter_var($_POST["id_article"] ?? null, FILTER_VALIDATE_INT);
            if (!$id) {
                header("Location: ../index.php", true, 302);
                exit;
            }

            $username = test_input($_POST["username"] ?? '');
            $comment  = test_input($_POST["comment"]  ?? '');

            $data = [
                "comment_username" => $username,
                "comment_content"  => $comment,
                "comment_date"     => date('Y-m-d H:i:s'),
                "id_article"       => $id
            ];

            insertToDB($conn, 'comment', $data);

            header("Location: ../single_article.php?id={$id}#comment", true, 302);
            exit;

        default:
            http_response_code(400);
            exit("ERROR: Invalid Type");
    }
}

/* ---------------------------- DATA ACCESS LAYER --------------------------- */
function insertToDB($conn, $table, $data)
{
    $allowed = ['article', 'category', 'author', 'comment'];
    if (!in_array($table, $allowed, true)) {
        throw new RuntimeException('Blocked table name');
    }

    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", preg_filter('/^/', ':', array_keys($data)));

    try {
        $sql  = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);

    } catch (PDOException $error) {
        error_log("Insert({$table}) failed: " . $error->getMessage());
        http_response_code(500);
        exit("Database submission failed.");
    }
}

/* ---------------------------- INPUT UTILS --------------------------- */
function test_input($data)
{
    $data = (string)$data;
    $data = trim($data);
    $data = stripslashes($data);
    $data = str_replace(["\r", "\n"], '', $data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/* --------------------------- UPLOAD UTILS --------------------------- */
function secureUpload($field, $destDir)
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== 0) {
        return '';
    }

    if (!is_dir($destDir)) {
        @mkdir($destDir, 0755, true);
    }

    $tmp  = $_FILES[$field]['tmp_name'];
    $name = basename($_FILES[$field]['name']);
    $size = (int)$_FILES[$field]['size'];

    if ($size <= 0 || $size > 1024 * 1024) {
        return '';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp) ?: '';

    $allowedExt  = ['jpg','jpeg','png','gif'];
    $allowedMime = ['image/jpeg','image/png','image/gif'];

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
        return '';
    }

    $rand = bin2hex(random_bytes(8));
    $stored = "{$rand}.{$ext}";
    $target = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $stored;

    if (!move_uploaded_file($tmp, $target)) {
        return '';
    }

    return $stored;
}
