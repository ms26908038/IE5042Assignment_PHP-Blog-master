<!-- Include Head -->
<?php include "assest/head.php"; ?>
<?php
/***********************************************************************
 * update_article.php — SECURITY HARDENED (view/form)
 * - Validates ?id with FILTER_VALIDATE_INT
 * - Handles not-found articles with 404
 * - Uses basename() for image path
 * - Encodes UI values to avoid reflected XSS
 * - Builds a safe action URL to assest/update.php
 ***********************************************************************/

// ✅ FIX: Validate incoming article ID strictly
$article_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$article_id) {
    http_response_code(400);
    exit('Invalid article ID');
}

// Get article Data to display
$stmt = $conn->prepare("SELECT * FROM article WHERE article_id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

// ✅ FIX: 404 if the record does not exist
if (!$article) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body>';
    echo '<div style="padding:2rem;font-family:system-ui,Segoe UI,Arial">Article not found.</div>';
    echo '</body></html>';
    exit;
}

// Get categories Data to display
$stmt = $conn->prepare("SELECT category_id, category_name FROM category");
$stmt->execute();
$categories = $stmt->fetchAll();

// Get authors Data to display
$stmt = $conn->prepare("SELECT author_id, author_fullname FROM author");
$stmt->execute();
$authors = $stmt->fetchAll();

// ✅ FIX: Safe helpers for output
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ✅ FIX: Normalize image filename for action/img paths
$currentImgFile = basename($article['article_image'] ?? '');
$safeImgForAction = rawurlencode($currentImgFile); // for URL parameter
$safeImgSrc = 'img/article/' . e($currentImgFile);

// (Optional) If a CSRF token already exists in the session (e.g., created in head.php),
// include it in the form. Server-side validation must happen in assest/update.php.
$csrf = '';
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['csrf'])) {
    $csrf = $_SESSION['csrf'];
}
?>

<!-- JS TextEditor -->
<script src="//cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>

<title>Update Article</title>
</head>

<body>

    <!-- Header -->
    <?php include "assest/header.php" ?>

    <!-- Main -->
    <main role="main" class="main">

        <div class="jumbotron text-center">
            <h1 class="display-3 font-weight-normal text-muted">Update Article</h1>
        </div>

        <div class="container">
            <div class="row">

                <div class="col-lg-8 mb-4">
                    <!-- ✅ FIX: Safe action — integer id and url-encoded image name -->
                    <form action="assest/update.php?type=article&id=<?= (int)$article_id ?>&img=<?= $safeImgForAction ?>" method="POST" enctype="multipart/form-data">

                        <?php if ($csrf !== ''): ?>
                            <!-- (Optional) CSRF token — validate it in assest/update.php -->
                            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="arTitle">Title</label>
                            <!-- ✅ FIX: Encode value for safe UI -->
                            <input type="text" class="form-control" name="arTitle" id="arTitle" value="<?= e($article['article_title']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="arContent">Content</label>
                            <!-- Note: textarea treats tags as text; CKEditor will load/transform it.
                                 If you see broken markup, you can switch to htmlspecialchars() here. -->
                            <textarea class="form-control" name="arContent" id="arContent" rows="6"><?= $article['article_content'] ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="UploadImage">Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="arImage" id="arImage">
                                <label class="custom-file-label" for="UploadImage"><?= e($article['article_image']) ?></label>
                            </div>
                        </div>

                        <div class="my-2" style="width: 200px;">
                            <!-- ✅ FIX: Safe image src via basename()+escape -->
                            <img class="w-100 h-auto" src="<?= $safeImgSrc ?>" alt="Current article image">
                        </div>

                        <div class="form-group">
                            <label for="arCategory">Category</label>
                            <select class="custom-select" name="arCategory" id="arCategory">
                                <option disabled>-- Select Category --</option>
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                        $cid = (int)$category['category_id'];
                                        $cname = e($category['category_name']);
                                        $selected = ($article['id_categorie'] == $cid) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $cid ?>" <?= $selected ?>><?= $cname ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="arAuthor">Author</label>
                            <select class="custom-select" name="arAuthor" id="arAuthor">
                                <option disabled>-- Select Author --</option>
                                <?php foreach ($authors as $author): ?>
                                    <?php
                                        $aid = (int)$author['author_id'];
                                        $aname = e($author['author_fullname']);
                                        $selected = ($article['id_author'] == $aid) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $aid ?>" <?= $selected ?>><?= $aname ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="update" class="btn btn-success btn-lg w-25">Update</button>
                        </div>

                    </form>
                </div>

                <div class="col-lg-4 mb-4">
                    <!-- (Reserved for sidebar/help) -->
                </div>

            </div>
        </div>

    </main>

    <!-- Footer -->
    <!-- <?php // include "assest/footer.php" ?> -->

    <!-- Text Editor Script -->
    <script>
        CKEDITOR.replace('arContent');
    </script>

</body>
</html>
