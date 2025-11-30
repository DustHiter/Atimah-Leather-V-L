<?php
session_start();
require_once 'db/config.php';

$cart_items_detailed = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $cart_item_ids = array_keys($_SESSION['cart']);
    
    // Extract pure product IDs from the composite key (e.g., '1-Black' -> '1')
    $product_ids = array_map(function($id) {
        return (int)explode('-', $id)[0];
    }, $cart_item_ids);

    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
            $stmt->execute(array_unique($product_ids));
            $products_data = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);

            foreach ($_SESSION['cart'] as $cart_item_id => $item) {
                $product_id = (int)explode('-', $cart_item_id)[0];
                
                if (isset($products_data[$product_id])) {
                    $product = $products_data[$product_id];
                    $quantity = $item['quantity'];
                    $color = $item['color'];
                    $subtotal = $product['price'] * $quantity;
                    $total_price += $subtotal;

                    $cart_items_detailed[] = [
                        'cart_item_id' => $cart_item_id,
                        'product_id' => $product_id,
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image_url' => $product['image_url'],
                        'quantity' => $quantity,
                        'color' => $color,
                        'subtotal' => $subtotal
                    ];
                }
            }
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            $cart_items_detailed = [];
            $total_price = 0;
        }
    }
}

$page_title = 'سبد خرید';
include 'includes/header.php';
?>

<div class="text-center mb-5">
    <h1 class="display-4 fw-bold">سبد خرید شما</h1>
</div>

<?php if (empty($cart_items_detailed)): ?>
    <div class="text-center p-5 bg-light rounded-3">
        <p class="lead">سبد خرید شما خالی است.</p>
        <a href="shop.php" class="btn btn-primary">بازگشت به فروشگاه</a>
    </div>
<?php else: ?>
    <form action="cart_handler.php" method="POST">
        <input type="hidden" name="update_cart" value="1">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" colspan="2">محصول</th>
                        <th scope="col" class="text-center">قیمت</th>
                        <th scope="col" class="text-center">تعداد</th>
                        <th scope="col" class="text-end">جمع کل</th>
                        <th scope="col" class="text-center">حذف</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items_detailed as $item): ?>
                        <tr>
                            <td style="width: 100px;">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded-3">
                            </td>
                            <td>
                                <h5 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <?php if ($item['color']): ?>
                                    <small class="text-muted">رنگ: <?php echo htmlspecialchars($item['color']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><strong><?php echo number_format($item['price']); ?></strong></td>
                            <td class="text-center" style="width: 120px;">
                                <input type="number" class="form-control text-center" name="quantities[<?php echo $item['cart_item_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="10">
                            </td>
                            <td class="text-end"><strong><?php echo number_format($item['subtotal']); ?></strong></td>
                            <td class="text-center">
                                <a href="cart_handler.php?action=remove&id=<?php echo $item['cart_item_id']; ?>" class="btn btn-sm btn-outline-danger">&times;</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
            <button type="submit" class="btn btn-outline-secondary">به‌روزرسانی سبد</button>
            <div class="text-end">
                <h4>جمع نهایی: <span class="fw-bold text-primary"><?php echo number_format($total_price); ?> تومان</span></h4>
            </div>
        </div>
    </form>

    <div class="text-center mt-5">
        <a href="checkout.php" class="btn btn-primary btn-lg">ادامه جهت تسویه حساب</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
