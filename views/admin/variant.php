<?php
// views/admin/variant.php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

check_access(['admin']);

$msg = '';
$menu_id = isset($_GET['menu_id']) ? intval($_GET['menu_id']) : 0;

// Ambil info detail menu utama
$stmt_menu = $pdo->prepare("SELECT m.*, c.name as category_name FROM menus m JOIN categories c ON m.category_id = c.id WHERE m.id = ?");
$stmt_menu->execute([$menu_id]);
$menu = $stmt_menu->fetch();

if (!$menu) {
    header("Location: menu.php");
    exit;
}

// A. LOGIKA TAMBAH GRUP VARIAN BARU (Misal: Size, Sugar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_group'])) {
    $group_name = trim($_POST['group_name']);
    if (!empty($group_name)) {
        $stmt = $pdo->prepare("INSERT INTO variant_groups (menu_id, group_name) VALUES (?, ?)");
        $stmt->execute([$menu_id, $group_name]);
        $msg = "Grup varian berhasil ditambahkan!";
    }
}

// B. LOGIKA TAMBAH OPSI VARIAN BARU (Misal: Large +Rp5.000)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    $variant_group_id = intval($_POST['variant_group_id']);
    $option_name = trim($_POST['option_name']);
    $additional_price = floatval($_POST['additional_price']);

    if ($variant_group_id > 0 && !empty($option_name)) {
        $stmt = $pdo->prepare("INSERT INTO variant_options (variant_group_id, option_name, additional_price) VALUES (?, ?, ?)");
        $stmt->execute([$variant_group_id, $option_name, $additional_price]);
        $msg = "Opsi varian berhasil ditambahkan!";
    }
}

// C. LOGIKA HAPUS GRUP ATAU OPSI
if (isset($_GET['delete_group'])) {
    $group_id = intval($_GET['delete_group']);
    $stmt = $pdo->prepare("DELETE FROM variant_groups WHERE id = ? AND menu_id = ?");
    $stmt->execute([$group_id, $menu_id]);
    header("Location: variant.php?menu_id=" . $menu_id);
    exit;
}

if (isset($_GET['delete_option'])) {
    $option_id = intval($_GET['delete_option']);
    $stmt = $pdo->prepare("DELETE FROM variant_options WHERE id = ?");
    $stmt->execute([$option_id]);
    header("Location: variant.php?menu_id=" . $menu_id);
    exit;
}

// D. AMBIL DATA STRUKTUR VARIAN DARI DATABASE
$groups = $pdo->prepare("SELECT * FROM variant_groups WHERE menu_id = ? ORDER BY id ASC");
$groups->execute([$menu_id]);
$variant_groups = $groups->fetchAll();
?>

<?php include '../layouts/header.php'; ?>

<!-- Top Bar navigasi balik -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <a href="menu.php" class="inline-flex items-center gap-2 text-xs font-bold text-coffee-600 uppercase tracking-wider mb-2 hover:text-coffee-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Katalog
        </a>
        <h2 class="text-2xl font-extrabold text-coffee-950 tracking-tight">Pengaturan Varian: <?= htmlspecialchars($menu['name']) ?></h2>
        <p class="text-sm text-coffee-600 mt-1">Kategori: <?= htmlspecialchars($menu['category_name']) ?> | Harga Dasar: Rp <?= number_format($menu['base_price'], 0, ',', '.') ?></p>
    </div>
</div>

