<?php
// MUST be called before session_start()
require_once 'includes/session_config.php';
session_start();

require_once 'vendor/autoload.php';

// Google API configuration
define('GOOGLE_CLIENT_ID', '915631311746-u10nasn59smdjn3ofle2a186vobmgll7.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URL', 'https://atimah-leather.dev.flatlogic.app/google_callback.php');

// Check if the user has a temporary identifier from the initial login, and clear it.
if (isset($_SESSION['otp_identifier'])) {
    unset($_SESSION['otp_identifier']);
}

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URL);
$client->addScope("email");
$client->addScope("profile");

// Handle the OAuth 2.0 server response
if (isset($_GET['code'])) {
    try {
        error_log('Google callback received: ' . print_r($_GET, true));
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        error_log('Google token response: ' . print_r($token, true));

        if (isset($token['error'])) {
            throw new Exception('Token error: ' . ($token['error_description'] ?? 'Unknown error'));
        }
        
        $client->setAccessToken($token['access_token']);

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $userInfo = [
            'email' => $google_account_info->email,
            'name' => $google_account_info->name,
        ];
        
        $_SESSION['google_user_info'] = $userInfo;

        // Explicitly save the session data before redirecting.
        session_write_close();
        
        header('Location: auth_handler.php?action=google_callback');
        exit();

    } catch (Throwable $t) {
        // Log the actual error to the server's error log for inspection.
        error_log('Google Auth Exception: ' . $t->getMessage());
        
        header('Location: login.php?error=google_auth_failed_exception');
        exit();
    }
} else {
    // It's the initial request, redirect to Google's OAuth 2.0 server
    header('Location: ' . $client->createAuthUrl());
    exit();
}
