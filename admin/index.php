<?php
session_start();
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../db/config.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}

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
    <title>پنل مدیریت - محصولات</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-dark text-white">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="font-lalezar">مدیریت محصولات</h1>
        <div class="d-flex gap-2">
            <a href="add_product.php" class="btn btn-success">+ افزودن محصول جدید</a>
            <a href="logout.php" class="btn btn-outline-danger">خروج</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">نام محصول</th>
                    <th scope="col">قیمت</th>
                    <th scope="col">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="4" class="text-center">هیچ محصولی یافت نشد.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($product['id']); ?></th>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['price']); ?> تومان</td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">ویرایش</a>
                                <a href="handler.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger delete-btn">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        <a href="../index.php" class="btn btn-outline-light">بازگشت به سایت</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Flash message handling
        <?php if ($flash_message): ?>
        Swal.fire({
            title: '<?php echo $flash_message["type"] === "success" ? "عالی" : "خطا"; ?>',
            html: '<?php echo addslashes($flash_message["message"]); ?>',
            icon: '<?php echo $flash_message["type"]; ?>',
            confirmButtonText: 'باشه'
        });
        <?php endif; ?>

        // Delete confirmation
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: "این عمل غیرقابل بازگشت است!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'بله، حذف کن!',
                    cancelButtonText: 'انصراف'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });
    });
</script>
</body>
</html>