<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? ($_GET['action'] ?? 'get');

try {

    if ($action === 'add') {
        $menu_id = intval($input['menu_id']);
        $qty = intval($input['qty'] ?? 1);
        $variants = $input['variants'] ?? []; 

        $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ? AND status = 'available'");
        $stmt->execute([$menu_id]);
        $menu = $stmt->fetch();

        if (!$menu) throw new Exception("Menu tidak ditemukan atau sedang habis.");

        $price_at_sale = floatval($menu['base_price']);
        $variant_details = [];

        if (!empty($variants)) {
            $inQuery = implode(',', array_map('intval', $variants));
            $stmt_var = $pdo->query("SELECT * FROM variant_options WHERE id IN ($inQuery)");
            $var_data = $stmt_var->fetchAll();

            foreach ($var_data as $v) {
                $price_at_sale += floatval($v['additional_price']);
                $variant_details[] = [
                    'id' => $v['id'],
                    'name' => $v['option_name'],
                    'price' => floatval($v['additional_price'])
                ];
            }
        }

        sort($variants);
        $cart_id = $menu_id . '_' . implode('_', $variants);

        if (isset($_SESSION['cart'][$cart_id])) {
            $_SESSION['cart'][$cart_id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$cart_id] = [
                'cart_id' => $cart_id,
                'menu_id' => $menu_id,
                'name' => $menu['name'],
                'base_price' => floatval($menu['base_price']),
                'price_at_sale' => $price_at_sale,
                'qty' => $qty,
                'variant_details' => $variant_details
            ];
        }

        echo json_encode(['status' => 'success', 'cart' => array_values($_SESSION['cart'])]);
        exit;
    }

    if ($action === 'update_qty') {
        $cart_id = $input['cart_id'] ?? '';
        $qty = intval($input['qty']);

        if (isset($_SESSION['cart'][$cart_id])) {
            if ($qty > 0) {
                $_SESSION['cart'][$cart_id]['qty'] = $qty;
            } else {
                // Jika qty 0, hapus dari keranjang
                unset($_SESSION['cart'][$cart_id]);
            }
        }
        echo json_encode(['status' => 'success', 'cart' => array_values($_SESSION['cart'])]);
        exit;
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        echo json_encode(['status' => 'success', 'cart' => []]);
        exit;
    }

    echo json_encode(['status' => 'success', 'cart' => array_values($_SESSION['cart'])]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}