<?php
// ============================================================
// index.php — Gerbang Pengalihan ke Sistem Login Utama
// ============================================================
session_start();
require_once 'config/koneksi.php';
require_once 'includes/functions.php';

// Jika sudah login, cek rolenya dan arahkan ke dashboard masing-masing
if (sudahLogin()) {
    if (getRoleUser() === 'admin') {
        header('Location: ' . BASE_URL . '/pages/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
    }
    exit;
}

// Jika belum login, langsung alihkan ke login.php yang benar
header('Location: ' . BASE_URL . '/login.php');
exit;
?>