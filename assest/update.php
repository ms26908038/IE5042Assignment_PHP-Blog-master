<?php require "db.php"; ?>
<?php
/***********************************************************************
 * UPDATE ENDPOINT — SECURITY HARDENED
 * - Prevents SQLi / parameter tampering on ?type, ?id, ?img
 * - Keeps original business logic and prepared statements
 * - Do NOT echo raw DB errors to users (log instead)
 ***********************************************************************/

/* ----------------------- INPUT VALIDATION (CRITICAL) ---------------- */

// ✅ FIX: Allow‑list the update type to block tampering (?type=…)
$allowed_types = ['article', 'category', 'author'];
$type = $_GET['type'] ?? '';
if (!in_array($type, $allowed_types, true)) {
    http_response_code(400);
    exit('Invalid update type');
}

// ✅ FIX: Strict integer validation for the target record ID (?id=…)
$rawId = $_GET['id'] ?? null;
$urlId = filter_var($rawId, FILTER_VALIDATE_INT);
if (!$urlId) {
    http_response_code(400);
    exit('Invalid ID');
}

// ✅ FIX: Sanitize the image name coming from URL (?img=…) to prevent traversal
$urlImage = basename($_GET['img'] ?? '');

/* --------------------------- ORIGINAL FLOW -------------------------- */

if ($conn) {
    if (isset($_POST["update"])) {

        switch ($type) {

            /* ============================ ARTICLE ============================ */
            case "article":

                // Sanitize text inputs (HTML for content is allowed here intentionally)
                $title     = test_input($_POST["arTitle"]     ?? '');
                $content   = $_POST["arContent"]              ?? '';  // rich text via editor
                $categorie = test_input($_POST["arCategory"]  ?? '');
                $author    = test_input($_POST["arAuthor"]    ?? '');
                $imageName = test_input($_FILES["arImage"]["name"] ?? '');

                // If a new image was posted, upload it; otherwise keep previous
                if (isset($_FILES["arImage"]) && $_FILES["arImage"]['error'] === 0) {
                    uploadImage2("arImage", "../img/article/");
                } else {
                    $imageName = $urlImage;
                }

                try {
                    $sql = "UPDATE `article`
                               SET `article_title`   = ?,
                                   `article_content` = ?,
                                   `article_image`   = ?,
                                   `id_categorie`    = ?,
                                   `id_author`       = ?
                             WHERE `article_id`      = ?";

                    $stmt = $conn->prepare($sql);
                    // ✅ Uses validated $urlId (integer) + prepared statement
                    $stmt->execute([$title, $content, $imageName, $categorie, $author, $urlId]);

                } catch (PDOException $e) {
                    // ✅ Do not leak DB internals
                    error_log("Update(article) failed: " . $e->getMessage());
                    http_response_code(500);
                    exit('Internal error');
                }

                header("Location: ../article.php", true, 301);
                exit;


            /* =========================== CATEGORY ============================ */
            case "category":

                $name      = test_input($_POST["catName"]      ?? '');
                $color     = test_input($_POST["catColor"]     ?? '');
                $imageName = test_input($_FILES["catImage"]["name"] ?? '');

                if (isset($_FILES["catImage"]) && $_FILES["catImage"]['error'] === 0) {
                    uploadImage2("catImage", "../img/category/");
                } else {
                    $imageName = $urlImage;
                }

                try {
                    $sql = "UPDATE `category`
                               SET `category_name`  = ?,
                                   `category_image` = ?,
                                   `category_color` = ?
                             WHERE `category_id`    = ?";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$name, $imageName, $color, $urlId]);

                } catch (PDOException $e) {
                    error_log("Update(category) failed: " . $e->getMessage());
                    http_response_code(500);
                    exit('Internal error');
                }

                header("Location: ../categories.php", true, 301);
                exit;


            /* ============================= AUTHOR ============================ */
            case "author":

                $fullName    = test_input($_POST["authName"]     ?? '');
                $description = test_input($_POST["authDesc"]     ?? '');
                $email       = test_input($_POST["authEmail"]    ?? '');
                $twitter     = test_input($_POST["authTwitter"]  ?? '');
                $github      = test_input($_POST["authGithub"]   ?? '');
                $linkedin    = test_input($_POST["authLinkedin"] ?? '');
                $imageName   = test_input($_FILES["authImage"]["name"] ?? '');

                if (isset($_FILES["authImage"]) && $_FILES["authImage"]['error'] === 0) {
                    uploadImage2("authImage", "../img/avatar/");
                } else {
                    $imageName = $urlImage;
                }

                try {
                    $sql = "UPDATE `author`
                               SET `author_fullname` = ?,
                                   `author_desc`     = ?,
                                   `author_email`    = ?,
                                   `author_twitter`  = ?,
                                   `author_github`   = ?,
                                   `author_link`     = ?,
                                   `author_avatar`   = ?
                             WHERE `author_id`       = ?";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$fullName, $description, $email, $twitter, $github, $linkedin, $imageName, $urlId]);

                } catch (PDOException $e) {
                    error_log("Update(author) failed: " . $e->getMessage());
                    http_response_code(500);
                    exit('Internal error');
                }

                header("Location: ../author.php", true, 301);
                exit;

            default:
                http_response_code(400);
                exit('Invalid operation');
        }
    }
} else {
    http_response_code(500);
    exit('Database connection error');
}


/* =========================== HELPERS (UNCHANGED) =========================== */
/* NOTE: We will harden uploadImage2 in a separate "File Upload Security" fix. */

function uploadImage2($name, $dest)
{
    $target_dir    = $dest;
    $target_file   = $target_dir . basename($_FILES[$name]["name"]);
    $uploadOk      = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES[$name]["tmp_name"]);
    if ($check === false) { $uploadOk = 0; }

    if (file_exists($target_file)) { $uploadOk = 0; }

    if ($_FILES[$name]["size"] > 500000) { $uploadOk = 0; }

    if (!in_array($imageFileType, ['jpg','jpeg','png','gif'], true)) { $uploadOk = 0; }

    if ($uploadOk == 1) {
        move_uploaded_file($_FILES[$name]["tmp_name"], $target_file);
    }
}

function test_input($data)
{
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, 'UTF-8');
}
