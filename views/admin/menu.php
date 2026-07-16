<?php
// views/admin/menu.php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

check_access(['admin']);

$msg = '';

// A. LOGIKA PROSES TAMBAH MENU BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu'])) {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $base_price = floatval($_POST['base_price']);
    $status = $_POST['status'] === 'empty' ? 'empty' : 'available';
    
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $image_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_dir = '../../public/uploads/';
        
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        move_uploaded_file($file_tmp, $target_dir . $image_name);
    }

    if (!empty($name) && $category_id > 0 && $base_price >= 0) {
        $stmt = $pdo->prepare("INSERT INTO menus (category_id, name, base_price, image, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $name, $base_price, $image_name, $status]);
        $msg = "Menu berhasil ditambahkan!";
    } else {
        $msg = "Gagal: Mohon isi seluruh form dengan benar.";
    }
}

// B. LOGIKA PROSES EDIT MENU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_menu'])) {
    $menu_id = intval($_POST['menu_id']);
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $base_price = floatval($_POST['base_price']);
    $status = $_POST['status'] === 'empty' ? 'empty' : 'available';

    // Persiapkan query dasar
    $update_query = "UPDATE menus SET category_id = ?, name = ?, base_price = ?, status = ?";
    $params = [$category_id, $name, $base_price, $status];

    // Cek apakah ada foto baru yang diupload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Ambil info foto lama untuk dihapus
        $stmt_old = $pdo->prepare("SELECT image FROM menus WHERE id = ?");
        $stmt_old->execute([$menu_id]);
        $old_img = $stmt_old->fetchColumn();
        
        if ($old_img && file_exists('../../public/uploads/' . $old_img)) {
            unlink('../../public/uploads/' . $old_img);
        }

        // Proses foto baru
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $image_name = time() . '_edit_' . uniqid() . '.' . $file_ext;
        move_uploaded_file($file_tmp, '../../public/uploads/' . $image_name);

        $update_query .= ", image = ?";
        $params[] = $image_name;
    }

    // Eksekusi update
    $update_query .= " WHERE id = ?";
    $params[] = $menu_id;

    $stmt = $pdo->prepare($update_query);
    $stmt->execute($params);
    $msg = "Data menu berhasil diperbarui!";
}

// C. LOGIKA HAPUS MENU
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt_img = $pdo->prepare("SELECT image FROM menus WHERE id = ?");
    $stmt_img->execute([$delete_id]);
    $menu_item = $stmt_img->fetch();
    
    if ($menu_item) {
        if ($menu_item['image'] && file_exists('../../public/uploads/' . $menu_item['image'])) {
            unlink('../../public/uploads/' . $menu_item['image']);
        }
        $stmt_del = $pdo->prepare("DELETE FROM menus WHERE id = ?");
        $stmt_del->execute([$delete_id]);
        header("Location: menu.php");
        exit;
    }
}

// AMBIL DATA DARI DATABASE
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$menus = $pdo->query("SELECT m.*, c.name as category_name FROM menus m JOIN categories c ON m.category_id = c.id ORDER BY m.id DESC")->fetchAll();
?>

<?php include '../layouts/header.php'; ?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-3xl font-extrabold text-coffee-950 tracking-tight">Manajemen Menu</h2>
        <p class="text-sm text-coffee-600 mt-1">Kelola katalog racikan produk dan ketersediaan stok SsyyCoffee.</p>
    </div>
</div>

