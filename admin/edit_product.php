<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

$flash_message = $_SESSION['flash_message'] ?? null;
if ($flash_message) {
    unset($_SESSION['flash_message']);
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching product: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش محصول: <?php echo htmlspecialchars($product['name']); ?></title>
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
            <h1 class="font-lalezar mb-4">ویرایش محصول</h1>
            <div class="card bg-dark-2">
                <div class="card-body p-4">
                    <form action="handler.php?action=edit" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">نام محصول</label>
                            <input type="text" class="form-control bg-dark text-white" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">توضیحات</label>
                            <textarea class="form-control bg-dark text-white" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">قیمت (به تومان)</label>
                            <input type="number" class="form-control bg-dark text-white" id="price" name="price" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="colors" class="form-label">رنگ‌ها</label>
                            <input type="text" class="form-control bg-dark text-white" id="colors" name="colors" value="<?php echo htmlspecialchars($product['colors'] ?? ''); ?>">
                            <div class="form-text">رنگ‌های موجود را با کاما از هم جدا کنید (مثال: #FFFFFF, #000000).</div>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">تصویر محصول</label>
                            <input type="file" class="form-control bg-dark text-white" id="image" name="image" accept="image/*">
                            <div class="form-text mt-2">تصویر فعلی:</div>
                            <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current Image" class="img-thumbnail mt-2" width="100">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" <?php echo ($product['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">نمایش در محصولات ویژه</label>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="index.php" class="btn btn-secondary">انصراف</a>
                            <button type="submit" class="btn btn-primary">به‌روزرسانی محصول</button>
                        </div>
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
