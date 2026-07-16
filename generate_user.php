<?php
// generate_user.php
require_once 'config/database.php';

try {
    // 1. Bersihkan tabel user lama agar tidak bentrok
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE users; SET FOREIGN_KEY_CHECKS = 1;");

    // 2. Buat hash password yang valid secara realtime
    $passAdmin = password_hash('admin123', PASSWORD_BCRYPT);
    $passKasir = password_hash('kasir123', PASSWORD_BCRYPT);

    // 3. Masukkan data Admin
    $stmt1 = $pdo->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt1->execute(['admin', $passAdmin, 'Teguh Admin', 'admin']);

    // 4. Masukkan data Kasir
    $stmt2 = $pdo->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt2->execute(['kasir', $passKasir, 'Siti Kasir', 'cashier']);

    echo "<div style='font-family:sans-serif; padding:20px; color:green;'>";
    echo "<h3> Membuat User Uji Coba!</h3>";
    echo "• Akun Admin -> Username: <b>admin</b> | Password: <b>admin123</b><br>";
    echo "• Akun Kasir -> Username: <b>kasir</b> | Password: <b>kasir123</b><br><br>";
    echo "<i>Silakan kembali ke halaman <a href='login.php'>login.php</a> dan coba masuk. Jangan lupa hapus file ini demi keamanan.</i>";
    echo "</div>";

} catch (PDOException $e) {
    die("Gagal generate user: " . $e->getMessage());
}