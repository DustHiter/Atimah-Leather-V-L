<?php
// Force session cookie parameters for cross-domain compatibility.
session_set_cookie_params([
    'lifetime' => 86400, // 24 hours
    'path' => '/',
    'domain' => '', // Set your domain if needed, empty for current host
    'secure' => true, // Must be true for SameSite=None
    'httponly' => true,
    'samesite' => 'None' // Allows cross-site cookie sending
]);
?>