<?php if(!empty($msg)): ?>
    <div class="mb-6 p-4 bg-amber-100 border border-amber-200 text-amber-900 text-sm font-semibold rounded-2xl">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- FORMS COLUMN -->
    <div class="space-y-6">
        <!-- FORM 1: Tambah Grup Varian -->
        <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100">
            <h3 class="text-sm font-bold text-coffee-950 uppercase tracking-wider mb-4">1. Buat Grup Varian</h3>
            <form action="variant.php?menu_id=<?= $menu_id ?>" method="POST" class="space-y-4">
                <input type="hidden" name="add_group" value="1">
                <div>
                    <label class="block text-xs font-bold text-coffee-600 uppercase mb-2">Nama Grup</label>
                    <input type="text" name="group_name" required placeholder="Contoh: Ukuran, Suhu, Topping" class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
                </div>
                <button type="submit" class="w-full py-2.5 bg-coffee-800 hover:bg-coffee-950 text-white text-xs font-bold rounded-xl transition-all shadow-md">
                    Tambah Grup
                </button>
            </form>
        </div>

        <!-- FORM 2: Tambah Opsi Varian (Hanya muncul jika sudah ada grup) -->
        <?php if (!empty($variant_groups)): ?>
        <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100">
            <h3 class="text-sm font-bold text-coffee-950 uppercase tracking-wider mb-4">2. Tambah Opsi ke Grup</h3>
            <form action="variant.php?menu_id=<?= $menu_id ?>" method="POST" class="space-y-4">
                <input type="hidden" name="add_option" value="1">
                <div>
                    <label class="block text-xs font-bold text-coffee-600 uppercase mb-2">Pilih Grup Varian</label>
                    <select name="variant_group_id" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
                        <?php foreach($variant_groups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['group_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-coffee-600 uppercase mb-2">Nama Opsi</label>
                    <input type="text" name="option_name" required placeholder="Contoh: L, Ice, Less Sugar, Jelly" class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-coffee-600 uppercase mb-2">Harga Tambahan (Biaya Ekstra)</label>
                    <input type="number" name="additional_price" value="0" class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
                </div>
                <button type="submit" class="w-full py-2.5 bg-coffee-800 hover:bg-coffee-950 text-white text-xs font-bold rounded-xl transition-all shadow-md">
                    Tambah Opsi Varian
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- DISPLAY COLUMN: STRUKTUR VARIAN SAAT INI -->
    <div class="lg:col-span-2 space-y-4">
        <?php if (empty($variant_groups)): ?>
            <div class="bg-white p-8 rounded-3xl shadow-soft text-center text-coffee-600 border border-coffee-100">
                Menu ini belum memiliki varian. Gunakan form di samping untuk membuat struktur pilihan menu.
            </div>
        <?php endif; ?>

        <?php foreach($variant_groups as $group): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-coffee-100 overflow-hidden">
                <!-- Header Grup -->
                <div class="bg-coffee-50 px-6 py-4 border-b border-coffee-100 flex justify-between items-center">
                    <h4 class="font-bold text-coffee-950 text-sm uppercase tracking-wide">Grup: <?= htmlspecialchars($group['group_name']) ?></h4>
                    <a href="variant.php?menu_id=<?= $menu_id ?>&delete_group=<?= $group['id'] ?>" onclick="return confirm('Hapus grup ini beserta seluruh opsinya?')" class="text-xs font-semibold text-red-600 hover:underline">Hapus Grup</a>
                </div>
                
                <!-- Daftar Opsi di dalam Grup ini -->
                <div class="p-4 divide-y divide-coffee-100">
                    <?php
                    $opt_stmt = $pdo->prepare("SELECT * FROM variant_options WHERE variant_group_id = ? ORDER BY id ASC");
                    $opt_stmt->execute([$group['id']]);
                    $options = $opt_stmt->fetchAll();
                    
                    if (empty($options)):
                    ?>
                        <p class="text-xs text-coffee-600 p-2 italic">Belum ada opsi di dalam grup ini.</p>
                    <?php endif; ?>

                    <?php foreach($options as $opt): ?>
                        <div class="flex justify-between items-center py-3 px-2 text-sm hover:bg-coffee-50/50 rounded-xl transition-colors">
                            <span class="font-medium text-coffee-950"><?= htmlspecialchars($opt['option_name']) ?></span>
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-bold px-2 py-0.5 bg-coffee-100 text-coffee-800 rounded-md">
                                    + Rp <?= number_format($opt['additional_price'], 0, ',', '.') ?>
                                </span>
                                <a href="variant.php?menu_id=<?= $menu_id ?>&delete_option=<?= $opt['id'] ?>" class="text-red-600 hover:text-red-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>