<?php if(!empty($msg)): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 text-sm font-bold rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- KOLOM KIRI: FORM TAMBAH -->
    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100">
        <h3 class="text-lg font-bold text-coffee-950 mb-4">Tambah Menu Baru</h3>
        <form action="menu.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="add_menu" value="1">
            
            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Nama Menu</label>
                <input type="text" name="name" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Kategori</label>
                <select name="category_id" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
                    <option value="">Pilih Kategori</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Harga Dasar</label>
                <input type="number" name="base_price" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Status Awal</label>
                <div class="flex gap-4 mt-1">
                    <label class="inline-flex items-center text-sm font-semibold">
                        <input type="radio" name="status" value="available" checked class="text-coffee-800 focus:ring-0 mr-2"> Tersedia
                    </label>
                    <label class="inline-flex items-center text-sm font-semibold">
                        <input type="radio" name="status" value="empty" class="text-coffee-800 focus:ring-0 mr-2"> Habis
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Foto Produk</label>
                <input type="file" name="image" accept="image/*" class="w-full text-xs text-coffee-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-coffee-100 file:text-coffee-800 hover:file:bg-coffee-200">
            </div>

            <button type="submit" class="w-full py-3 bg-coffee-800 hover:bg-coffee-950 text-white text-sm font-bold rounded-xl transition-all shadow-md">
                Simpan Produk
            </button>
        </form>
    </div>

    <!-- KOLOM KANAN: TABEL -->
    <div class="lg:col-span-2 bg-white rounded-3xl shadow-soft border border-coffee-100 overflow-hidden">
        <div class="p-6 border-b border-coffee-100">
            <h3 class="text-lg font-bold text-coffee-950">Katalog Produk Terdaftar</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-coffee-50 border-b border-coffee-100 text-[11px] font-bold uppercase tracking-wider text-coffee-600">
                        <th class="p-4">Foto</th>
                        <th class="p-4">Nama Produk</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4">Harga</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-sm">
                    <?php if(empty($menus)): ?>
                        <tr><td colspan="6" class="p-8 text-center text-coffee-600">Belum ada menu terdaftar.</td></tr>
                    <?php endif; ?>
                    <?php foreach($menus as $row): ?>
                        <tr class="hover:bg-coffee-50/50 transition-colors">
                            <td class="p-4">
                                <?php if($row['image']): ?>
                                    <img src="../../public/uploads/<?= htmlspecialchars($row['image']) ?>" class="w-12 h-12 object-cover rounded-xl shadow-sm border border-coffee-200">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-coffee-100 rounded-xl flex items-center justify-center text-xs text-coffee-600 font-bold">No Pix</div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-bold text-coffee-950"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="p-4 text-coffee-600"><?= htmlspecialchars($row['category_name']) ?></td>
                            <td class="p-4 font-semibold text-coffee-950">Rp <?= number_format($row['base_price'], 0, ',', '.') ?></td>
                            <td class="p-4">
                                <?php if($row['status'] === 'available'): ?>
                                    <span class="px-2.5 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-lg border border-green-100">Tersedia</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-lg border border-red-100">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                <a href="variant.php?menu_id=<?= $row['id'] ?>" class="px-3 py-1.5 bg-coffee-100 text-coffee-950 rounded-xl hover:bg-coffee-200 transition-colors text-xs font-bold mr-1">+ Varian</a>
                                
                                <!-- TOMBOL TRIGGER MODAL EDIT -->
                                <button onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>', <?= $row['category_id'] ?>, <?= $row['base_price'] ?>, '<?= $row['status'] ?>')" 
                                        class="px-3 py-1.5 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition-colors text-xs font-bold mr-1">
                                    Edit
                                </button>
                                
                                <a href="menu.php?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus menu ini beserta fotonya?')" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors text-xs font-bold">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL EDIT MENU -->
<div id="edit-modal" class="hidden fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-coffee-100 flex justify-between items-center bg-coffee-50">
            <h3 class="font-bold text-lg text-coffee-950">Edit Menu</h3>
            <button onclick="closeEditModal()" class="text-coffee-600 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <form action="menu.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="edit_menu" value="1">
            <input type="hidden" name="menu_id" id="edit_menu_id">
            
            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Nama Menu</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Kategori</label>
                <select name="category_id" id="edit_category_id" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Harga Dasar</label>
                <input type="number" name="base_price" id="edit_base_price" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Status Stok</label>
                <div class="flex gap-4 mt-1">
                    <label class="inline-flex items-center text-sm font-semibold">
                        <input type="radio" name="status" id="edit_status_available" value="available" class="text-coffee-800 focus:ring-0 mr-2"> Tersedia
                    </label>
                    <label class="inline-flex items-center text-sm font-semibold">
                        <input type="radio" name="status" id="edit_status_empty" value="empty" class="text-coffee-800 focus:ring-0 mr-2"> Habis
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Ganti Foto <span class="text-[10px] text-coffee-600 normal-case">(Opsional)</span></label>
                <input type="file" name="image" accept="image/*" class="w-full text-xs text-coffee-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-coffee-100 file:text-coffee-800 hover:file:bg-coffee-200">
            </div>

            <button type="submit" class="w-full mt-4 py-3.5 bg-coffee-800 hover:bg-coffee-950 text-white text-sm font-bold rounded-xl shadow-md transition-all">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<!-- SCRIPT UNTUK MODAL EDIT -->
<script>
    function openEditModal(id, name, categoryId, basePrice, status) {
        // Isi data ke dalam form modal
        document.getElementById('edit_menu_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category_id').value = categoryId;
        document.getElementById('edit_base_price').value = basePrice;
        
        if (status === 'available') {
            document.getElementById('edit_status_available').checked = true;
        } else {
            document.getElementById('edit_status_empty').checked = true;
        }

        // Tampilkan modal
        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>

<?php include '../layouts/footer.php'; ?>