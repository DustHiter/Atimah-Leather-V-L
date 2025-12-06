<?php
session_start();

// Redirect if email is not in session (user hasn't come from login page)
if (!isset($_SESSION['otp_email'])) {
    header('Location: login.php');
    exit;
}

$email_for_display = htmlspecialchars($_SESSION['otp_email']);
$page_title = "تایید کد یکبار مصرف";
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?> - آتیمه</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Remixicon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- Main Custom CSS (for variables) -->
    <link rel="stylesheet" href="assets/css/dark_luxury.css?v=<?= time(); ?>">
</head>
<body class="dark-luxury">

    <div class="auth-wrapper">
        <div class="auth-bg">
            <div class="auth-bg-content">
                <h1>فقط یک قدم دیگر...</h1>
                <p>کد تاییدی که به ایمیل شما ارسال شده را وارد کنید تا وارد دنیای شگفت‌انگیز چرم شوید.</p>
            </div>
        </div>

        <div class="auth-form-wrapper">
            <div class="auth-form-container">
                <div class="form-header">
                     <div class="logo">آتیمه</div>
                    <h2>تایید کد</h2>
                    <p>کد ۶ رقمی ارسال شده به <strong class="d-block mt-2"><?= $email_for_display; ?></strong> را وارد کنید.</p>
                </div>

                <?php if(isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']); ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <form action="auth_handler.php?action=verify_otp" method="POST">
                    <input type="hidden" name="email" value="<?= $email_for_display; ?>">
                    <div class="form-group">
                         <label for="otp_code" class="form-label visually-hidden">کد تایید</label>
                        <input type="text" class="form-control otp-input" id="otp_code" name="otp_code" placeholder="- - - - - -" required pattern="\d{6}" maxlength="6" autocomplete="one-time-code">
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">تایید و ورود</button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p><a href="login.php"><i class="ri-arrow-right-line"></i> بازگشت و اصلاح ایمیل</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
