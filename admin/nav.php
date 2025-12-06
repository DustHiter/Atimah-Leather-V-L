<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2><a href="index.php">آتیمه<span>.</span></a></h2>
    </div>
    <nav>
        <ul class="admin-nav">
            <li class="admin-nav-item">
                <a class="admin-nav-link <?php echo ($current_page == 'index.php' || $current_page == 'dashboard.php') ? 'active' : ''; ?>" href="index.php">
                    <i class="ri-dashboard-line"></i>
                    <span>داشبورد</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a class="admin-nav-link <?php echo in_array($current_page, ['products.php', 'add_product.php', 'edit_product.php']) ? 'active' : ''; ?>" href="products.php">
                    <i class="ri-shopping-cart-2-line"></i>
                    <span>محصولات</span>
                </a>
            </li>
            <li class="admin-nav-item">
                <a class="admin-nav-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>" href="orders.php">
                    <i class="ri-file-list-3-line"></i>
                    <span>سفارشات</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank"><i class="ri-external-link-line"></i> مشاهده سایت</a>
        <hr style="border-color: var(--admin-border-light); margin: 1rem 0;">
        <a href="logout.php"><i class="ri-logout-box-line"></i> خروج</a>
    </div>
</aside>
