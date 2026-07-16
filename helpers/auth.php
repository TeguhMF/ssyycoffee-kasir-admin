<?php
// helpers/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi untuk mengamankan halaman.
 * Memastikan user sudah login dan memiliki role yang sesuai.
 * 
 * @param array $allowed_roles Role yang diperbolehkan mengakses halaman (e.g., ['admin', 'cashier'])
 */
function check_access($allowed_roles = []) {
    // 1. Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        // Jika belum login, tendang ke halaman login di root
        header('Location: http://localhost/ssyycoffee/login.php');
        exit;
    }

    // 2. Cek apakah role user ada di dalam daftar role yang diizinkan
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        // Jika role tidak sesuai, lempar ke halaman default masing-masing role
        if ($_SESSION['role'] === 'admin') {
            header('Location: http://localhost/ssyycoffee/views/admin/dashboard.php');
        } else {
            header('Location: http://localhost/ssyycoffee/views/cashier/index.php');
        }
        exit;
    }
}