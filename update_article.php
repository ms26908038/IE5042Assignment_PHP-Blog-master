<!-- Include Head -->
<?php 
include "assest/head.php"; 

// ✅ 1. Validate Article ID
$article_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$article_id) {
    http_response_code(400);
    exit('Invalid article ID');
}

// ✅ Get article data
$stmt = $conn->prepare("SELECT * FROM article WHERE article_id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

// ✅ 404 if not found
if (!$article) {
    http_response_code(404);
    echo "<h2>Article not found.</h2>";
    exit;
}

// ✅ Get categories
$stmt = $conn->prepare("SELECT category_id, category_name FROM category");
$stmt->execute();
$categories = $stmt->fetchAll();

// ✅ Get authors
$stmt = $conn->prepare("SELECT author_id, author_fullname FROM author");
$stmt->execute();
$authors = $stmt->fetchAll();

// ✅ Helper for escaping
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// ✅ Clean image filename
$currentImgFile = basename($article['article_image'] ?? '');
$safeImgForAction = rawurlencode($currentImgFile);
$safeImgSrc = 'img/article/' . e($currentImgFile);
?>

<!-- JS TextEditor -->
<script src="//cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>

<title>Update Article</title>
</head>

<body>

<?php include "assest/header.php"; ?>

<main role="main" class="main">

    <div class="jumbotron text-center">
        <h1 class="display-3 font-weight-normal text-muted">Update Article</h1>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-lg-8 mb-4">

                <!-- ✅ Safe Action URL -->
                <form action="assest/update.php?type=article&id=<?= (int)$article_id ?>&img=<?= $safeImgForAction ?>" 
                      method="POST" enctype="multipart/form-data">

                    <!-- ✅ ✅ CSRF PROTECTION ADDED HERE -->
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">

                    <!-- Title -->
                    <div class="form-group">
                        <label for="arTitle">Title</label>
                        <input type="text" class="form-control"
                               name="arTitle" id="arTitle"
                               value="<?= e($article['article_title']) ?>">
                    </div>

                    <!-- Content -->
                    <div class="form-group">
                        <label for="arContent">Content</label>
                        <textarea class="form-control" name="arContent" id="arContent"
                                  rows="6"><?= $article['article_content'] ?></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group">
                        <label for="arImage">Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="arImage" id="arImage">
                            <label class="custom-file-label" for="arImage">
                                <?= e($article['article_image']) ?>
                            </label>
                        </div>
                    </div>

                    <div class="my-2" style="width: 200px;">
                        <img class="w-100 h-auto" src="<?= $safeImgSrc ?>" alt="Current article image">
                    </div>

                    <!-- Category -->
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

                    <!-- Author -->
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
                        <button type="submit" name="update" class="btn btn-success btn-lg w-25">
                            Update
                        </button>
                    </div>

                </form>
            </div>

            <div class="col-lg-4 mb-4">
                <!-- Optional sidebar -->
            </div>

        </div>
    </div>

</main>

<!-- CKEditor -->
<script>
    CKEDITOR.replace('arContent');
</script>

</body>
</html>