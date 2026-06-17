<?php
// ============================================================
// logout.php — Proses Logout Aman & Pengalihan ke login.php
// ============================================================
session_start();

// Panggil koneksi untuk mendapatkan konstanta BASE_URL
require_once 'config/koneksi.php';

// Hapus seluruh data session login
$_SESSION = array();

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Alihkan kembali ke login.php dengan membawa pesan sukses logout
header('Location: ' . BASE_URL . '/login.php?logout=1');
exit;
?>