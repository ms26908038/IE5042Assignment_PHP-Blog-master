<?php require "db.php"; ?>

<?php

// Set secure cookie parameters to fix the ZAP alert
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', 
    'secure' => true,     // Only send over HTTPS
    'httponly' => true,   // [FIXES THE ALERT] Prevents JS access
    'samesite' => 'Lax'   // Helps prevent CSRF
]);

// Initialize the session
session_start();

// Generate a CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$loggedin = false;

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
  
// This makes the old session ID useless to an attacker
    if (!isset($_SESSION['rotated'])) {
        session_regenerate_id(true);
        $_SESSION['rotated'] = true;
    }

$loggedin = true;

//}


}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/logo/flogo.png" sizes="32x32" type="image/png">

    <!-- Bootstrap, FontAwesome, Custom Styles -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.css">
    <!-- <link rel="stylesheet" href="css/footer.css">    -->
    <!-- <link type="text/css" rel="stylesheet" href="css/style.css" /> -->