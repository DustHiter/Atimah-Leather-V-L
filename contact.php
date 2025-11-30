<?php
session_start();
require_once __DIR__ . '/mail/MailService.php';

$page_title = 'تماس با ما';
$page_description = 'با ما در تماس باشید. نظرات و پیشنهادات شما برای ما ارزشمند است.';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $body = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($body) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'لطفاً تمام فیلدها را به درستی پر کنید.';
    } else {
        $response = MailService::sendContactMessage($name, $email, $body, null, $subject);
        if (!empty($response['success'])) {
            $message = 'پیام شما با موفقیت ارسال شد. سپاسگزاریم!';
        } else {
            $error = 'خطایی در ارسال پیام رخ داد. لطفاً بعداً تلاش کنید. متن خطا: ' . htmlspecialchars($response['error'] ?? 'Unknown error');
        }
    }
}

include 'includes/header.php';
?>

<main class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center" data-aos="fade-up">
            <h1 class="display-4 fw-bold"><?php echo $page_title; ?></h1>
            <p class="lead text-white-50 mt-3"><?php echo $page_description; ?></p>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
            <div class="card border-0" style="background-color: var(--surface-color);">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST" data-aos="fade-up" data-aos-delay="200">
                        <div class="mb-4">
                            <label for="name" class="form-label fs-5">نام شما</label>
                            <input type="text" class="form-control form-control-lg bg-dark text-white" id="name" name="name" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fs-5">ایمیل شما</label>
                            <input type="email" class="form-control form-control-lg bg-dark text-white" id="email" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label for="subject" class="form-label fs-5">موضوع</label>
                            <input type="text" class="form-control form-control-lg bg-dark text-white" id="subject" name="subject" required>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="form-label fs-5">پیام شما</label>
                            <textarea class="form-control form-control-lg bg-dark text-white" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">ارسال پیام</button>
                        </div>
                    </form>
                </div>
            </div>
             <div class="alert alert-info mt-4"><b>توجه:</b> این فرم برای اهداف آزمایشی است. برای دریافت واقعی ایمیل‌ها، باید اطلاعات سرور ایمیل (SMTP) خود را در فایل <code>.env</code> وارد کنید.</div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>
