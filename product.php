<?php
session_start();
require_once 'db/config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: shop.php");
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: shop.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}

$page_title = htmlspecialchars($product['name']);
$available_colors = !empty($product['colors']) ? array_map('trim', explode(',', $product['colors'])) : [];

include 'includes/header.php';
?>

        <div class="row g-5">
            <div class="col-lg-6">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid rounded-4 shadow-lg w-100" alt="<?php echo htmlspecialchars($product['name']); ?>" style="aspect-ratio: 1/1; object-fit: cover;">
            </div>
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="lead text-white-50 my-3"><?php echo htmlspecialchars($product['description']); ?></p>
                
                <div class="display-5 fw-bold my-4 text-gold"><?php echo number_format($product['price']); ?> <span class="fs-5 text-white-50">تومان</span></div>
                
                <form action="cart_handler.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                    <?php if (!empty($available_colors)): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold fs-5 mb-3">انتخاب رنگ:</label>
                        <div class="d-flex flex-wrap gap-3 color-swatches">
                            <?php foreach ($available_colors as $index => $color): ?>
                                <div data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($color); ?>">
                                    <input type="radio" class="btn-check" name="color" id="color-<?php echo $index; ?>" value="<?php echo htmlspecialchars($color); ?>" autocomplete="off" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                    <label class="btn" for="color-<?php echo $index; ?>"><?php echo htmlspecialchars($color); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                     <div class="d-flex align-items-center mb-4">
                        <label for="quantity" class="form-label ms-3 mb-0 fs-5">تعداد:</label>
                        <input type="number" name="quantity" id="quantity" class="form-control bg-dark text-white" value="1" min="1" max="10" style="width: 80px;">
                    </div>

                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg w-100 py-3 fw-bold">افزودن به سبد خرید</button>
                </form>

            </div>
        </div>

<?php include 'includes/footer.php'; ?>
