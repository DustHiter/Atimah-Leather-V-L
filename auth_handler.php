<?php
// MUST be called before session_start()
require_once 'includes/session_config.php';
session_start();

require_once 'db/config.php';

// If Google user info is not in the session, redirect to login.
if (!isset($_SESSION['google_user_info'])) {
    header('Location: login.php?error=google_auth_failed');
    exit();
}

// Retrieve user info from session
$google_user = $_SESSION['google_user_info'];
$email = $google_user['email'];
$fullName = $google_user['name'];
$nameParts = explode(' ', $fullName, 2);
$firstName = $nameParts[0];
$lastName = isset($nameParts[1]) ? $nameParts[1] : '';

// Clear the temporary session data
unset($_SESSION['google_user_info']);

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists, log them in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
        $_SESSION['is_admin'] = $user['is_admin'];
    } else {
        // User does not exist, create a new one
        $insertStmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin, created_at) VALUES (?, ?, ?, NULL, 0, NOW())");
        $insertStmt->execute([$firstName, $lastName, $email]);
        $newUserId = $pdo->lastInsertId();

        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['is_admin'] = 0;
    }

    header('Location: profile.php');
    exit();

} catch (Throwable $t) {
    $error_message = 'Database error during Google auth processing: ' . $t->getMessage();
    error_log($error_message);
    header('Location: login.php?error=db_error');
    exit();
}
