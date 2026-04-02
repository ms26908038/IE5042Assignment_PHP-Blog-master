<?php 
// 1. Include head.php (This already starts the session, connects to DB, and generates the token)
include "assest/head.php"; 

// Check if the user is already logged in, if yes then redirect him to index
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";

// 2. Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- CSRF VALIDATION ---
    // Since head.php generated the token, we just check if the form's token matches the session
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed. Unauthorized request.");
    }
    // -----------------------

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        // Note: Using $conn because that is what you used in your db.php
        $sql = "SELECT * FROM users WHERE username = :username";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);

            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["user_id"]; 
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, store data in session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Security: Regenerate ID to prevent Session Fixation
                            session_regenerate_id(true);

                            header("location: index.php");
                            exit;
                        } else {
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $username_err = "No account found with that username.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            unset($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logo/flogo.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.css">
    <link type="text/css" rel="stylesheet" href="css/style.css" />
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:700%7CNunito:300,600" rel="stylesheet">
    <title>Login</title>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "assest/header.php" ?>

    <main class="main">
        <div class="section jumbotron mb-0 h-100">
            <div class="container d-flex flex-column justify-content-center align-items-center h-100">
                
                <div class="wrapper my-0 pt-3 bg-white w-50 text-center">
                    <img src="img/logo/logo.png" alt="logo" style="width: 100px;height: auto;">
                </div>

                <div class="wrapper bg-white rounded px-4 py-4 w-50">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control <?= (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?= htmlspecialchars($username); ?>">
                            <span class="invalid-feedback"><?= $username_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control <?= (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback"><?= $password_err; ?></span>
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn btn-success" value="Login">
                        </div>
                        <p><a href="#" class="text-muted">Lost your password?</a></p>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>