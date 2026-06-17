<?php
// ============================================
// config/koneksi.php
// File koneksi ke database MySQL (Auto-Fix Version)
// ============================================

// 1. OTOMATIS AKTIFKAN ERROR REPORTING (Agar jika ada error di halaman lain langsung muncul teksnya, bukan blank)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. DEFINISI KONSTANTA DATABASE
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // User default MySQL XAMPP
define('DB_PASS', '');           // Password default MySQL XAMPP (kosong)
define('DB_NAME', 'campus_events');

// Menggunakan port default 3306, jika database kamu menggunakan port kustom (seperti 3307), ubah angka di bawah ini
define('DB_PORT', 3306); 

// 3. DINAMIS BASE_URL (Otomatis mendeteksi domain/localhost dan port server beralih tanpa hardcode manual)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . $domainName . '/campus_events');

// 4. PROSES KONEKSI KE DATABASE (Ditambahkan parameter Port untuk stabilitas sistem)
$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek apakah koneksi berhasil atau gagal
if (!$koneksi) {
    die('<div style="font-family:sans-serif;padding:35px;color:#b91c1c;background:#fef2f2;border:2px solid #f87171;max-width:650px;margin:50px auto;border-radius:12px;box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h3 style="margin-top:0;font-size:20px;display:flex;align-items:center;gap:8px;">❌ Koneksi Database Gagal Terhubung!</h3>
        <p style="margin:10px 0;line-height:1.6;"><strong>Pesan Sistem Error:</strong> <code style="background:#fee2e2;padding:2px 6px;border-radius:4px;font-family:monospace;">' . mysqli_connect_error() . '</code></p>
        <p style="margin:10px 0;color:#4b5563;">Silakan periksa konfigurasi database kamu di XAMPP/phpMyAdmin. Pastikan database bernama <strong>' . DB_NAME . '</strong> sudah dibuat dan file <strong>db.sql</strong> sudah diimport dengan benar.</p>
    </div>');
}

// 5. SET CHARSET KE UTF8MB4 (Sesuai dengan penataan collation utf8mb4_unicode_ci di db.sql kamu)
mysqli_set_charset($koneksi, 'utf8mb4');
?>