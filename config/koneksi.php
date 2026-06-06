<?php
// ============================================
// config/koneksi.php
// File koneksi ke database MySQL
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Ganti sesuai user MySQL kamu
define('DB_PASS', '');           // Ganti sesuai password MySQL kamu
define('DB_NAME', 'campus_events');
define('BASE_URL', 'http://localhost/campus_events');

// Buat koneksi ke database
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die('<div style="font-family:sans-serif;padding:20px;color:red;">
        <h3>❌ Koneksi Database Gagal!</h3>
        <p>' . mysqli_connect_error() . '</p>
        <p>Cek pengaturan di file <strong>config/koneksi.php</strong></p>
    </div>');
}

// Set charset ke utf8 agar karakter Indonesia tampil benar
mysqli_set_charset($koneksi, 'utf8mb4');
?>
