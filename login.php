<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php'); // Redirect to profile page if logged in
    exit;
}

$page_title = "ورود یا ثبت‌نام";
$page_description = "به آتیمه، خانه چرم و اصالت خوش آمدید. وارد حساب کاربری خود شوید یا یک حساب جدید بسازید تا از تجربه خرید لذت ببرید.";
$page_keywords = "ورود, ثبت نام, چرم, آتیمه, حساب کاربری";

// Using a specific body class for targeted styling
$body_class = "login-page-modern";
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title); ?> - آتیمه</title>
    <meta name="description" content="<?= htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page_keywords); ?>">
    
    <!-- SEO Meta Tags -->
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://yourdomain.com/login.php" /> <!-- Replace with your actual domain -->

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Remixicon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/theme.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="assets/css/custom.css?v=<?= time(); ?>">
</head>
<body class="<?= $body_class; ?>">

    <main class="login-container">
        <div class="login-form-wrapper">
            <div class="login-header text-center mb-4">
                <a href="index.php" class="logo-link">
                    <h1 class="logo-title h2">آتیمه</h1>
                </a>
                <p class="tagline">اصالت و زیبایی در دستان شما</p>
            </div>
            
            <h2 class="form-title text-center mb-4">ورود یا ثبت نام</h2>

            <?php if(isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_message']['type']); ?> alert-dismissible fade show my-3" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_message']['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <form action="auth_handler.php?action=send_otp" method="POST" class="needs-validation" novalidate>
                <div class="login-toggle-container mb-4">
                    <div class="btn-group w-100" role="group" aria-label="Login method toggle">
                        <input type="radio" class="btn-check" name="login_method" id="email_toggle" value="email" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="email_toggle">ایمیل</label>

                        <input type="radio" class="btn-check" name="login_method" id="phone_toggle" value="phone" autocomplete="off">
                        <label class="btn btn-outline-primary" for="phone_toggle">تلفن همراه</label>
                    </div>
                </div>

                <!-- Email Input -->
                <div id="email_input_group">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email_input" name="email" placeholder="ایمیل خود را وارد کنید" required>
                        <label for="email_input">ایمیل</label>
                        <div class="invalid-feedback">
                            لطفا یک ایمیل معتبر وارد کنید.
                        </div>
                    </div>
                </div>

                <!-- Phone Input (hidden by default) -->
                <div id="phone_input_group" style="display: none;">
                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control" id="phone_input" name="phone" placeholder="09123456789" pattern="09[0-9]{9}" required>
                        <label for="phone_input">تلفن همراه</label>
                        <div class="invalid-feedback">
                            لطفا یک شماره تلفن معتبر (مانند 09123456789) وارد کنید.
                        </div>
                    </div>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">ادامه</button>
                </div>

                            <div class="or-separator text-center my-3">
                                <span class="text-muted">یا</span>
                            </div>

                            <a href="google_callback.php" class="btn btn-light border w-100 d-flex align-items-center justify-content-center py-2 shadow-sm">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google icon" style="width: 20px; height: 20px;" class="me-2">
                                <span class="fw-bold text-secondary">ورود با گوگل</span>
                            </a>
            </form>

            <div class="auth-footer text-center mt-4">
                <p><a href="index.php"><i class="ri-arrow-right-line align-middle"></i> بازگشت به فروشگاه</a></p>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Standard Bootstrap validation script
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
        })();

        // New, simplified toggle logic
        document.addEventListener('DOMContentLoaded', function() {
            const emailToggle = document.getElementById('email_toggle');
            const phoneToggle = document.getElementById('phone_toggle');
            
            const emailGroup = document.getElementById('email_input_group');
            const phoneGroup = document.getElementById('phone_input_group');

            const emailInput = document.getElementById('email_input');
            const phoneInput = document.getElementById('phone_input');

            function toggleInputs(showEmail) {
                if (showEmail) {
                    emailGroup.style.display = 'block';
                    emailInput.disabled = false;
                    phoneGroup.style.display = 'none';
                    phoneInput.disabled = true;
                } else {
                    emailGroup.style.display = 'none';
                    emailInput.disabled = true;
                    phoneGroup.style.display = 'block';
                    phoneInput.disabled = false;
                }
            }

            emailToggle.addEventListener('change', function() {
                if (this.checked) {
                    toggleInputs(true);
                }
            });

            phoneToggle.addEventListener('change', function() {
                if (this.checked) {
                    toggleInputs(false);
                }
            });

            // Initialize on page load
            toggleInputs(emailToggle.checked);
        });
    </script>
</body>
</html>