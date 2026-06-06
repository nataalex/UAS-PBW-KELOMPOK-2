<?php
// includes/sidebar_admin.php
// Sidebar navigasi untuk halaman admin
$halaman_aktif = $halaman_aktif ?? '';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🎓</div>
        <div>
            <div class="brand-name">Campus Events</div>
            <div class="brand-sub">Admin Portal - <?= htmlspecialchars($_SESSION['organisasi'] ?? 'Pusat') ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>
        <a href="<?= BASE_URL ?>/pages/admin/dashboard.php"
           class="nav-item <?= $halaman_aktif === 'dashboard' ? 'aktif' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="<?= BASE_URL ?>/pages/admin/event.php"
           class="nav-item <?= $halaman_aktif === 'event' ? 'aktif' : '' ?>">
            <span class="nav-icon">📅</span> Kelola Event
        </a>
        <a href="<?= BASE_URL ?>/pages/admin/registrasi.php"
           class="nav-item <?= $halaman_aktif === 'registrasi' ? 'aktif' : '' ?>">
            <span class="nav-icon">📋</span> Registrasi
        </a>
        <a href="<?= BASE_URL ?>/pages/admin/kategori.php"
           class="nav-item <?= $halaman_aktif === 'kategori' ? 'aktif' : '' ?>">
            <span class="nav-icon">🏷️</span> Kategori
        </a>
        <a href="<?= BASE_URL ?>/pages/admin/ruangan.php"
           class="nav-item <?= $halaman_aktif === 'ruangan' ? 'aktif' : '' ?>">
            <span class="nav-icon">🏛️</span> Ruangan
        </a>

        <div class="nav-label" style="margin-top:12px">Pengguna</div>
        <a href="<?= BASE_URL ?>/pages/admin/mahasiswa.php"
           class="nav-item <?= $halaman_aktif === 'mahasiswa' ? 'aktif' : '' ?>">
            <span class="nav-icon">👤</span> Data Mahasiswa
        </a>

        <div class="nav-divider"></div>
        <a href="<?= BASE_URL ?>/logout.php" class="nav-item nav-logout">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>
</div>
