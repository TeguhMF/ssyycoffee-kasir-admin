<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi kasir tidak valid.']);
    exit;
}

try {
    if ($action === 'checkout') {
        if (empty($_SESSION['cart'])) throw new Exception("Keranjang kosong!");

        $payment_method = $input['payment_method'] ?? 'cash';
        $customer_phone = trim($input['customer_phone'] ?? '');
        $redeem_points = $input['redeem_points'] ?? false; // Flag tukar poin dari frontend
        
        $customer_id = null;
        $total_qty = 0;
        $subtotal = 0;
        $discount_value = 0;

        foreach ($_SESSION['cart'] as $item) {
            $subtotal += ($item['price_at_sale'] * $item['qty']);
            $total_qty += $item['qty'];
        }

        if (!empty($customer_phone)) {
            $stmt_cust = $pdo->prepare("SELECT id, points FROM customers WHERE phone = ?");
            $stmt_cust->execute([$customer_phone]);
            $customer = $stmt_cust->fetch();

            if ($customer) {
                $customer_id = $customer['id'];
                

                if ($redeem_points && $customer['points'] >= 10000) {
                    $discount_value = 10000; 
                    
                    $stmt_deduct = $pdo->prepare("UPDATE customers SET points = points - 10000 WHERE id = ?");
                    $stmt_deduct->execute([$customer_id]);
                }

            } else {
                $stmt_new = $pdo->prepare("INSERT INTO customers (name, phone, points) VALUES (?, ?, 0)");
                $stmt_new->execute(['Member ' . $customer_phone, $customer_phone]);
                $customer_id = $pdo->lastInsertId();
            }
        }

        $grand_total = $subtotal - $discount_value;

        $today = date('Ymd');
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $invoice_number = 'SSYY-' . $today . '-' . str_pad($stmt_count->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);

        $pdo->beginTransaction();

        $discount_type = $discount_value > 0 ? 'nominal' : 'none';

        $stmt_order = $pdo->prepare("INSERT INTO orders (invoice_number, user_id, customer_id, subtotal, discount_type, discount_value, grand_total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed')");
        $stmt_order->execute([$invoice_number, $user_id, $customer_id, $subtotal, $discount_type, $discount_value, $grand_total, $payment_method]);
        $order_id = $pdo->lastInsertId();

        $stmt_detail = $pdo->prepare("INSERT INTO order_details (order_id, menu_id, qty, price_at_sale, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt_var = $pdo->prepare("INSERT INTO order_detail_variants (order_detail_id, variant_option_id) VALUES (?, ?)");

        foreach ($_SESSION['cart'] as $item) {
            $stmt_detail->execute([$order_id, $item['menu_id'], $item['qty'], $item['price_at_sale'], ($item['price_at_sale'] * $item['qty'])]);
            $detail_id = $pdo->lastInsertId();

            if (!empty($item['variant_details'])) {
                foreach ($item['variant_details'] as $variant) {
                    $stmt_var->execute([$detail_id, $variant['id']]);
                }
            }
        }

        if ($customer_id) {
            $points_earned = $total_qty * 50;
            $stmt_pts = $pdo->prepare("UPDATE customers SET points = points + ? WHERE id = ?");
            $stmt_pts->execute([$points_earned, $customer_id]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];

        echo json_encode(['status' => 'success', 'order_id' => $order_id]);
        exit;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}