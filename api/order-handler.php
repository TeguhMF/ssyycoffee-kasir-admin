<?php
// api/order-handler.php
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
    echo json_encode(['status' => 'error', 'message' => 'Sesi kasir tidak valid. Silakan login ulang.']);
    exit;
}

try {
    if ($action === 'checkout') {
        if (empty($_SESSION['cart'])) {
            throw new Exception("Keranjang belanja kosong!");
        }

        $payment_method = $input['payment_method'] ?? 'cash'; // 'cash' atau 'qris'
        $discount_value = 0; // Bisa dikembangkan nanti jika ada form input diskon

        // 1. Hitung Subtotal asli dari session server (Mencegah manipulasi harga dari browser)
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += ($item['price_at_sale'] * $item['qty']);
        }
        $grand_total = $subtotal - $discount_value;

        // 2. Generate Nomor Invoice (Contoh: SSYY-20260716-0001)
        $today = date('Ymd');
        $stmt_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $daily_count = $stmt_count->fetchColumn() + 1;
        $invoice_number = 'SSYY-' . $today . '-' . str_pad($daily_count, 4, '0', STR_PAD_LEFT);

        // 3. Mulai Transaksi Database (Aman dari kegagalan insert parsial)
        $pdo->beginTransaction();

        // A. Insert ke tabel orders
        $stmt_order = $pdo->prepare("INSERT INTO orders (invoice_number, user_id, subtotal, discount_type, discount_value, grand_total, payment_method, status) VALUES (?, ?, ?, 'none', ?, ?, ?, 'completed')");
        $stmt_order->execute([$invoice_number, $user_id, $subtotal, $discount_value, $grand_total, $payment_method]);
        $order_id = $pdo->lastInsertId();

        // B. Insert ke tabel order_details & order_detail_variants
        $stmt_detail = $pdo->prepare("INSERT INTO order_details (order_id, menu_id, qty, price_at_sale, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt_var = $pdo->prepare("INSERT INTO order_detail_variants (order_detail_id, variant_option_id) VALUES (?, ?)");

        foreach ($_SESSION['cart'] as $item) {
            $total_price_item = $item['price_at_sale'] * $item['qty'];
            $stmt_detail->execute([$order_id, $item['menu_id'], $item['qty'], $item['price_at_sale'], $total_price_item]);
            $order_detail_id = $pdo->lastInsertId();

            // Jika item ini punya varian terpilih, simpan ke tabel jembatan
            if (!empty($item['variant_details'])) {
                foreach ($item['variant_details'] as $variant) {
                    $stmt_var->execute([$order_detail_id, $variant['id']]);
                }
            }
        }

        $pdo->commit();

        // Kosongkan keranjang setelah sukses
        $_SESSION['cart'] = [];

        // Kembalikan ID Order agar JS bisa membuka halaman struk
        echo json_encode(['status' => 'success', 'order_id' => $order_id]);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}