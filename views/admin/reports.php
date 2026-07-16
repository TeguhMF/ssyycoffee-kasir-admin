<?php
// views/admin/reports.php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

// Proteksi halaman: Hanya admin
check_access(['admin']);

// Mengatur nilai default untuk filter tanggal (Awal bulan sampai Hari ini)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// 1. Ambil Ringkasan (Summary) berdasarkan rentang tanggal
$stmt_summary = $pdo->prepare("SELECT COUNT(id) as total_trx, SUM(grand_total) as total_income FROM orders WHERE DATE(created_at) >= ? AND DATE(created_at) <= ? AND status = 'completed'");
$stmt_summary->execute([$start_date, $end_date]);
$summary = $stmt_summary->fetch();

$total_trx = $summary['total_trx'] ?? 0;
$total_income = $summary['total_income'] ?? 0;

// 2. Ambil Daftar Transaksi berdasarkan rentang tanggal
$stmt_orders = $pdo->prepare("SELECT o.*, u.name as cashier_name FROM orders o JOIN users u ON o.user_id = u.id WHERE DATE(o.created_at) >= ? AND DATE(o.created_at) <= ? AND o.status = 'completed' ORDER BY o.id DESC");
$stmt_orders->execute([$start_date, $end_date]);
$orders = $stmt_orders->fetchAll();
?>

<?php include '../layouts/header.php'; ?>

<div class="flex flex-col md:flex-row md:justify-between md:items-end mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-coffee-950 tracking-tight">Laporan Penjualan</h2>
        <p class="text-sm text-coffee-600 mt-1">Pantau riwayat transaksi dan hitung omzet berdasarkan periode waktu.</p>
    </div>
    
    <!-- Form Filter Tanggal -->
    <form action="reports.php" method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-coffee-100">
        <div class="flex items-center gap-2 px-2">
            <span class="text-xs font-bold text-coffee-600">Dari:</span>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required class="text-sm font-semibold text-coffee-950 bg-transparent border-none focus:ring-0 cursor-pointer">
        </div>
        <div class="w-px h-6 bg-coffee-200"></div>
        <div class="flex items-center gap-2 px-2">
            <span class="text-xs font-bold text-coffee-600">Sampai:</span>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required class="text-sm font-semibold text-coffee-950 bg-transparent border-none focus:ring-0 cursor-pointer">
        </div>
        <button type="submit" class="px-4 py-2 bg-coffee-800 hover:bg-coffee-950 text-white text-xs font-bold rounded-xl transition-colors shadow-md">
            Filter Data
        </button>
    </form>
</div>

<!-- Ringkasan Periode Terpilih -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-coffee-600 uppercase tracking-wider mb-1">Total Pendapatan (Periode Ini)</p>
            <h3 class="text-3xl font-extrabold text-green-700">Rp <?= number_format($total_income, 0, ',', '.') ?></h3>
        </div>
        <div class="w-16 h-16 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center border border-green-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100 flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-coffee-600 uppercase tracking-wider mb-1">Total Transaksi (Periode Ini)</p>
            <h3 class="text-3xl font-extrabold text-coffee-950"><?= number_format($total_trx, 0, ',', '.') ?> <span class="text-lg text-coffee-600 font-semibold">Struk</span></h3>
        </div>
        <div class="w-16 h-16 rounded-2xl bg-coffee-50 text-coffee-800 flex items-center justify-center border border-coffee-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
        </div>
    </div>
</div>

<!-- Tabel Riwayat Transaksi -->
<div class="bg-white rounded-3xl shadow-soft border border-coffee-100 overflow-hidden">
    <div class="p-6 border-b border-coffee-100">
        <h3 class="text-lg font-bold text-coffee-950">Rincian Riwayat Transaksi</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-coffee-50 border-b border-coffee-100 text-[11px] font-bold uppercase tracking-wider text-coffee-600">
                    <th class="p-4 pl-6">No. Invoice</th>
                    <th class="p-4">Tanggal & Waktu</th>
                    <th class="p-4">Kasir</th>
                    <th class="p-4 text-center">Metode</th>
                    <th class="p-4 text-right">Total Transaksi</th>
                    <th class="p-4 text-center pr-6">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-coffee-100 text-sm">
                <?php if(empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="p-10 text-center text-coffee-600">Tidak ada data transaksi pada rentang tanggal tersebut.</td>
                    </tr>
                <?php endif; ?>
                
                <?php foreach($orders as $order): ?>
                    <tr class="hover:bg-coffee-50/50 transition-colors">
                        <td class="p-4 pl-6 font-bold text-coffee-950"><?= htmlspecialchars($order['invoice_number']) ?></td>
                        <td class="p-4 text-coffee-800 font-medium"><?= date('d M Y - H:i', strtotime($order['created_at'])) ?></td>
                        <td class="p-4 text-coffee-600 font-medium"><?= htmlspecialchars($order['cashier_name']) ?></td>
                        <td class="p-4 text-center">
                            <?php if($order['payment_method'] === 'qris'): ?>
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-lg border border-blue-100 uppercase tracking-wider">QRIS</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-lg border border-amber-100 uppercase tracking-wider">TUNAI</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right font-extrabold text-coffee-950">Rp <?= number_format($order['grand_total'], 0, ',', '.') ?></td>
                        <td class="p-4 text-center pr-6">
                            <!-- Tombol Cetak Ulang Struk -->
                            <button onclick="reprintReceipt(<?= $order['id'] ?>)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-coffee-100 text-coffee-800 rounded-xl hover:bg-coffee-200 transition-colors text-xs font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v-2.25a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25Z" /></svg>
                                Struk
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Fungsi JavaScript untuk me-reprint struk
    // Menembak langsung ke file receipt kasir yang sudah kita buat sebelumnya
    function reprintReceipt(orderId) {
        window.open(`../cashier/receipt.php?id=${orderId}`, 'Struk Ulang', 'width=400,height=600');
    }
</script>

<?php include '../layouts/footer.php'; ?>