<?php
session_start();
require_once 'db/config.php';

// If cart is empty, redirect to shop page, there is nothing to checkout
if (empty($_SESSION['cart'])) {
    header('Location: shop.php');
    exit;
}

$p_title = "تسویه حساب";
$order_placed_successfully = false;
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Data Validation ---
    $name = trim($_POST['customer_name'] ?? '');
    $email = trim($_POST['customer_email'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');

    if (empty($name) || empty($email) || empty($address)) {
        $error_message = 'لطفاً تمام فیلدها را پر کنید.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'لطفاً یک آدرس ایمیل معتبر وارد کنید.';
    }
    
    if(empty($error_message)) {
        $pdo = db();
        try {
            // --- Server-side recalculation of total ---
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);

            $total_amount = 0;
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                if(isset($products_from_db[$product_id])){
                    $total_amount += $products_from_db[$product_id]['price'] * $quantity;
                }
            }
            
            // --- Database Transaction ---
            $pdo->beginTransaction();

            // 1. Insert into orders table
            $sql_order = "INSERT INTO orders (customer_name, customer_email, customer_address, total_amount) VALUES (?, ?, ?, ?)";
            $stmt_order = $pdo->prepare($sql_order);
            $stmt_order->execute([$name, $email, $address, $total_amount]);
            $order_id = $pdo->lastInsertId();

            // 2. Insert into order_items table
            $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_items = $pdo->prepare($sql_items);
            
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                if(isset($products_from_db[$product_id])){
                     $price = $products_from_db[$product_id]['price'];
                     $stmt_items->execute([$order_id, $product_id, $quantity, $price]);
                }
            }
            
            // 3. Commit the transaction
            $pdo->commit();

            // 4. Clear the cart and set success flag
            unset($_SESSION['cart']);
            $order_placed_successfully = true;
            $p_title = "سفارش شما ثبت شد";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Checkout Error: " . $e->getMessage());
            $error_message = 'مشکلی در ثبت سفارش شما به وجود آمد. لطفاً دوباره تلاش کنید.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $p_title; ?> - چرم آتیمه</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500&family=Lalezar&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/custom.css?v=<?php echo time(); ?>">
</head>
<body class="bg-dark text-white">

    <!-- Header -->
    <header class="p-3 mb-3 border-bottom border-secondary">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="index.php" class="d-flex align-items-center mb-2 mb-lg-0 text-white text-decoration-none">
                     <h1 class="font-playfair fs-2" style="color: #D4AF37;">آتیمه</h1>
                </a>
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="index.php" class="nav-link px-2 text-white">خانه</a></li>
                    <li><a href="shop.php" class="nav-link px-2 text-white">فروشگاه</a></li>
                </ul>
                <div class="text-end">
                     <a href="cart.php" class="btn btn-outline-warning position-relative">
                        سبد خرید
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                           <?php echo count($_SESSION['cart'] ?? []); ?>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container my-5">
        <div class="text-center mb-5">
            <h2 class="font-lalezar display-4"><?php echo $p_title; ?></h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if ($order_placed_successfully): ?>
                    <div class="alert alert-success text-center">
                        <h4>از خرید شما متشکریم!</h4>
                        <p>سفارش شما با موفقیت ثبت شد و به زودی پردازش خواهد شد. یک ایمیل تایید برای شما ارسال گردید.</p>
                        <a href="shop.php" class="btn btn-warning">بازگشت به فروشگاه</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">.<?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <div class="card bg-dark-2">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">اطلاعات ارسال</h5>
                            <form action="checkout.php" method="POST">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label">نام و نام خانوادگی</label>
                                    <input type="text" class="form-control bg-dark text-white" id="customer_name" name="customer_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">آدرس ایمیل</label>
                                    <input type="email" class="form-control bg-dark text-white" id="customer_email" name="customer_email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">آدرس کامل</label>
                                    <textarea class="form-control bg-dark text-white" id="customer_address" name="customer_address" rows="3" required></textarea>
                                </div>
                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-warning btn-lg fw-bold">ثبت سفارش و پرداخت</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-5 mt-5 border-top border-secondary">
        <div class="container text-center">
            <p class="text-muted">&copy; <?php echo date("Y"); ?> چرم آتیمه. تمام حقوق محفوظ است.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>