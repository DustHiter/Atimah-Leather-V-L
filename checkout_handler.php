<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();


require_once 'db/config.php';

// 1. Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit();
}

// 2. Check if cart is empty
if (empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = 'سبد خرید شما خالی است.';
    header('Location: cart.php');
    exit();
}

// 3. Collect and trim form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$address_line = trim($_POST['address_line'] ?? '');
$city = trim($_POST['city'] ?? '');
$province = trim($_POST['province'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');

// 4. Basic Validation
$errors = [];
if (empty($first_name)) $errors[] = 'فیلد نام الزامی است.';
if (empty($last_name)) $errors[] = 'فیلد نام خانوادگی الزامی است.';
if (empty($phone_number)) $errors[] = 'فیلد تلفن همراه الزامی است.';
if (empty($address_line)) $errors[] = 'فیلد آدرس الزامی است.';
if (empty($city)) $errors[] = 'فیلد شهر الزامی است.';
if (empty($province)) $errors[] = 'فیلد استان الزامی است.';
if (empty($postal_code)) $errors[] = 'فیلد کد پستی الزامی است.';

if (!empty($errors)) {
    $_SESSION['checkout_errors'] = $errors;
    // Store submitted data to re-populate the form
    $_SESSION['form_data'] = $_POST;
    header('Location: checkout.php');
    exit();
}

// == Server-Side Calculation ==
$cart = $_SESSION['cart'];
$product_ids = array_keys($cart);

$items_for_json = [];
$total_price = 0;

if (!empty($product_ids)) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = db()->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create a map for quick lookup
    $products_by_id = [];
    foreach($products_data as $product) {
        $products_by_id[$product['id']] = $product;
    }

    foreach ($cart as $product_id => $details) {
        if (isset($products_by_id[$product_id])) {
            $product = $products_by_id[$product_id];
            $price = $product['price'];
            $quantity = $details['quantity'];
            $total_price += $price * $quantity;

            $items_for_json[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $price,
                'quantity' => $quantity,
                'color' => $details['color']
            ];
        }
    }
}

$shipping_cost = 50000;
$grand_total = $total_price + $shipping_cost;

// == Database Operations ==
$pdo = db();
try {
    $pdo->beginTransaction();

    // 5. User Handling (Guest or Logged in)
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        // For guests, check if user exists by phone
        $user_stmt = $pdo->prepare("SELECT id FROM users WHERE phone_number = ?");
        $user_stmt->execute([$phone_number]);
        $existing_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_user) {
            $user_id = $existing_user['id'];
        } else {
            // Create a new user
            $user_insert_stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone_number, is_admin) VALUES (?, ?, ?, ?, 0)");
            $user_insert_stmt->execute([$first_name, $last_name, $email, $phone_number]);
            $user_id = $pdo->lastInsertId();
        }
        // Log the new/guest user in
        $_SESSION['user_id'] = $user_id;
    }

    // 6. Generate a unique tracking ID
    $tracking_id = 'FL-' . strtoupper(bin2hex(random_bytes(5)));

    // 7. Insert the order into the database
    $order_stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, billing_name, billing_email, billing_phone, billing_province, billing_city, billing_address, billing_postal_code, total_amount, items_json, status, tracking_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)"
    );

    $full_name = $first_name . ' ' . $last_name;
    $items_json_encoded = json_encode($items_for_json, JSON_UNESCAPED_UNICODE);

    $order_stmt->execute([
        $user_id,
        $full_name,
        $email,
        $phone_number,
        $province,
        $city,
        $address_line,
        $postal_code,
        $grand_total, // Storing the final amount including shipping
        $items_json_encoded,
        $tracking_id
    ]);

    $pdo->commit();

    // 8. Clear cart and redirect to a success page
    unset($_SESSION['cart']);
    unset($_SESSION['form_data']);

    header('Location: track_order.php?tracking_id=' . $tracking_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    // Log the detailed error for developers
    error_log('Checkout Error: ' . $e->getMessage());

    // Set a user-friendly error message and redirect
    $_SESSION['checkout_errors'] = ['یک خطای غیرمنتظره در هنگام ثبت سفارش رخ داد. لطفاً لحظاتی دیگر دوباره تلاش کنید.'];
    $_SESSION['form_data'] = $_POST;
    header('Location: checkout.php');
    exit();
}