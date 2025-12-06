<?php
header('Content-Type: application/json');
require_once '../db/config.php';
require_once '../includes/jdf.php';

// Function to translate order status
function get_persian_status($status) {
    switch ($status) {
        case 'pending': return 'در انتظار پرداخت';
        case 'processing': return 'در حال پردازش';
        case 'shipped': return 'ارسال شده';
        case 'completed': return 'تکمیل شده';
        case 'cancelled': return 'لغو شده';
        case 'refunded': return 'مسترد شده';
        default: return 'نامشخص';
    }
}

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Read JSON from the request body
        $json_data = file_get_contents('php://input');
        $data = json_decode($json_data, true);
        $tracking_id = $data['tracking_id'] ?? '';

        if (empty($tracking_id)) {
            throw new Exception('کد رهگیری سفارش الزامی است.');
        }

        $db = db();
        $stmt = $db->prepare(
            "SELECT 
                o.*,
                o.billing_name AS full_name
             FROM orders o
             WHERE o.tracking_id = :tracking_id"
        );
        $stmt->execute([':tracking_id' => $tracking_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $items_json = $order['items_json'];
            $items = json_decode($items_json, true);
            $products = [];

            if (is_array($items)) {
                $product_stmt = $db->prepare("SELECT name, price, image_url FROM products WHERE id = :product_id");
                foreach ($items as $item) {
                    $product_stmt->execute([':product_id' => $item['id']]);
                    $product_details = $product_stmt->fetch(PDO::FETCH_ASSOC);

                    if ($product_details) {
                        $products[] = [
                            'name' => $product_details['name'],
                            'price' => $product_details['price'],
                            'image_url' => $product_details['image_url'],
                            'quantity' => $item['quantity'],
                            'color' => $item['color'] ?? null,
                        ];
                    }
                }
            }
            
            // Format data for response
            $order['created_at_jalali'] = jdate('Y/m/d H:i', strtotime($order['created_at']));
            $order['status_jalali'] = get_persian_status($order['status']);

            
            $response['success'] = true;
            $response['message'] = 'سفارش یافت شد.';
            $response['order'] = $order;
            $response['products'] = $products;
        } else {
            $response['message'] = 'سفارشی با این مشخصات یافت نشد.';
        }
    } catch (PDOException $e) {
        error_log("Order tracking PDO error: " . $e->getMessage());
        $response['message'] = 'خطا در پایگاه داده رخ داد: ' . $e->getMessage();
    } catch (Exception $e) {
        error_log("Order tracking general error: " . $e->getMessage());
        $response['message'] = $e->getMessage(); // Show the specific error message for now
    }

    echo json_encode($response);
    
}
?>