<?php
session_start();
require_once __DIR__ . '/auth_check.php';

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن محصول جدید</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-dark text-white">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                 <h1 class="font-lalezar">افزودن محصول جدید</h1>
                 <a href="index.php" class="btn btn-outline-light">بازگشت</a>
            </div>
            <div class="card bg-dark-2">
                <div class="card-body p-4">
                    <form action="handler.php?action=add" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">نام محصول</label>
                            <input type="text" class="form-control bg-dark text-white" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">توضیحات</label>
                            <textarea class="form-control bg-dark text-white" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">قیمت (تومان)</label>
                            <input type="number" class="form-control bg-dark text-white" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">تصویر محصول</label>
                            <input type="file" class="form-control bg-dark text-white" id="image" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="colors" class="form-label">کدهای رنگ (اختیاری)</label>
                            <input type="text" class="form-control bg-dark text-white" id="colors" name="colors" placeholder="مثال: #8B4513, #2C2C2C">
                            <div class="form-text">کدهای رنگ هگزادسیمال را با کاما جدا کنید.</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1">
                            <label class="form-check-label" for="is_featured">محصول ویژه</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">افزودن محصول</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($flash_message): ?>
    Swal.fire({
        title: '<?php echo $flash_message["type"] === "success" ? "عالی" : "خطا"; ?>',
        html: '<?php echo addslashes($flash_message["message"]); ?>', // Use html to render <br> tags
        icon: '<?php echo $flash_message["type"]; ?>',
        confirmButtonText: 'باشه'
    });
    <?php endif; ?>
});
</script>
</body>
</html>