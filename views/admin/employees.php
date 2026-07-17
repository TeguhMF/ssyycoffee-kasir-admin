<?php
require_once '../../config/database.php';
require_once '../../helpers/auth.php';

check_access(['admin']);

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $username = strtolower(trim($_POST['username'])); // Username jadikan huruf kecil semua
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($name) && !empty($username) && !empty($password)) {
        
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->execute([$username]);
        
        if ($stmt_check->fetch()) {
            $msg = "Gagal: Username '$username' sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $username, $hashed_password, $role]);
            $msg = "Karyawan baru berhasil ditambahkan!";
        }
    } else {
        $msg = "Gagal: Semua kolom wajib diisi.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $username = strtolower(trim($_POST['username']));
    $role = $_POST['role'];
    $password = $_POST['password']; 

    $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt_check->execute([$username, $user_id]);
    
    if ($stmt_check->fetch()) {
        $msg = "Gagal: Username '$username' sudah dipakai karyawan lain!";
    } else {
        if (!empty($password)) {
        
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, role = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $username, $role, $hashed_password, $user_id]);
        } else {
            
            $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $username, $role, $user_id]);
        }
        $msg = "Data karyawan berhasil diperbarui!";
    }
}

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    

    if ($delete_id === $_SESSION['user_id']) {
        $msg = "Peringatan: Anda tidak dapat menghapus akun Anda sendiri!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: employees.php");
        exit;
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, name ASC")->fetchAll();
?>

<?php include '../layouts/header.php'; ?>

<div class="flex justify-between items-center mb-8">
    <div>
        <h2 class="text-3xl font-extrabold text-coffee-950 tracking-tight">Manajemen Karyawan</h2>
        <p class="text-sm text-coffee-600 mt-1">Kelola akses akun admin dan kasir sistem SsyyCoffee.</p>
    </div>
</div>

<?php if(!empty($msg)): ?>
    <?php $is_error = strpos($msg, 'Gagal') !== false || strpos($msg, 'Peringatan') !== false; ?>
    <div class="mb-6 p-4 <?= $is_error ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800' ?> border text-sm font-bold rounded-2xl flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    
    <!-- KOLOM KIRI: FORM TAMBAH -->
    <div class="bg-white p-6 rounded-3xl shadow-soft border border-coffee-100">
        <h3 class="text-lg font-bold text-coffee-950 mb-4">Tambah Akun Baru</h3>
        <form action="employees.php" method="POST" class="space-y-4">
            <input type="hidden" name="add_user" value="1">
            
            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Username Login</label>
                <input type="text" name="username" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600" placeholder="Tanpa spasi">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Password Akses</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Posisi / Role</label>
                <div class="flex gap-4 mt-1">
                    <label class="inline-flex items-center text-sm font-semibold">
                        <input type="radio" name="role" value="cashier" checked class="text-coffee-800 focus:ring-0 mr-2"> Kasir
                    </label>
                    <label class="inline-flex items-center text-sm font-semibold text-red-600">
                        <input type="radio" name="role" value="admin" class="text-red-600 focus:ring-0 mr-2"> Admin
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-coffee-800 hover:bg-coffee-950 text-white text-sm font-bold rounded-xl transition-all shadow-md">
                Daftarkan Karyawan
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-3xl shadow-soft border border-coffee-100 overflow-hidden">
        <div class="p-6 border-b border-coffee-100">
            <h3 class="text-lg font-bold text-coffee-950">Daftar Pengguna Sistem</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-coffee-50 border-b border-coffee-100 text-[11px] font-bold uppercase tracking-wider text-coffee-600">
                        <th class="p-4 pl-6">Nama Pengguna</th>
                        <th class="p-4">Username Login</th>
                        <th class="p-4">Hak Akses</th>
                        <th class="p-4 text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100 text-sm">
                    <?php foreach($users as $user): ?>
                        <tr class="hover:bg-coffee-50/50 transition-colors">
                            <td class="p-4 pl-6 font-bold text-coffee-950">
                                <?= htmlspecialchars($user['name']) ?>
                                <?php if($user['id'] === $_SESSION['user_id']): ?>
                                    <span class="ml-2 text-[10px] bg-coffee-200 text-coffee-800 px-2 py-0.5 rounded-full font-bold">You</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-coffee-600 font-medium">@<?= htmlspecialchars($user['username']) ?></td>
                            <td class="p-4">
                                <?php if($user['role'] === 'admin'): ?>
                                    <span class="px-2.5 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-lg border border-red-100">Administrator</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 bg-coffee-100 text-coffee-800 text-xs font-bold rounded-lg border border-coffee-200">Kasir</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center pr-6 whitespace-nowrap">
                                <button onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'])) ?>', '<?= htmlspecialchars(addslashes($user['username'])) ?>', '<?= $user['role'] ?>')" class="px-3 py-1.5 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition-colors text-xs font-bold mr-1">
                                    Edit
                                </button>
                                
                                <?php if($user['id'] !== $_SESSION['user_id']): ?>
                                    <a href="employees.php?delete=<?= $user['id'] ?>" onclick="return confirm('Hapus akses karyawan ini?')" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors text-xs font-bold">Hapus</a>
                                <?php else: ?>
                                    <span class="px-3 py-1.5 bg-stone-100 text-stone-400 rounded-xl text-xs font-bold cursor-not-allowed" title="Anda tidak bisa menghapus diri sendiri">Hapus</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
>
<div id="edit-modal" class="hidden fixed inset-0 bg-stone-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-coffee-100 flex justify-between items-center bg-coffee-50">
            <h3 class="font-bold text-lg text-coffee-950">Edit Data Karyawan</h3>
            <button onclick="closeEditModal()" class="text-coffee-600 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <form action="employees.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="edit_user" value="1">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Username Login</label>
                <input type="text" name="username" id="edit_username" required class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Posisi / Role</label>
                <div class="flex gap-4 mt-1">
                    <label class="inline-flex items-center text-sm font-semibold">
                        <input type="radio" name="role" id="edit_role_cashier" value="cashier" class="text-coffee-800 focus:ring-0 mr-2"> Kasir
                    </label>
                    <label class="inline-flex items-center text-sm font-semibold text-red-600">
                        <input type="radio" name="role" id="edit_role_admin" value="admin" class="text-red-600 focus:ring-0 mr-2"> Admin
                    </label>
                </div>
            </div>

            <div class="pt-2 border-t border-coffee-100">
                <label class="block text-xs font-bold text-coffee-950 uppercase mb-2">Reset Password <span class="text-[10px] text-coffee-600 normal-case">(Kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" placeholder="Ketik password baru..." class="w-full px-4 py-2.5 bg-coffee-50 border border-coffee-200 rounded-xl text-sm focus:outline-none focus:border-coffee-600">
            </div>

            <button type="submit" class="w-full mt-4 py-3.5 bg-coffee-800 hover:bg-coffee-950 text-white text-sm font-bold rounded-xl shadow-md transition-all">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, username, role) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_username').value = username;
        
        if (role === 'admin') {
            document.getElementById('edit_role_admin').checked = true;
        } else {
            document.getElementById('edit_role_cashier').checked = true;
        }

        document.getElementById('edit-modal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>

<?php include '../layouts/footer.php'; ?>