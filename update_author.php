<!-- Include Head -->
<?php 
include "assest/head.php"; 

// ✅ 1. Validate Author ID (Prevents XSS & Parameter Tampering)
$raw_id = $_GET['id'] ?? null;
$author_id = filter_var($raw_id, FILTER_VALIDATE_INT);
if (!$author_id) {
    http_response_code(400);
    exit("Invalid Author ID");
}

// ✅ 2. Get Author Data
$stmt = $conn->prepare("SELECT * FROM author WHERE author_id = ?");
$stmt->execute([$author_id]);
$author = $stmt->fetch();

if (!$author) {
    http_response_code(404);
    exit("Author not found");
}

// ✅ Normalize avatar filename (Prevents Path Traversal)
$currentAvatar = basename($author["author_avatar"]);

// ✅ Helper for safe output
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<title>Update Author</title>
</head>

<body>

    <!-- Header -->
    <?php include "assest/header.php" ?>

    <!-- Main -->
    <main role="main" class="main">

        <div class="jumbotron text-center">
            <h1 class="display-3 font-weight-normal text-muted">Update Author</h1>
        </div>

        <div class="container">
            <div class="row">

                <div class="col-lg-12 mb-4">

                    <!-- ✅ FIXED: Safe action URL -->
                    <form action="assest/update.php?type=author&id=<?= e($author_id) ?>&img=<?= e($currentAvatar) ?>" 
                          method="POST" enctype="multipart/form-data">

                        <div class="form-group">
                            <label for="authName">Full Name</label>
                            <input type="text" class="form-control" name="authName" id="authName" 
                                   value="<?= e($author['author_fullname']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="authDesc">Description</label>
                            <input type="text" class="form-control" name="authDesc" id="authDesc" 
                                   value="<?= e($author['author_desc']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="authEmail">Email</label>
                            <input type="text" class="form-control" name="authEmail" id="authEmail"
                                   value="<?= e($author['author_email']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="authImage">Avatar</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="authImage" id="authImage">
                                <label class="custom-file-label" for="authImage"><?= e($currentAvatar) ?></label>
                            </div>
                        </div>

                        <div class="my-2" style="width: 200px;">
                            <img class="w-100 h-auto" src="img/avatar/<?= e($currentAvatar) ?>" alt="">
                        </div>

                        <div class="form-group">
                            <label for="authTwitter">Twitter Username <span class="text-info">(optional)</span></label>
                            <input type="text" class="form-control" name="authTwitter" id="authTwitter"
                                   value="<?= e($author['author_twitter']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="authGithub">Github Username <span class="text-info">(optional)</span></label>
                            <input type="text" class="form-control" name="authGithub" id="authGithub"
                                   value="<?= e($author['author_github']) ?>">
                        </div>

                        <div class="form-group">
                            <label for="authLinkedin">LinkedIn Username <span class="text-info">(optional)</span></label>
                            <input type="text" class="form-control" name="authLinkedin" id="authLinkedin"
                                   value="<?= e($author['author_link']) ?>">
                        </div>

                        <div class="text-center">
                            <button type="submit" name="update" class="btn btn-success btn-lg w-25">Submit</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>

    </main>

</body>
</html>