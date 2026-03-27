<?php include "assest/head.php"; ?>

<?php
/***********************************************************************
 * single_article.php — SECURITY HARDENED
 * - Prevent SQLi/type-juggling on ?id
 * - Prevent path traversal on image/avatars
 * - Prevent CSS injection in inline styles
 * - Encode most outputs to reduce XSS risk (content left as HTML for now)
 ***********************************************************************/

// ✅ FIX: Helper for safe HTML output
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ✅ FIX: Helper to allow only hex colors (#RGB or #RRGGBB) for inline style
function safe_color($c) {
    return (is_string($c) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $c))
        ? $c
        : '#666'; // fallback
}

// ✅ FIX: Validate and normalize the incoming ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    exit('Invalid article ID');
}
$article_id = (int) $_GET['id'];

// Get Article Info
$stmt = $conn->prepare("
    SELECT a.*, au.*
    FROM `article` a
    INNER JOIN `author` au ON a.id_author = au.author_id
    WHERE a.`article_id` = ?
");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

// ✅ FIX: 404 if article not found
if (!$article) {
    http_response_code(404);
    include 'assest/header.php';
    echo '<div class="container py-5"><h2>Article not found</h2></div>';
    include 'assest/footer.php';
    exit;
}

// Get Category of article
$stmt = $conn->prepare("SELECT * FROM `category` WHERE `category_id` = ?");
$stmt->execute([$article["id_categorie"]]);
$category = $stmt->fetch();

// Get Author's other articles
$stmt = $conn->prepare("SELECT article_title, article_id FROM `article` WHERE id_author = ? LIMIT 4");
$stmt->execute([$article["id_author"]]);
$authorArticles = $stmt->fetchAll();

// Get Comments
$stmt = $conn->prepare("
    SELECT c.*
    FROM `comment` c
    WHERE c.`id_article` = ?
    ORDER BY c.comment_id DESC
");
$stmt->execute([$article_id]);
$comments = $stmt->fetchAll();

// ✅ FIX: Build safe asset paths (prevents path traversal)
$articleImg = 'img/article/' . e(basename($article['article_image'] ?? ''));
$authorAvatar = 'img/avatar/' . e(basename($article['author_avatar'] ?? ''));

// ✅ FIX: Sanitize category props used in UI
$catId    = isset($category['category_id']) ? (int)$category['category_id'] : 0;
$catName  = isset($category['category_name']) ? e($category['category_name']) : 'Uncategorized';
$catColor = safe_color($category['category_color'] ?? '#666');

?>
<!-- Custom CSS -->
<link type="text/css" rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="css/single_article.css">

<title>Single Article</title>
</head>

<body>

    <!-- Header -->
    <?php include "assest/header.php" ?>
    <!-- /Header -->

    <!-- Main -->
    <main role="main" class="bg-l py-4">
        <div class="container">
            <div class="row">

                <!-- Article -->
                <div class="content bg-white col-lg-9 p-0 border border-muted">

                    <!-- Post Image -->
                    <div class="post__img" style="background-image: url('<?= $articleImg ?>');"></div>

                    <!-- Post Content -->
                    <div class="post__content w-75 mx-auto">

                        <div class="post-head text-center my-5">
                            <h1 class="post__title">
                                <!-- ✅ Encode title -->
                                <?= e($article["article_title"]) ?>
                            </h1>

                            <div class="post-meta ">
                                <span class="post__date">
                                    <?= e(date_format(date_create($article["article_created_time"]), "F d, Y ")) ?>
                                </span>
                                <a class="post-category"
                                   href="articleOfCategory.php?catID=<?= $catId ?>"
                                   style="background-color:<?= $catColor ?>;">
                                    <?= $catName ?>
                                </a>
                            </div>
                        </div>

                        <div class="post-body text-break">

                            <?php
                            // ⚠ NOTE: article_content is rich HTML from editor.
                            // For the dedicated XSS fix, we will sanitize/whitelist allowed tags.
                            // For now, render as-is to preserve formatting.
                            echo $article["article_content"];
                            ?>

                        </div>

                        <!-- author Info -->
                        <div class="post-footer d-flex my-5">
                            <img class="profile-thumbnail rounded-circle pr-2"
                                 src="<?= $authorAvatar ?>"
                                 alt="author avatar"
                                 style="width: 120px;height: 120px;">
                            <div class="d-flex flex-column justify-content-around">
                                <h3 class="font-italic mb-1"><?= e($article['author_fullname']) ?></h3>
                                <p class="text-muted mb-1"><?= e($article['author_desc']) ?></p>
                                <div class="social_media">
                                    <!-- ✅ Encode handles (placed in visible text; URLs go to trusted domains) -->
                                    <a href="https://twitter.com/<?= urlencode((string)$article['author_twitter']) ?>" class="mr-3">
                                        <i class="fa fa-twitter"></i>
                                        <span class="px-1"><?= e($article['author_twitter']) ?></span>
                                    </a>
                                    <a href="https://github.com/<?= urlencode((string)$article['author_github']) ?>" class="mr-3">
                                        <i class="fa fa-github"></i>
                                        <span class="px-1"><?= e($article['author_github']) ?></span>
                                    </a>
                                    <a href="https://linkedin.com/<?= urlencode((string)$article['author_link']) ?>" class="mr-3">
                                        <i class="fa fa-linkedin-square"></i>
                                        <span class="px-1"><?= e($article['author_link']) ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                </div><!-- /Article -->

                <!-- Aside -->
                <div class="aside col-3">
                    <!-- Author Info -->
                    <div class="p-3 bg-white border  border-muted">
                        <div class="d-flex align-items-center">
                            <img class="profile-thumbnail rounded-circle"
                                 src="<?= $authorAvatar ?>"
                                 alt="author avatar"
                                 style="width: 60px;height: 60px;">
                            <h5 class="font-italic m-0"><?= e($article['author_fullname']) ?></h5>
                        </div>
                        <p class="author_desc p-1"><?= e($article['author_desc']) ?></p>
                        <div class="d-flex flex-column justify-content-between">
                            <div class="author_links">
                                <a href="https://twitter.com/<?= urlencode((string)$article['author_twitter']) ?>" class="mr-3">
                                    <i class="fa fa-lg fa-twitter"></i>
                                </a>
                                <a href="https://github.com/<?= urlencode((string)$article['author_github']) ?>" class="mr-3">
                                    <i class="fa fa-lg fa-github"></i>
                                </a>
                                <a href="https://linkedin.com/<?= urlencode((string)$article['author_link']) ?>" class="mr-3">
                                    <i class="fa fa-lg fa-linkedin-square"></i>
                                </a>
                            </div>
                        </div>
                    </div><!-- /Author Info -->

                    <div class="card bg-light my-3">
                        <div class="card-header"><strong> More from <?= e($article['author_fullname']) ?></strong></div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($authorArticles as $a): ?>
                                <li class="list-group-item">
                                    <a href="single_article.php?id=<?= (int)$a['article_id'] ?>">
                                        <?= e($a['article_title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div><!-- /Aside -->

            </div>

            <!-- Comments -->
            <div id="comment" class="row">
                <div class="col-lg-9 border p-4 mt-3 bg-white">

                    <div class="comments">
                        <h2 class="text-center text-muted py-3">Comments</h2>

                        <?php foreach ($comments as $comment): ?>
                            <?php
                                $commentAvatar = 'img/avatar/' . e(basename($comment['comment_avatar'] ?? ''));
                            ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2 pr-0 text-center">
                                            <img src="<?= $commentAvatar ?>" class="img img-rounded img-fluid w-50" alt="user avatar"/>
                                        </div>
                                        <div class="col-md-10">
                                            <p>
                                                <a class="float-left" href="#"><strong><?= 'User-' . e($comment['comment_username']) ?></strong></a>
                                                <span class="float-right px-2 text-muted">
                                                    <?= e(date_format(date_create($comment['comment_date']), "d F Y h:i")) ?>
                                                </span>
                                            </p>
                                            <div class="clearfix"></div>
                                            <p class="text-secondary mt-2"><?= e($comment['comment_content']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="post-comments">
                        <form action="assest/insert.php?type=comment&id=<?= $article_id ?>#comment" method="POST">
                            <div class="form-group mt-3">
                                <input type="hidden" name="username" value="<?= rand() ?>">
                                <input type="hidden" name="id_article" value="<?= $article_id ?>">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Add your comment..."></textarea>
                                <button name="submit" type="submit" class="btn btn-success float-right mt-1">Add Comment</button>
                            </div>
                            <div class="clearfix"></div>
                        </form>
                    </div>

                </div>
            </div><!-- /Comments -->
        </div>

    </main><!-- /Main -->

    <!-- Footer -->
    <?php include "assest/footer.php" ?>
    <!-- /Footer -->

</body>
</html>
``