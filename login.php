<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header('Location: views/admin/dashboard.php');
                    } else {
                        header('Location: views/cashier/index.php');
                    }
                    exit;
                } else {
                    $error = 'DEBUG: Username terdaftar, tapi PASSWORD salah di sistem.';
                }
            } else {
                $error = 'DEBUG: USERNAME tidak ditemukan di tabel database.';
            }
        } catch (PDOException $e) {
            $error = 'DATABASE ERROR: ' . $e->getMessage();
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SsyyCoffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        coffee: {
                            50: '#FDFBF7',
                            100: '#F4EFE6',
                            200: '#E6DCC8',
                            600: '#8C6239',
                            800: '#5C3A21',
                            950: '#2B1704',
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 25px -2px rgba(43, 23, 4, 0.05), 0 2px 10px -1px rgba(43, 23, 4, 0.02)',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-coffee-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-soft">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-coffee-950 tracking-tight">SsyyCoffee</h2>
            <p class="text-sm text-coffee-600 mt-2">Point of Sale & Management System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 p-4 bg-red-50 border border-red-100 text-red-700 text-sm rounded-2xl flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-5">
            <div>
                <label for="username" class="block text-xs font-bold text-coffee-950 uppercase tracking-wider mb-2">Username</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 bg-coffee-50 border border-coffee-200 text-coffee-950 rounded-xl focus:outline-none focus:border-coffee-600 transition-colors text-sm"
                    placeholder="Masukkan username kasir/admin">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-coffee-950 uppercase tracking-wider mb-2">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 bg-coffee-50 border border-coffee-200 text-coffee-950 rounded-xl focus:outline-none focus:border-coffee-600 transition-colors text-sm"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full py-3.5 bg-coffee-800 hover:bg-coffee-950 text-white font-semibold rounded-xl shadow-md transition-all text-sm mt-2">
                Masuk
            </button>
        </form>
    </div>

</body>
</html>