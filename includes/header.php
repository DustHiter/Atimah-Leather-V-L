<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cart_item_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
$page_title = $page_title ?? 'فروشگاه آتیمه'; // Default title
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($_SERVER['PROJECT_DESCRIPTION'] ?? 'خرید محصولات چرمی لوکس و با کیفیت.'); ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css?v=<?php echo time(); ?>">

    <script>
        // Apply theme from local storage before page load to prevent flashing
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

</head>
<body>
    <div class="overflow-hidden">
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_admin_page = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    ?>

<header class="site-header sticky-top py-3">
    <nav class="navbar navbar-expand-lg container">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="index.php">آتیمه</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">خانه</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">فروشگاه</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">درباره ما</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">تماس با ما</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <button id="theme-toggle" class="btn me-3">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>
                    <a href="cart.php" class="ms-4 position-relative">
                        <i class="bi bi-bag fs-5"></i>
                        <?php if ($cart_item_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cart_item_count; ?>
                                <span class="visually-hidden">محصول در سبد خرید</span>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="/admin/login.php" class="ms-3">
                        <i class="bi bi-person fs-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>