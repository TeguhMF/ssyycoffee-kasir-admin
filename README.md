# Sistem Point of Sale (POS) SsyyCoffee

## Ringkasan Proyek
SsyyCoffee adalah aplikasi web Point of Sale (POS) *full-stack* yang tangguh, dirancang khusus untuk operasional kedai kopi. Dikembangkan menggunakan PHP *native* dan Tailwind CSS, sistem ini memfasilitasi pemrosesan transaksi yang mulus, manajemen varian produk dinamis, pelaporan pendapatan komprehensif, dan program loyalitas pelanggan yang terintegrasi.

## Fitur Utama
*   **Antarmuka POS Interaktif:** Operasi keranjang asinkron menggunakan Vanilla JavaScript (Fetch API) untuk pembaruan *state* secara *real-time* tanpa memuat ulang halaman (*page reload*).
*   **Manajemen Varian Dinamis:** Dukungan untuk opsi produk bertingkat (misalnya, ukuran, tingkat kemanisan) dengan penyesuaian harga otomatis.
*   **Modul Loyalitas Pelanggan:** Sistem akumulasi poin otomatis (50 poin per item) dengan penukaran hadiah berbasis pencapaian (batas 10.000 poin untuk diskon otomatis).
*   **Role-Based Access Control (RBAC):** Pemisahan hak akses yang membagi peran 'Admin' (manajemen penuh dan analitik) dan 'Kasir' (operasi transaksional).
*   **Pelaporan & Analitik:** Perekapan pendapatan yang dapat disaring berdasarkan rentang tanggal dan pelacakan riwayat transaksi.
*   **Integrasi Cetak Termal:** Format pembuatan struk yang dioptimalkan secara khusus untuk printer termal POS.

## Tumpukan Teknologi (Tech Stack)
*   **Backend:** PHP 8.x (Native)
*   **Database:** MySQL / MariaDB
*   **Frontend:** HTML5, CSS3, Tailwind CSS
*   **Scripting:** Vanilla JavaScript (ES6+ Promises/Async-Await)

## Arsitektur Teknis & Sorotan

### 1. Manajemen State Berbasis API
Antarmuka kasir beroperasi layaknya *quasi-Single Page Application* (SPA). *State* keranjang dipertahankan di server melalui PHP Sessions, sementara komunikasi klien-server ditangani secara asinkron melalui *endpoint* RESTful internal (`/api/cart-handler.php` dan `/api/order-handler.php`). Respons diformat secara ketat sebagai JSON.

### 2. Desain Database Relasional
Sistem ini menggunakan skema database relasional yang dinormalisasi. Relasi utama meliputi:
*   `orders` (1:N) `order_details`: Melacak detail item per transaksi.
*   `order_details` (1:N) `order_detail_variants`: Melacak konfigurasi spesifik untuk setiap item.
*   `customers` (1:N) `orders`: Menetapkan riwayat pembelian dan menghitung agregat poin loyalitas.

### 3. Kepatuhan ACID melalui Transaksi Database
Untuk memastikan integritas data selama proses *checkout*, sistem ini memanfaatkan Transaksi Database PDO. Penyisipan data pesanan, detail pesanan, detail varian, dan pembaruan poin loyalitas pelanggan dienkapsulasi dalam satu blok transaksi (`beginTransaction`, `commit`, `rollBack`). Jika ada kueri yang gagal, seluruh *state* akan dikembalikan (*revert*), mencegah adanya data yatim (*orphaned records*).

### 4. Langkah Keamanan
*   **Pencegahan Injeksi SQL:** Semua interaksi database secara ketat menggunakan *PDO Prepared Statements* dengan kueri berparameter.
*   **Autentikasi & Kriptografi:** Kata sandi di-*hash* secara kriptografis menggunakan fungsi bawaan PHP `password_hash()` yang mengimplementasikan algoritma Bcrypt.
*   **Mitigasi Pembajakan Sesi (Session Hijacking):** Rute yang dilindungi dijaga oleh *middleware* validasi sesi (`helpers/auth.php`) untuk memastikan akses yang tidak sah segera dialihkan.

## Panduan Instalasi & Pengaturan

### Prasyarat
*   Web server yang menjalankan Apache/Nginx (misalnya, XAMPP, MAMP, Laragon).
*   PHP versi 7.4 atau lebih tinggi (direkomendasikan 8.x).
*   MySQL Server.

### Langkah Instalasi
1.  **Clone repositori:**
    ```bash
    git clone [https://github.com/TeguhMF/ssyycoffee-kasir-admin.git](https://github.com/TeguhMF/ssyycoffee-kasir-admin.git)
    cd ssyycoffee-kasir-admin
    ```
2.  **Konfigurasi Database:**
    *   Buat database baru di server MySQL Anda dengan nama `ssyycoffee`.
    *   Impor file *dump* database yang disediakan (`ssyycoffee.sql`) ke dalam database yang baru dibuat.
3.  **Pengaturan Lingkungan (Environment):**
    *   Navigasi ke direktori `config/database.php`.
    *   Verifikasi dan modifikasi kredensial database (`$host`, `$dbname`, `$username`, `$password`) agar sesuai dengan konfigurasi lingkungan lokal Anda.
4.  **Eksekusi:**
    *   Jalankan direktori proyek melalui web server lokal Anda.
    *   Akses aplikasi melalui web browser (misalnya, `http://localhost/ssyycoffee`).

## Penulis
Dikembangkan oleh **TeguhMF**