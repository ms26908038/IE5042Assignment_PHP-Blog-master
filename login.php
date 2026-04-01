<?php include "assest/head.php"; ?>

<?php
// If user already logged in → redirect
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// Define variables
$username = $password = "";
$username_err = $password_err = "";

// ✅ CSRF validation BEFORE any other login logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["csrf"]) || 
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf"])) {
        http_response_code(403);
        exit("Invalid CSRF token");
    }

    // ✅ Username validation
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // ✅ Password validation
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // If no validation errors, check credentials
    if (empty($username_err) && empty($password_err)) {

        $sql = "SELECT * FROM users WHERE username = :username";

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);

            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {

                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];

                        if (password_verify($password, $hashed_password)) {

                            // Password correct → login user
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

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

    unset($pdo);
}
?>