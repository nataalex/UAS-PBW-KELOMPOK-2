<?php
// includes/sidebar_admin.php
// Sidebar navigasi untuk halaman admin — FULL Royal Blue UNSIKA (Enhanced Contrast)
$halaman_aktif = $halaman_aktif ?? '';
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --royal-blue: #1d4ed8;         /* Biru utama dari image_26ebc7.png */
        --text-light: #ffffff;         /* Teks utama putih cerah */
        --text-ghost: rgba(255, 255, 255, 0.75); /* Teks menu normal (lebih terang & jelas) */
        --text-label: rgba(255, 255, 255, 0.85); /* Teks Kategori Menu (Sangat Jelas & Kontras) */
    }

    /* SIDEBAR CONTAINER */
    .sidebar {
        width: 260px;
        height: 100vh;
        background: linear-gradient(180deg, #1e40af 0%, var(--royal-blue) 60%, #2563eb 100%);
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        flex-direction: column;
        padding: 24px 16px;
        position: fixed;
        left: 0;
        top: 0;
        box-shadow: 4px 0 24px rgba(15, 23, 42, 0.15);
        box-sizing: border-box;
        z-index: 999;
    }

    .sidebar * {
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    /* BRANDING SECTION */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        margin-bottom: 32px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        padding-bottom: 20px;
    }

    /* Box Icon Topi Toga ala image_2673fd.png */
    .brand-icon-box {
        width: 42px;
        height: 42px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .brand-icon-box svg {
        width: 24px;
        height: 24px;
        color: #ffffff;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
    }

    .brand-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-light);
        line-height: 1.2;
    }

    .brand-sub {
        font-size: 11px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.8);
        margin-top: 2px;
    }

    /* NAVIGATION CONTAINER */
    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: 6px;
        height: 100%;
    }

    /* PERJELAS TULISAN MENU UTAMA & PENGGUNA DI SINI */
    .nav-label {
        font-size: 11px;
        font-weight: 800; /* Dibikin tebal bold */
        text-transform: uppercase;
        letter-spacing: 1.2px; /* Kasih jarak antar huruf biar tegas */
        color: var(--text-label); /* Putih kontras tinggi */
        padding: 16px 12px 6px 12px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15); /* Bayangan halus biar teks makin timbul */
    }

    /* NAV ITEMS (Tombol Menu) */
    .nav-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px;
        color: var(--text-ghost);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    /* Icon Styling */
    .nav-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
    }
    
    .nav-icon svg {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
        transition: transform 0.2s;
    }

    /* HOVER STATE */
    .nav-item:hover {
        background-color: rgba(255, 255, 255, 0.12);
        color: var(--text-light);
    }

    .nav-item:hover .nav-icon svg {
        transform: translateX(2px);
    }

    /* ACTIVE STATE (Sama persis seperti image_2673fd.png) */
    .nav-item.aktif {
        background-color: #ffffff; 
        color: var(--royal-blue);  
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .nav-item.aktif .nav-icon svg {
        color: var(--royal-blue);
        stroke-width: 2.5;
    }

    /* PERJELAS LOGOUT DI SINI */
    .nav-item.nav-logout {
        margin-top: auto; /* Maksa nempel ke paling bawah */
        color: #ffffff; /* Default langsung putih cerah biar kelihatan jelas */
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background-color: rgba(255, 255, 255, 0.05);
    }

    /* Pas di-hover berubah jadi merah tegas */
    .nav-item.nav-logout:hover {
        background-color: #ef4444; 
        color: #ffffff;
        border-color: #ef4444;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
    }

    .nav-divider {
        height: 1px;
        background-color: rgba(255, 255, 255, 0.15);
        margin: 12px 0;
    }
</style>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon-box">
            <svg viewBox="0 0 24 24">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
            </svg>
        </div>
        <div>
            <div class="brand-name">Campus Events</div>
            <div class="brand-sub">Admin Portal - <?= htmlspecialchars($_SESSION['organisasi'] ?? 'BEM') ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>
        
        <a href="<?= BASE_URL ?>/pages/admin/dashboard.php"
           class="nav-item <?= $halaman_aktif === 'dashboard' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </span> 
            Dashboard
        </a>
        
        <a href="<?= BASE_URL ?>/pages/admin/event.php"
           class="nav-item <?= $halaman_aktif === 'event' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span> 
            Kelola Event
        </a>
        
        <a href="<?= BASE_URL ?>/pages/admin/registrasi.php"
           class="nav-item <?= $halaman_aktif === 'registrasi' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </span> 
            Registrasi
        </a>
        
        <a href="<?= BASE_URL ?>/pages/admin/kategori.php"
           class="nav-item <?= $halaman_aktif === 'kategori' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span> 
            Kategori
        </a>
        
        <a href="<?= BASE_URL ?>/pages/admin/ruangan.php"
           class="nav-item <?= $halaman_aktif === 'ruangan' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span> 
            Ruangan
        </a>

        <div class="nav-label" style="margin-top:12px">Pengguna</div>
        
        <a href="<?= BASE_URL ?>/pages/admin/mahasiswa.php"
           class="nav-item <?= $halaman_aktif === 'mahasiswa' ? 'aktif' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span> 
            Data Mahasiswa
        </a>

        <div class="nav-divider"></div>
        
        <a href="<?= BASE_URL ?>/logout.php" class="nav-item nav-logout">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </span> 
            Logout
        </a>
    </nav>
</div>