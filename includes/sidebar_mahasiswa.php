<?php
// includes/sidebar_mahasiswa.php
$halaman_aktif = $halaman_aktif ?? '';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🎓</div>
        <div>
            <div class="brand-name">Campus Events</div>
            <div class="brand-sub">Portal Mahasiswa</div>
        </div>
    </div>

    <!-- Profil singkat mahasiswa -->
    <div class="sidebar-profil">
        <div class="profil-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'M', 0, 1)) ?></div>
        <div>
            <div class="profil-nama"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?></div>
            <div class="profil-nim"><?= htmlspecialchars($_SESSION['nim'] ?? '') ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu</div>
        <a href="<?= BASE_URL ?>/pages/mahasiswa/dashboard.php"
           class="nav-item <?= $halaman_aktif === 'dashboard' ? 'aktif' : '' ?>">
            <span class="nav-icon">🏠</span> Beranda
        </a>
        <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php"
           class="nav-item <?= $halaman_aktif === 'event' ? 'aktif' : '' ?>">
            <span class="nav-icon">🔍</span> Jelajahi Event
        </a>
        <a href="<?= BASE_URL ?>/pages/mahasiswa/registrasi_saya.php"
           class="nav-item <?= $halaman_aktif === 'registrasi' ? 'aktif' : '' ?>">
            <span class="nav-icon">📋</span> Registrasi Saya
        </a>
        <a href="<?= BASE_URL ?>/pages/mahasiswa/profil.php"
           class="nav-item <?= $halaman_aktif === 'profil' ? 'aktif' : '' ?>">
            <span class="nav-icon">⚙️</span> Profil Saya
        </a>

        <div class="nav-divider"></div>
        <a href="<?= BASE_URL ?>/logout.php" class="nav-item nav-logout">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>
</div>
