<?php
// config/database.php

$host    = 'localhost';
$db      = 'ssyycoffee'; // Pastikan nama database di MySQL sudah kamu buat dengan nama ini
$user    = 'root';
$pass    = '';           // Kosongkan jika pakai XAMPP default, atau sesuaikan dengan setup databasemu
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Menampilkan error SQL secara detail untuk mempermudah debugging
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query otomatis menjadi array asosiatif (nama_kolom => nilai)
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan prepared statements asli dari MySQL untuk keamanan maksimal
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Jika koneksi gagal, hentikan aplikasi dan tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}