<?php // 1. Database Connection
require "db.php"; //

// 2. Set secure cookie parameters to fix ID: B04: Session Fixation / Hijacking (A07:2021) 
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', 
    'secure' => true,     // Only send over HTTPS (Note: may need 'false' for local XAMPP without SSL)
    'httponly' => true,   // Prevents JavaScript from stealing the session cookie
    'samesite' => 'Lax'   // Helps prevent CSRF attacks
]);

// SECURE IMPLEMENTATION FOR ID: B11: Content Security Policy (CSP) Header Not Set File
// This policy allows scripts from own domain ('self') 
// and the trusted jQuery CDN used in B10.
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://code.jquery.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;");


// 3. SECURE HEADERS IMPLEMENTATION for ID: B05: Clickjacking Risk
// Prevent Clickjacking: Only your own site can frame itself.
header("X-Frame-Options: SAMEORIGIN");

// Modern Clickjacking fix: Only allow framing by the same domain.
header("Content-Security-Policy: frame-ancestors 'self';");

// Prevents the browser from "sniffing" the MIME type (Prevents XSS)
header("X-Content-Type-Options: nosniff");

// 4. Initialize the session
session_start(); //

// 5. CSRF Token Generation (Used in your login form) for ID: B02: Missing Anti-Cross-Site Request Forgery (CSRF) Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 6. Check Login Status
$loggedin = false;
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
   $loggedin = true; //
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