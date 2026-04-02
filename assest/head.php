<?php
require "db.php";

/**
 * ------------------------------------------------------------
 * Global Application Bootstrap (Security Hardened)
 * ------------------------------------------------------------
 * - Starts session before any output
 * - Generates CSRF token (once per session)
 * - Sends HTTP Security Headers (CSP, XFO, Nosniff, etc.)
 * - Sets login status flag for navigation logic
 * ------------------------------------------------------------
 */

// ✅ Start session (MUST be first)
session_start();

// ✅ Login status flag
$loggedin = false;

// ✅ CSRF Token (created once per session)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ SECURITY HEADERS (FIXES OWASP ZAP ALERTS)
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' https://cdn.ckeditor.com; " .
    "style-src 'self' 'unsafe-inline'; " .
    "img-src 'self' data:;"
);
header("X-Frame-Options: SAMEORIGIN");                 // Clickjacking protection
header("X-Content-Type-Options: nosniff");              // MIME sniffing protection
header("Referrer-Policy: no-referrer-when-downgrade");  // Referrer control
header("X-XSS-Protection: 1; mode=block");              // Legacy XSS protection

// ✅ Check if user is logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $loggedin = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Site Icon -->
    <link rel="icon" href="img/logo/flogo.png" sizes="32x32" type="image/png">

    <!-- CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.css">