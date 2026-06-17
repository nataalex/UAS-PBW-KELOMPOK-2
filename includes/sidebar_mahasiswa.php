<?php
// includes/sidebar_mahasiswa.php
$halaman_aktif = $halaman_aktif ?? '';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </div>
        <div class="brand-text">
            <div class="brand-name">Campus Events</div>
            <div class="brand-sub">Portal Mahasiswa</div>
        </div>
    </div>

    <div class="sidebar-profil">
        <div class="profil-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'M', 0, 1)) ?></div>
        <div class="profil-info">
            <div class="profil-nama"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? '') ?></div>
            <div class="profil-nim"><?= htmlspecialchars($_SESSION['nim'] ?? '') ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>
        
        <a href="<?= BASE_URL ?>/pages/mahasiswa/dashboard.php"
           class="nav-item <?= $halaman_aktif === 'dashboard' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span> 
            Beranda
        </a>
        
        <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php"
           class="nav-item <?= $halaman_aktif === 'event' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </span> 
            Jelajahi Event
        </a>
        
        <a href="<?= BASE_URL ?>/pages/mahasiswa/registrasi_saya.php"
           class="nav-item <?= $halaman_aktif === 'registrasi' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
            </span> 
            Registrasi Saya
        </a>
        
        <a href="<?= BASE_URL ?>/pages/mahasiswa/profil.php"
           class="nav-item <?= $halaman_aktif === 'profil' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span> 
            Profil Saya
        </a>

        <div class="nav-divider"></div>
        
        <a href="<?= BASE_URL ?>/logout.php" class="nav-item nav-logout">
            <span class="nav-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            </span> 
            Logout
        </a>
    </nav>
</div>