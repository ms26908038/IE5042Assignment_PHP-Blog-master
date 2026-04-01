<!-- Include Head -->
<?php 
include "assest/head.php"; 

// ✅ 1. Validate Category ID (Prevents XSS & Parameter Tampering)
$raw_id = $_GET['id'] ?? null;
$category_id = filter_var($raw_id, FILTER_VALIDATE_INT);

if (!$category_id) {
    http_response_code(400);
    exit("Invalid Category ID");
}

// ✅ 2. Get Category Data
$stmt = $conn->prepare("SELECT * FROM category WHERE category_id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    exit("Category not found");
}

// ✅ Normalize image filename
$currentImage = basename($category["category_image"]);

// ✅ Safe output helper
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<title>Update Category</title>
</head>

<body>

    <!-- Header -->
    <?php include "assest/header.php" ?>

    <main role="main" class="main">

        <div class="jumbotron text-center">
            <h1 class="display-3 font-weight-normal text-muted">Update a Category</h1>
        </div>

        <div class="container">
            <div class="row">

                <div class="col-lg-12 mb-4">

                    <!-- ✅ SAFE: form action sanitized -->
                    <form 
                        action="assest/update.php?type=category&id=<?= e($category_id) ?>&img=<?= e($currentImage) ?>" 
                        method="POST" 
                        enctype="multipart/form-data">

                        <!-- ✅ ✅ CSRF TOKEN added immediately after <form> -->
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="form-group">
                            <label for="catName">Category Name</label>
                            <input type="text" class="form-control" name="catName" id="catName"
                                   value="<?= e($category["category_name"]) ?>">
                        </div>

                        <div class="form-group">
                            <label for="catImage">Category Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="catImage" id="catImage">
                                <label class="custom-file-label" for="catImage">
                                    <?= e($currentImage) ?>
                                </label>
                            </div>
                        </div>

                        <div class="my-2" style="width: 200px;">
                            <img class="w-100 h-auto" src="img/category/<?= e($currentImage) ?>" alt="Category Image">
                        </div>

                        <div class="form-group">
                            <label for="catColor">Category Color</label>
                            <input type="color" id="catColor" name="catColor"
                                   value="<?= e($category["category_color"]) ?>">
                        </div>

                        <div class="text-center">
                            <button type="submit" name="update" class="btn btn-success btn-lg w-25">Update</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>

    </main>

</body>
</html>