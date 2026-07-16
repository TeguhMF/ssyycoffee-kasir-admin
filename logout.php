<?php
// logout.php
session_start();

// Hapus semua data di session
$_SESSION = array();

// Hancurkan session di server
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Tendang kembali ke halaman login
header("Location: login.php");
exit;