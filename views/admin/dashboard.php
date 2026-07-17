<?php

require_once '../../config/database.php';
require_once '../../helpers/auth.php';

check_access(['admin']);

$today = date('Y-m-d');
$month = date('m');
$year = date('Y');

$stmt_omzet = $pdo->prepare("SELECT SUM(grand_total) FROM orders WHERE DATE(created_at) = ? AND status = 'completed'");
$stmt_omzet->execute([$today]);
$omzet_hari_ini = $stmt_omzet->fetchColumn() ?: 0;


$stmt_trx = $pdo->prepare("SELECT COUNT(id) FROM orders WHERE DATE(created_at) = ? AND status = 'completed'");
$stmt_trx->execute([$today]);
$trx_hari_ini = $stmt_trx->fetchColumn() ?: 0;

$stmt_item = $pdo->prepare("SELECT SUM(od.qty) FROM order_details od JOIN orders o ON od.order_id = o.id WHERE DATE(o.created_at) = ? AND o.status = 'completed'");
$stmt_item->execute([$today]);
$item_hari_ini = $stmt_item->fetchColumn() ?: 0;

$stmt_omzet_bulan = $pdo->prepare("SELECT SUM(grand_total) FROM orders WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND status = 'completed'");
$stmt_omzet_bulan->execute([$month, $year]);
$omzet_bulan_ini = $stmt_omzet_bulan->fetchColumn() ?: 0;


$chart_labels = [];
$chart_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date_loop = date('Y-m-d', strtotime("-$i days"));
    $stmt_chart = $pdo->prepare("SELECT SUM(grand_total) FROM orders WHERE DATE(created_at) = ? AND status = 'completed'");
    $stmt_chart->execute([$date_loop]);
    $val = $stmt_chart->fetchColumn() ?: 0;
    
    $chart_labels[] = date('d M', strtotime($date_loop));
    $chart_data[] = $val;
}

$stmt_latest = $pdo->query("SELECT o.*, u.name as cashier_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.status = 'completed' ORDER BY o.id DESC LIMIT 5");
$latest_orders = $stmt_latest->fetchAll();
?>

<?php include '../layouts/header.php'; ?>

<div class="mb-8">
    <h2 class="text-3xl font-extrabold text-coffee-950 tracking-tight">Dashboard</h2>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100 flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-coffee-100 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-coffee-800"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-coffee-600 uppercase tracking-wider mb-1">Pendapatan Hari Ini</p>
            <h3 class="text-2xl font-extrabold text-coffee-950">Rp <?= number_format($omzet_hari_ini, 0, ',', '.') ?></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100 flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-coffee-100 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-coffee-800"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-coffee-600 uppercase tracking-wider mb-1">Total Struk</p>
            <h3 class="text-2xl font-extrabold text-coffee-950"><?= number_format($trx_hari_ini, 0, ',', '.') ?></h3>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100 flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl bg-coffee-100 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-coffee-800"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
        </div>
        <div>
            <p class="text-xs font-bold text-coffee-600 uppercase tracking-wider mb-1">Item Terjual</p>
            <h3 class="text-2xl font-extrabold text-coffee-950"><?= number_format($item_hari_ini, 0, ',', '.') ?> <span class="text-sm font-semibold text-coffee-600">Cup/Pcs</span></h3>
        </div>
    </div>

    <div class="bg-coffee-950 p-6 rounded-3xl shadow-soft border border-coffee-800 flex items-center gap-5 relative overflow-hidden">
        <div class="absolute -right-4 -top-4 opacity-10">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-32 h-32 text-white"><path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z" clip-rule="evenodd" /></svg>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-coffee-800/50 border border-coffee-600/30 flex items-center justify-center shrink-0 z-10">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-coffee-100"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
        </div>
        <div class="z-10">
            <p class="text-xs font-bold text-coffee-200 uppercase tracking-wider mb-1">Bulan Ini</p>
            <h3 class="text-2xl font-extrabold text-white">Rp <?= number_format($omzet_bulan_ini, 0, ',', '.') ?></h3>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-soft border border-coffee-100">
        <h3 class="text-lg font-bold text-coffee-950 mb-6">Tren Penjualan (7 Hari Terakhir)</h3>
        <div class="relative h-72 w-full">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100 flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-coffee-950">Transaksi Terbaru</h3>
            <a href="reports.php" class="text-xs font-bold text-coffee-600 hover:text-coffee-800">Lihat Semua</a>
        </div>
        
        <div class="flex-1 overflow-y-auto pr-2 space-y-4">
            <?php if(empty($latest_orders)): ?>
                <div class="text-center text-sm text-coffee-600 mt-10">Belum ada transaksi sukses.</div>
            <?php endif; ?>
            
            <?php foreach($latest_orders as $ord): ?>
                <div class="flex justify-between items-center p-3 rounded-2xl hover:bg-coffee-50 transition-colors border border-transparent hover:border-coffee-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-coffee-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-coffee-800"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-coffee-950 leading-tight"><?= htmlspecialchars($ord['invoice_number']) ?></p>
                            <p class="text-[10px] font-semibold text-coffee-600 uppercase"><?= date('H:i', strtotime($ord['created_at'])) ?> • <?= htmlspecialchars($ord['payment_method']) ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-extrabold text-coffee-950">Rp <?= number_format($ord['grand_total'], 0, ',', '.') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    const labels = <?= json_encode($chart_labels) ?>;
    const data = <?= json_encode($chart_data) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Omzet Penjualan (Rp)',
                data: data,
                borderColor: '#5C3A21',
                backgroundColor: 'rgba(140, 98, 57, 0.1)', 
                borderWidth: 3,
                pointBackgroundColor: '#2B1704',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2B1704',
                    titleFont: { family: 'sans-serif', size: 13 },
                    bodyFont: { family: 'sans-serif', size: 14, weight: 'bold' },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.y;
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F4EFE6', borderDash: [5, 5] },
                    ticks: {
                        color: '#8C6239',
                        font: { family: 'sans-serif', size: 11, weight: 'bold' },
                        callback: function(value) {
                            if (value === 0) return '0';
                            return 'Rp ' + (value / 1000) + 'K';
                        }
                    },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#8C6239', font: { family: 'sans-serif', size: 11, weight: 'bold' } },
                    border: { display: false }
                }
            }
        }
    });
</script>

<?php include '../layouts/footer.php'; ?>