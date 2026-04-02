<?php
require_once 'config.php';
require_once 'assest/head.php'; // head.php already runs session_start() and db connection

// 1. Configuration
$client_id     = GOOGLE_CLIENT_ID;
$client_secret = GOOGLE_CLIENT_SECRET;
$redirect_uri  = GOOGLE_REDIRECT_URL;

if (isset($_GET['code'])) {
    // 2. Exchange the Code for an Access Token
    $post_params = [
        'code'          => $_GET['code'],
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code',
    ];

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));
    
    // --- CRITICAL XAMPP FIX ---
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    // --------------------------

    $response = curl_exec($ch);
    $data = json_decode($response, true);

    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];

        // 3. Get User Profile Info using the token
        $userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . $access_token;
        
        // Using CURL for UserInfo as well for SSL consistency
        $ch_u = curl_init($userinfo_url);
        curl_setopt($ch_u, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_u, CURLOPT_SSL_VERIFYPEER, false);
        $user_response = curl_exec($ch_u);
        $user_data = json_decode($user_response, true);

        $email = $user_data['email'];
        $name  = $user_data['name'];

        // 4. Database Logic
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $conn->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
            $stmt->execute([$name, $email]);
        }

        // 5. Log them in
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $name;
        $_SESSION['email']    = $email;

        // Security best practice B04
        session_regenerate_id(true);

        header('Location: index.php');
        exit;
    }
}

// If it reached here, something failed
die(print_r($data));
exit;