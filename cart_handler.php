<?php
session_start();

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if the form was submitted and it's an add-to-cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $color = isset($_POST['color']) ? trim($_POST['color']) : null;

    if ($product_id > 0 && $quantity > 0) {
        // Create a unique ID for the cart item based on product ID and color
        $cart_item_id = $product_id . ($color ? '-' . preg_replace('/[^a-zA-Z0-9_]/ ', '-', $color) : '');

        // If the exact item (product + color) is already in the cart, update the quantity
        if (isset($_SESSION['cart'][$cart_item_id])) {
            $_SESSION['cart'][$cart_item_id]['quantity'] += $quantity;
        } else {
            // Otherwise, add it as a new item
            $_SESSION['cart'][$cart_item_id] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'color' => $color
            ];
        }
    }
    
    // Redirect to the cart page to show the updated cart
    header('Location: cart.php');
    exit;
}

// Handle removing an item from the cart
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'remove') {
    $cart_item_id = isset($_GET['id']) ? $_GET['id'] : '';
    if (!empty($cart_item_id) && isset($_SESSION['cart'][$cart_item_id])) {
        unset($_SESSION['cart'][$cart_item_id]);
    }
    // Redirect back to the cart page
    header('Location: cart.php');
    exit;
}

// Handle updating quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])){
    if(!empty($_POST['quantities'])){
        foreach($_POST['quantities'] as $cart_item_id => $quantity){
            $quantity = (int)$quantity;
            if(!empty($cart_item_id) && isset($_SESSION['cart'][$cart_item_id])){
                if($quantity > 0){
                     $_SESSION['cart'][$cart_item_id]['quantity'] = $quantity;
                } else {
                    // Remove item if quantity is 0 or less
                    unset($_SESSION['cart'][$cart_item_id]);
                }
            }
        }
    }
    header('Location: cart.php');
    exit;
}


// If someone accesses this file directly without a valid action, redirect them to the shop.
header('Location: shop.php');
exit;