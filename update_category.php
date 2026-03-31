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

// ✅ Normalize image filename (prevents path traversal)
$currentImage = basename($category["category_image"]);

// ✅ Helper for safe output encoding
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<title>Update Category</title>
</head>

<body>

    <!-- Header -->
    <?php include "assest/header.php" ?>

    <!-- Main -->
    <main role="main" class="main">

        <div class="jumbotron text-center ">
            <h1 class="display-3 font-weight-normal text-muted">Update a Category</h1>
        </div>

        <div class="container">

            <div class="row">

                <div class="col-lg-12 mb-4">

                    <!-- ✅ FIXED: Safe form action URL -->
                    <form 
                        action="assest/update.php?type=category&id=<?= e($category_id) ?>&img=<?= e($currentImage) ?>" 
                        method="POST" enctype="multipart/form-data">

                        <div class="form-group">
                            <label for="catName">Category Name</label>
                            <input type="text" class="form-control" name="catName" id="catName" 
