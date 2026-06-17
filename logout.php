<?php
// ============================================
// logout.php — Proses Logout Aman Berbasis BASE_URL
// ============================================
session_start();

// Load file koneksi untuk mendapatkan nilai konstanta BASE_URL resmi
require_once 'config/koneksi.php';

// Hapus seluruh data session login
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect kembali ke root index menggunakan BASE_URL konkrit
header('Location: ' . BASE_URL . '/index.php?logout=1');
exit;
?>