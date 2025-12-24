<?php
session_start(); // Ensure session is started
$page_title = 'تماس با ما';
require_once 'includes/header.php';
require_once 'mail/MailService.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_form'])) {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['flash_message'] = ['type' => 'warning', 'message' => 'لطفاً تمام فیلدها را پر کنید.'];
    } elseif (!$email) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'آدرس ایمیل وارد شده معتبر نیست.'];
    } else {
        $to_email = getenv('MAIL_TO') ?: 'support@atimeh.com'; // Fallback email
        $subject = "پیام جدید از فرم تماس وب‌سایت";
        $email_result = MailService::sendContactMessage($name, $email, $message, $to_email, $subject);

        if (!empty($email_result['success'])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'پیام شما با موفقیت ارسال شد. سپاسگزاریم!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'خطا در ارسال پیام. لطفاً بعداً دوباره تلاش کنید.'];
            error_log("MailService Error: " . ($email_result['error'] ?? 'Unknown error'));
        }
    }
    
    // Redirect to the same page to prevent form resubmission
    header("Location: contact.php");
    exit();
}

// Check for flash message
$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    // Clear the message from session so it doesn't show again
    unset($_SESSION['flash_message']);
}
?>

<div class="container py-5 my-5">
    <div class="section-title text-center mb-5" data-aos="fade-down">
        <h1>ارتباط با ما</h1>
        <p class="fs-5 text-muted">نظرات، پیشنهادات و سوالات شما برای ما ارزشمند است.</p>
    </div>

    <div class="contact-card p-4 p-lg-5" data-aos="fade-up">
        <div class="row g-5">
            <div class="col-lg-5">
                <div class="contact-info h-100 d-flex flex-column justify-content-center">
                    <h3 class="mb-4">راه‌های ارتباطی</h3>
                    <div class="d-flex align-items-start mb-4">
                        <i class="ri-map-pin-line mt-1 me-3"></i>
                        <div>
                            <strong>آدرس:</strong>
                            <p class="text-muted mb-0">تهران، خیابان هنر، کوچه خلاقیت، پلاک ۱۲</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <i class="ri-mail-line mt-1 me-3"></i>
                        <div>
                            <strong>ایمیل:</strong>
                            <p class="mb-0"><a href="mailto:info@atimeh.com">info@atimeh.com</a></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-4">
                        <i class="ri-phone-line mt-1 me-3"></i>
                        <div>
                            <strong>تلفن:</strong>
                            <p class="mb-0"><a href="tel:+982112345678">۰۲۱-۱۲۳۴۵۶۷۸</a></p>
                        </div>
                    </div>
                    <hr class="my-4" style="border-color: var(--luxury-border);">
                    <h4 class="h5 mb-3">ما را دنبال کنید</h4>
                    <div class="d-flex gap-2">
                        <a href="#" class="social-btn"><i class="ri-instagram-line"></i></a>
                        <a href="#" class="social-btn"><i class="ri-telegram-line"></i></a>
                        <a href="#" class="social-btn"><i class="ri-whatsapp-line"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <h3 class="mb-4">فرم تماس</h3>
                <form action="contact.php" method="POST">
                    <input type="hidden" name="contact_form" value="1">
                    <div class="mb-4">
                        <label for="name" class="form-label">نام شما</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="form-label">ایمیل</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-4">
                        <label for="message" class="form-label">پیام شما</label>
                        <textarea class="form-control" id="message" name="message" rows="7" required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">ارسال پیام</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert for flash messages -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($flash_message): ?>
    Swal.fire({
        title: '<?php echo addslashes($flash_message["message"]); ?>',
        icon: '<?php echo $flash_message["type"]; ?>',
        toast: true,
        position: 'top-start',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        showCloseButton: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
        customClass: {
            popup: 'dark-theme-toast'
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>