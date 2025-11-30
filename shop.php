<?php
require_once 'db/config.php';

try {
    $pdo = db();
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    $products = [];
}

$page_title = 'فروشگاه';
include 'includes/header.php';
?>

<div class="text-center mb-5">
    <h1 class="display-4 fw-bold">گالری محصولات</h1>
    <p class="lead text-muted">دست‌سازه‌هایی از چرم طبیعی، با عشق و دقت</p>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="product-card h-100">
                    <div class="product-image">
                        <a href="product.php?id=<?php echo $product['id']; ?>">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                    </div>
                    <div class="product-info text-center">
                        <h3 class="product-title"><a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                        <p class="product-price"><?php echo number_format($product['price']); ?> تومان</p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <p class="text-center p-5 bg-light rounded-3">محصولی برای نمایش یافت نشد.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
