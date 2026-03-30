<?php include "assest/head.php"; ?>

<?php
// REMEDIATION: Using filter_var to ensure the ID is an integer immediately
$raw_id = $_GET['id'] ?? 0;
$article_id = filter_var($raw_id, FILTER_VALIDATE_INT);

if (!$article_id) {
    die("Invalid Article ID");
}

// Get Article Info
$stmt = $conn->prepare("SELECT * FROM `article` INNER JOIN `author` ON `article`.id_author = `author`.author_id  WHERE `article_id` = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    die("Article not found");
}

// Get Category of article
$stmt = $conn->prepare("SELECT * FROM `category` WHERE `category_id` = ?");
$stmt->execute([$article["id_categorie"]]);
$category = $stmt->fetch();

// Get Author's articles
$stmt = $conn->prepare("SELECT article_title, article_id FROM `article` WHERE id_author = ? LIMIT 4");
$stmt->execute([$article["id_author"]]);
$articles = $stmt->fetchAll();

// Get Comments
$stmt = $conn->prepare("SELECT * FROM `article` INNER JOIN `comment` WHERE `article`.`article_id`= `comment`.`id_article` AND `article`.`article_id` = ? ORDER BY comment_id DESC");
$stmt->execute([$article_id]);
$comments = $stmt->fetchAll();
?>

<link type="text/css" rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="css/single_article.css">

<title>Single Article</title>

</head>

<body>

    <?php include "assest/header.php" ?>
    <main role="main" class="bg-l py-4">

        <div class="container">

            <div class="row">

                <div class="content bg-white col-lg-9 p-0 border border-muted">


                    <div class="post__img" style="background-image: url('img/article/<?= htmlspecialchars($article["article_image"], ENT_QUOTES, 'UTF-8') ?>');"></div>

                    <div class="post__content w-75 mx-auto">

                        <div class="post-head text-center my-5">
                            <h1 class="post__title">
                                <?= htmlspecialchars($article["article_title"], ENT_QUOTES, 'UTF-8') ?>
                            </h1>

                            <div class="post-meta ">
                                <span class="post__date">
                                    <?= date_format(date_create($article["article_created_time"]), "F d, Y ") ?>
                                </span>
                                <a class="post-category" href="articleOfCategory.php?catID=<?= htmlspecialchars($category['category_id'], ENT_QUOTES, 'UTF-8') ?>" style="background-color:<?= htmlspecialchars($category['category_color'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </div>
                        </div>

                        <div class="post-body text-break">
                            <?= $article["article_content"] ?>
                        </div>

                        <div class="post-footer d-flex my-5">

                            <img class="profile-thumbnail rounded-circle pr-2" src="img/avatar/<?= htmlspecialchars($article['author_avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="author avatar" style="width: 120px;height: 120px;">
                            <div class="d-flex flex-column justify-content-around">
                                <h3 class="font-italic mb-1"><?= htmlspecialchars($article['author_fullname'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="text-muted mb-1"><?= htmlspecialchars($article['author_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="social_media">
                                    <a href="#" class="mr-3"><i class="fa fa-twitter"></i><span class="px-1"><?= htmlspecialchars($article['author_twitter'], ENT_QUOTES, 'UTF-8') ?></span></a>
                                    <a href="#" class="mr-3"><i class="fa fa-github"></i><span class="px-1"><?= htmlspecialchars($article['author_github'], ENT_QUOTES, 'UTF-8') ?></span></a>
                                    <a href="#" class="mr-3"><i class="fa fa-linkedin-square"></i><span class="px-1"><?= htmlspecialchars($article['author_link'], ENT_QUOTES, 'UTF-8') ?></span></a>
                                </div>
                            </div>
                        </div>

                    </div>


                </div><div class="aside col-3">
                    <div class="p-3 bg-white border  border-muted">
                        <div class="d-flex align-items-center">
                            <img class="profile-thumbnail rounded-circle" src="img/avatar/<?= htmlspecialchars($article['author_avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="author avatar" style="width: 60px;height: 60px;">
                            <h5 class="font-italic m-0"><?= htmlspecialchars($article['author_fullname'], ENT_QUOTES, 'UTF-8') ?></h5>
                        </div>
                        <p class="author_desc p-1"><?= htmlspecialchars($article['author_desc'], ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="d-flex flex-column justify-content-between">
                            <div class="author_links">
                                <a href="https://twitter.com/<?= htmlspecialchars($article['author_twitter'], ENT_QUOTES, 'UTF-8') ?>" class="mr-3"><i class="fa fa-lg fa-twitter"></i></a>
                                <a href="https://github.com/<?= htmlspecialchars($article['author_github'], ENT_QUOTES, 'UTF-8') ?>" class="mr-3"><i class="fa fa-lg fa-github"></i></a>
                                <a href="https://linkedin.com/<?= htmlspecialchars($article['author_link'], ENT_QUOTES, 'UTF-8') ?>" class="mr-3"><i class="fa fa-lg fa-linkedin-square"></i></a>
                            </div>
                        </div>
                    </div><div class="card bg-light my-3">
                        <div class="card-header"><strong> More from <?= htmlspecialchars($article['author_fullname'], ENT_QUOTES, 'UTF-8') ?></strong></div>

                        <ul class="list-group list-group-flush">
                            <?php foreach ($articles as $sidebar_article) : ?>
                                <li class="list-group-item">
                                    <a href="single_article.php?id=<?= htmlspecialchars($sidebar_article['article_id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($sidebar_article['article_title'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>


                </div></div>


            <div id="comment" class="row">
                <div class="col-lg-9 border p-4 mt-3 bg-white">

                    <div class="comments">
                        <h2 class="text-center text-muted py-3">Comments</h2>

                        <?php foreach ($comments as $comment) : ?>

                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-2 pr-0 text-center">
                                            <img src="img/avatar/default.png" class="img img-rounded img-fluid w-50" />
                                        </div>
                                        <div class="col-md-10">
                                            <p>
                                                <a class="float-left" href="#"><strong>User-<?= htmlspecialchars($comment['comment_username'], ENT_QUOTES, 'UTF-8') ?></strong></a>
                                                <span class="float-right px-2 text-muted"><?= date_format(date_create($comment['comment_date']), "d F Y h:i") ?></span>
                                            </p>
                                            <div class="clearfix"></div>
                                            <p class="text-secondary mt-2"><?= htmlspecialchars($comment['comment_content'], ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        <?php endforeach; ?>
                    </div>

                    <div class="post-comments">
                        <form action="assest/insert.php?type=comment&id=<?= htmlspecialchars($article_id, ENT_QUOTES, 'UTF-8') ?>#comment" method="POST">
                            <div class="form-group mt-3">
                                <input type="hidden" name="username" value="<?= rand() ?>">
                                <input type="hidden" name="id_article" value="<?= htmlspecialchars($article_id, ENT_QUOTES, 'UTF-8') ?>">
                                <textarea name="comment" class="form-control" rows="3" placeholder="Add your comment..."></textarea>
                                <button name="submit" type="submit" class="btn btn-success float-right mt-1">Add Comment</button>
                            </div>
                            <div class="clearfix"></div>
                        </form>

                    </div>


                </div>
            </div></div>

    </main><?php include "assest/footer.php" ?>
    </body>

</html>