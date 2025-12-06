<?php
session_start();

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hardcoded_password = 'admin123';

    if (isset($_POST['password']) && $_POST['password'] === $hardcoded_password) {
        $_SESSION['is_admin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'رمز عبور وارد شده اشتباه است.';
    }
}
$page_title = 'ورود به پنل مدیریت';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title; ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="assets/css/admin_main.css?v=<?= time(); ?>">
</head>
<body class="admin-theme">

<div class="admin-login-wrapper">
    <div class="admin-login-box">
        <h2>پنل مدیریت آتیمه</h2>
        <p>برای دسترسی به پنل، لطفاً وارد شوید.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger mb-3"><?= $error; ?></div>
             <p class="text-center text-muted mb-4">رمز عبور پیش‌فرض: <code>admin123</code></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="password" class="form-label">رمز عبور</label>
                <input type="password" class="form-control" id="password" name="password" required autofocus>
            </div>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary w-100">ورود <i class="ri-arrow-left-line"></i></button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

<style>
.alert-danger {
    background-color: var(--admin-danger-bg, #fef2f2);
    border: 1px solid var(--admin-danger-border, #fecaca);
    color: var(--admin-danger-text, #991b1b);
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
}
.w-100 { width: 100%; }
</style>