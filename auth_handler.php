<?php
require_once 'includes/session_config.php';
session_start();
require_once 'db/config.php';
require_once 'mail/MailService.php';

// Main router for authentication actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'send_otp':
        handle_send_otp();
        break;
    case 'verify_otp':
        handle_verify_otp();
        break;
    case 'resend_otp':
        handle_resend_otp();
        break;
    case 'google_callback':
        handle_google_callback();
        break;
    default:
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'درخواست نامعتبر است.'];
        header('Location: login.php');
        exit;
}

function handle_resend_otp() {
    header('Content-Type: application/json');
    $pdo = db();

    if (!isset($_SESSION['otp_identifier'])) {
        echo json_encode(['success' => false, 'message' => 'جلسه شما یافت نشد. لطفا دوباره تلاش کنید.']);
        exit;
    }

    $identifier = $_SESSION['otp_identifier'];
    $login_method = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    // Generate a new, cryptographically secure 6-digit OTP for resend
    $otp = random_int(100000, 999999);
    $expires = date('Y-m-d H:i:s', time() + (10 * 60)); // 10 minutes expiry

    try {
        // A new OTP is inserted. The verification logic automatically picks the latest valid one.
        $stmt = $pdo->prepare("INSERT INTO otp_codes (identifier, code, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$identifier, $otp, $expires]);

        // FOR TESTING: Always show the OTP for debugging purposes
        $_SESSION['show_otp_for_debugging'] = $otp;

        echo json_encode(['success' => true, 'otp' => $otp, 'message' => 'کد جدید با موفقیت ارسال شد.']);
        exit;

    } catch (Throwable $t) {
        error_log("OTP Resend Error: " . $t->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطایی در سیستم هنگام ارسال مجدد کد رخ داد.']);
        exit;
    }
}

function handle_send_otp() {
    $pdo = db();
    $identifier = '';
    $login_method = '';

    // Simplified and corrected logic
    if (isset($_POST['email'])) {
        // Trim whitespace from the email input
        $identifier = trim($_POST['email']);
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $login_method = 'email';
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'لطفا یک ایمیل معتبر وارد کنید.'];
            header('Location: login.php');
            exit;
        }
    } elseif (isset($_POST['phone'])) {
        // Trim whitespace from the phone input
        $identifier = trim($_POST['phone']);
        if (preg_match('/^09[0-9]{9}$/', $identifier)) {
            $login_method = 'phone';
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'لطفا یک شماره تلفن معتبر (مانند 09123456789) وارد کنید.'];
            header('Location: login.php');
            exit;
        }
    } else {
        // Neither email nor phone was submitted
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ایمیل یا شماره تلفن ارسال نشده است.'];
        header('Location: login.php');
        exit;
    }
    // Generate a cryptographically secure 6-digit OTP
    $otp = random_int(100000, 999999);
    $expires = date('Y-m-d H:i:s', time() + (10 * 60)); // 10 minutes expiry

    try {
        $stmt = $pdo->prepare("INSERT INTO otp_codes (identifier, code, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$identifier, $otp, $expires]);

        $_SESSION['otp_identifier'] = $identifier;
        
        // FOR TESTING: Always show the OTP for debugging purposes for both email and phone
        $_SESSION['show_otp_for_debugging'] = $otp;


        header('Location: verify.php');
        exit;

    } catch (Throwable $t) {
        error_log("OTP Generation Error: " . $t->getMessage());
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'خطایی در سیستم رخ داد. لطفا دوباره تلاش کنید.'];
        header('Location: login.php');
        exit;
    }
}

function handle_verify_otp() {
    if (empty($_POST['otp_code']) || empty($_SESSION['otp_identifier'])) {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'جلسه شما منقضی شده است. لطفا دوباره تلاش کنید.'];
        header('Location: login.php');
        exit;
    }

    $pdo = db();
    $identifier = $_SESSION['otp_identifier'];
    $otp_code = $_POST['otp_code'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE identifier = ? AND code = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$identifier, $otp_code]);
        $otp_entry = $stmt->fetch();

        if ($otp_entry) {
            // OTP is correct, clean up and log the user in
            $delete_stmt = $pdo->prepare("DELETE FROM otp_codes WHERE identifier = ?");
            $delete_stmt->execute([$identifier]);
            unset($_SESSION['otp_identifier']);
            unset($_SESSION['show_otp_for_debugging']);

            // Determine if login was via email or phone
            $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            $column = $is_email ? 'email' : 'phone';

            $user_stmt = $pdo->prepare("SELECT * FROM users WHERE $column = ?");
            $user_stmt->execute([$identifier]);
            $user = $user_stmt->fetch();

            if ($user) {
                // User exists, log them in
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                $_SESSION['is_admin'] = $user['is_admin'];
            } else {
                // User does not exist, create a new one
                $insert_column = $is_email ? 'email' : 'phone';
                $insert_stmt = $pdo->prepare("INSERT INTO users ($insert_column, created_at) VALUES (?, NOW())");
                $insert_stmt->execute([$identifier]);
                $newUserId = $pdo->lastInsertId();

                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $identifier; // Placeholder name
                $_SESSION['is_admin'] = 0;
            }
            
            header('Location: profile.php');
            exit;

        } else {
            // Invalid or expired OTP
            $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'کد تایید نامعتبر یا منقضی شده است.'];
            header('Location: verify.php');
            exit;
        }

    } catch (Throwable $t) {
        // Reverted to production error handling
        error_log("OTP Verification Error: " . $t->getMessage());
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'خطایی در پایگاه داده رخ داد. لطفا دوباره تلاش کنید.'];
        header('Location: verify.php');
        exit;
    }
}

function handle_google_callback() {
    if (!isset($_SESSION['google_user_info'])) {
        header('Location: login.php?error=google_auth_failed');
        exit();
    }

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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
            $_SESSION['is_admin'] = $user['is_admin'];
        } else {
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
        error_log('Database error during Google auth processing: ' . $t->getMessage());
        header('Location: login.php?error=db_error');
        exit();
    }
}
?>