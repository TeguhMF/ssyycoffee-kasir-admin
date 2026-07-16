<?php
// views/cashier/receipt.php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

check_access(['admin', 'cashier']);

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data transaksi utama beserta nama kasir
$stmt_order = $pdo->prepare("SELECT o.*, u.name as cashier_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt_order->execute([$order_id]);
$order = $stmt_order->fetch();

if (!$order) {
    die("Struk tidak ditemukan.");
}

// Ambil data item belanja
$stmt_details = $pdo->prepare("SELECT od.*, m.name as menu_name FROM order_details od JOIN menus m ON od.menu_id = m.id WHERE od.order_id = ?");
$stmt_details->execute([$order_id]);
$details = $stmt_details->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #<?= htmlspecialchars($order['invoice_number']) ?></title>
    <style>
        /* Pengaturan ukuran kertas Printer Thermal 58mm */
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm; /* Sesuaikan ke 80mm jika printernya lebar */
            margin: 0 auto;
            padding: 10px;
            color: #000;
            font-size: 12px;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .flex { display: flex; justify-content: space-between; }
        .mb-2 { margin-bottom: 8px; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .item-name { width: 100%; display: block; }
        .variant-text { font-size: 10px; padding-left: 10px; }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center mb-2">
        <h2 style="margin:0; font-size:16px;">SsyyCoffee</h2>
        <p style="margin:0; font-size:10px;">Jl. Raya Pemrograman No. 1</p>
    </div>

    <div class="border-bottom border-top">
        <div class="flex"><span>No:</span> <span><?= htmlspecialchars($order['invoice_number']) ?></span></div>
        <div class="flex"><span>Tgl:</span> <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span></div>
        <div class="flex"><span>Kasir:</span> <span><?= htmlspecialchars($order['cashier_name']) ?></span></div>
    </div>

    <div class="border-bottom">
        <?php foreach ($details as $d): ?>
            <span class="item-name font-bold"><?= htmlspecialchars($d['menu_name']) ?></span>
            
            <?php
            // Ambil varian tiap item
            $stmt_var = $pdo->prepare("SELECT vo.option_name FROM order_detail_variants odv JOIN variant_options vo ON odv.variant_option_id = vo.id WHERE odv.order_detail_id = ?");
            $stmt_var->execute([$d['id']]);
            $variants = $stmt_var->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($variants)):
            ?>
                <div class="variant-text">- <?= htmlspecialchars(implode(', ', $variants)) ?></div>
            <?php endif; ?>

            <div class="item-row">
                <span><?= $d['qty'] ?>x @<?= number_format($d['price_at_sale'], 0, ',', '.') ?></span>
                <span><?= number_format($d['total_price'], 0, ',', '.') ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mb-2">
        <div class="flex font-bold">
            <span>TOTAL</span>
            <span>Rp <?= number_format($order['grand_total'], 0, ',', '.') ?></span>
        </div>
        <div class="flex">
            <span>Pembayaran</span>
            <span style="text-transform:uppercase;"><?= htmlspecialchars($order['payment_method']) ?></span>
        </div>
    </div>

    <div class="text-center border-top font-bold mt-4">
        Terima Kasih!<br>
        Silakan Berkunjung Kembali
    </div>

</body>
</html>