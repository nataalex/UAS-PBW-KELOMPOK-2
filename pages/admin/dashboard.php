<?php
// ========================================================================
// pages/admin/dashboard.php — Dashboard Admin (Fixed Overlap Bug)
// ========================================================================

// ========================================================================
// 1. PENGATURAN AWAL & AUTENTIKASI
// ========================================================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';

// Memastikan hanya Admin yang sudah login yang bisa mengakses halaman ini
wajibAdmin();

$judul_halaman = 'Dashboard';
$halaman_aktif = 'dashboard';

// ========================================================================
// 2. MENGAMBIL DATA STATISTIK UTAMA (Untuk Kartu Indikator)
// ========================================================================
$total_event = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM event"))['total'];

$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM profil_mahasiswa"))['total'];

$total_registrasi = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi"))['total'];

$total_confirmed = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi WHERE status = 'confirmed'"))['total'];

$total_kategori = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM kategori_event"))['total'];

$total_ruangan = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM ruangan"))['total'];

// ========================================================================
// 3. MENGAMBIL DATA TABEL (Event & Registrasi Terbaru)
// ========================================================================
$event_terbaru = mysqli_query($koneksi,
    "SELECT e.*, k.nama_kategori, r.nama_ruangan,
            COUNT(reg.id_registrasi) as jumlah_daftar
     FROM event e
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     JOIN ruangan r ON e.id_ruangan = r.id_ruangan
     LEFT JOIN registrasi reg ON e.id_event = reg.id_event AND reg.status != 'cancelled'
     GROUP BY e.id_event
     ORDER BY e.created_at DESC
     LIMIT 5"
);

$reg_terbaru = mysqli_query($koneksi,
    "SELECT r.*, pm.nama_lengkap, pm.nim, e.nama_event
     FROM registrasi r
     JOIN profil_mahasiswa pm ON r.id_mahasiswa = pm.id_mahasiswa
     JOIN event e ON r.id_event = e.id_event
     ORDER BY r.tanggal_registrasi DESC, r.id_registrasi DESC
     LIMIT 7"
);
?>

<?php include '../../includes/header.php'; ?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --royal-blue: #1d4ed8;       
        --deep-blue: #1e40af;        
        --bg-dashboard: #f8fafc;     
        --text-dark: #0f172a;        
        --text-muted: #64748b;       
        --border-color: #e2e8f0;     
    }

    body {
        background-color: var(--bg-dashboard);
        margin: 0;
    }

    .app-wrapper {
        background-color: var(--bg-dashboard);
        min-height: 100vh;
        display: flex;
    }

    .main-content {
        margin-left: 260px; 
        width: calc(100% - 260px);
        padding: 40px; 
        background-color: var(--bg-dashboard);
        min-height: 100vh;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    .main-content * {
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    /* --- TOPBAR AREA (KODE YANG DIPERBAIKI UNTUK MENCEGAH OVERLAP) --- */
    .topbar {
        position: relative !important; /* Memaksa topbar tidak melayang */
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important; 
        padding-bottom: 24px !important; 
        margin-bottom: 32px !important;  
        border-bottom: 2px solid var(--border-color) !important; 
        background: transparent !important;
        width: 100% !important;
        height: auto !important; /* Mereset tinggi jika sebelumnya di-set dari header.php */
        z-index: 10 !important;
    }

    .topbar-judul {
        font-size: 28px; 
        font-weight: 800; 
        color: var(--text-dark);
        letter-spacing: -0.75px;
        line-height: 1.2;
    }

    .topbar-sub {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 6px;
        font-weight: 500;
    }

    .btn.btn-utama.btn-sm {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--deep-blue) 100%);
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        border-radius: 12px; 
        border: none;
        box-shadow: 0 4px 14px rgba(29, 78, 216, 0.25);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn.btn-utama.btn-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(29, 78, 216, 0.4);
    }

    /* --- KARTU STATISTIK GRID --- */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px rgba(15, 23, 42, 0.06);
    }

    .stat-ikon {
        width: 60px; 
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 800; 
    }

    .stat-label {
        font-size: 15px; 
        color: var(--text-dark);
        font-weight: 600;
    }

    /* --- LAYOUT DUA KOLOM DATA --- */
    .premium-data-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 24px;
        margin-bottom: 32px;
        align-items: start;
    }

    .card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
        background: transparent !important;
    }

    .card-judul {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .btn-abu {
        font-size: 12px;
        font-weight: 600;
        color: var(--royal-blue);
        text-decoration: none;
        padding: 6px 14px;
        background: #eff6ff;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .btn-abu:hover {
        background: var(--royal-blue);
        color: #ffffff;
    }

    .tabel-wrapper { overflow-x: auto; }
    .tabel-wrapper table { width: 100%; border-collapse: collapse; text-align: left; }
    .tabel-wrapper th {
        font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);
        padding: 10px 8px; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9;
    }
    .tabel-wrapper td {
        padding: 14px 8px; font-size: 13.5px; color: var(--text-dark);
        border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .tabel-wrapper tr:last-child td { border-bottom: none; }
    .tabel-wrapper tbody tr:hover { background-color: #f8fafc; }

    .badge, [class*="badge-"], [class*="status-"] {
        display: inline-flex !important; align-items: center; padding: 4px 10px !important;
        font-size: 11px !important; font-weight: 600 !important; border-radius: 8px !important;
        text-transform: capitalize !important; border: none !important;
    }
    .badge-success, .status-published, .status-confirmed, .badge-aktif { background: #d1fae5 !important; color: #065f46 !important; }
    .badge-warning, .status-draft, .badge-pending { background: #fef3c7 !important; color: #92400e !important; }
    .badge-danger, .status-cancelled { background: #fee2e2 !important; color: #991b1b !important; }

    /* --- INFO RINGKAS GRID (3 KOLOM BAWAH) --- */
    .info-ringkas-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

    .card-body-premium {
        background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px;
        padding: 24px; display: flex; flex-direction: column; align-items: center; text-align: center;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01); transition: transform 0.2s;
    }
    .card-body-premium:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(15, 23, 42, 0.06); }

    .icon-circle-container {
        width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; font-size: 26px; font-weight: 800; margin-bottom: 12px;
    }

    .label-bawah { font-size: 14px; color: var(--text-dark); font-weight: 600; margin: 4px 0 16px 0; }

    .btn-action-small {
        padding: 8px 20px; font-size: 12px; font-weight: 600; color: #475569; background: #f1f5f9;
        text-decoration: none; border-radius: 8px; transition: all 0.2s; width: 100%; max-width: 120px;
    }
    .btn-action-small:hover { background: var(--royal-blue); color: #ffffff; }

    .empty-state { text-align: center; padding: 40px 20px !important; color: var(--text-muted); font-size: 13px; font-weight: 500; }

    .btn-menu {
        display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-dark); margin-right: 14px; padding: 0;
    }

    /* RESPONSIVE LAYOUT */
    @media (max-width: 1200px) {
        .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .premium-data-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 1024px) {
        .main-content { margin-left: 0; width: 100%; padding: 40px 24px 24px 24px; }
        .btn-menu { display: block; }
    }
    @media (max-width: 640px) {
        .stat-grid { grid-template-columns: 1fr; }
        .info-ringkas-grid { grid-template-columns: 1fr; }
        .topbar { flex-direction: column; align-items: flex-start; gap: 16px; }
        .btn.btn-utama.btn-sm { width: 100%; justify-content: center; }
    }
</style>

<div class="app-wrapper">
    
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>
    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        
        <div class="topbar">
            <div class="topbar-kiri" style="display:flex; align-items:center;">
                <button class="btn-menu" id="btnMenu"></button>
                <div>
                    <div class="topbar-judul">Dashboard</div>
                    <div class="topbar-sub">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</div>
                </div>
            </div>
            
            <div class="topbar-kanan">
                <a href="<?= BASE_URL ?>/pages/admin/event.php?aksi=tambah" class="btn btn-utama btn-sm">
                    <svg style="width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:3;" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Buat Event
                </a>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#eff6ff; color: var(--royal-blue)">
                        <?= $total_event ?>
                    </div>
                    <div>
                        <div class="stat-label">Total Event</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#ecfdf5; color: #059669">
                        <?= $total_mahasiswa ?>
                    </div>
                    <div>
                        <div class="stat-label">Mahasiswa Terdaftar</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#fffbeb; color: #d97706">
                        <?= $total_registrasi ?>
                    </div>
                    <div>
                        <div class="stat-label">Total Registrasi</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#f5f3ff; color: #7c3aed">
                        <?= $total_confirmed ?>
                    </div>
                    <div>
                        <div class="stat-label">Registrasi Confirmed</div>
                    </div>
                </div>
            </div>

            <div class="premium-data-grid">

                <div class="card">
                    <div class="card-header">
                        <span class="card-judul"> Event Terbaru</span>
                        <a href="<?= BASE_URL ?>/pages/admin/event.php" class="btn-abu">Lihat Semua</a>
                    </div>
                    <div class="tabel-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Event</th>
                                    <th>Kategori</th>
                                    <th>Pendaftar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($event_terbaru) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($event_terbaru)): ?>
                                    <tr>
                                        <td>
                                            <span style="font-weight:600; color:var(--text-dark)"><?= htmlspecialchars($row['nama_event']) ?></span><br>
                                            <small style="color:var(--text-muted); font-size:11px"><?= formatTanggal($row['tanggal_mulai']) ?></small>
                                        </td>
                                        <td><small style="font-weight:500; color:#475569"><?= htmlspecialchars($row['nama_kategori']) ?></small></td>
                                        <td><strong><?= $row['jumlah_daftar'] ?></strong><span style="color:#cbd5e1;margin:0 2px">/</span><?= $row['kapasitas'] ?></td>
                                        <td><?= badgeStatus($row['status']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="empty-state">Belum ada data event terbaru.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-judul"> Registrasi Terbaru</span>
                        <a href="<?= BASE_URL ?>/pages/admin/registrasi.php" class="btn-abu">Lihat Semua</a>
                    </div>
                    <div class="tabel-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Event</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($reg_terbaru) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($reg_terbaru)): ?>
                                    <tr>
                                        <td>
                                            <span style="font-weight:600; color:var(--text-dark)"><?= htmlspecialchars($row['nama_lengkap']) ?></span><br>
                                            <small style="color:var(--text-muted); font-size:11px"><?= htmlspecialchars($row['nim']) ?></small>
                                        </td>
                                        <td><small style="font-weight:500; color:#475569"><?= htmlspecialchars(substr($row['nama_event'], 0, 26)) ?><?= strlen($row['nama_event']) > 26 ? '...' : '' ?></small></td>
                                        <td><?= badgeStatus($row['status']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="empty-state">Belum ada registrasi baru masuk.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="info-ringkas-grid">
                
                <div class="card-body-premium">
                    <div class="icon-circle-container" style="background:#fff7ed; color:#ea580c">
                        <?= $total_kategori ?>
                    </div>
                    <div class="label-bawah">Kategori Event</div>
                    <a href="<?= BASE_URL ?>/pages/admin/kategori.php" class="btn-action-small">Kelola</a>
                </div>
                
                <div class="card-body-premium">
                    <div class="icon-circle-container" style="background:#f0fdfa; color:#0d9488">
                        <?= $total_ruangan ?>
                    </div>
                    <div class="label-bawah">Ruangan Tersedia</div>
                    <a href="<?= BASE_URL ?>/pages/admin/ruangan.php" class="btn-action-small">Kelola</a>
                </div>
                
                <div class="card-body-premium">
                    <div class="icon-circle-container" style="background:#e0e7ff; color:#4f46e5">
                        <?= $total_mahasiswa ?>
                    </div>
                    <div class="label-bawah">Total Mahasiswa</div>
                    <a href="<?= BASE_URL ?>/pages/admin/mahasiswa.php" class="btn-action-small">Lihat</a>
                </div>
                
            </div>
        </div>
    </main>
</div>

<script>
    const btnMenu = document.getElementById('btnMenu');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if(btnMenu && sidebar && overlay) {
        btnMenu.addEventListener('click', () => {
            sidebar.style.transform = 'translateX(0)';
            sidebar.style.left = '0';
            overlay.style.display = 'block';
        });

        overlay.addEventListener('click', () => {
            sidebar.style.transform = 'translateX(-100%)';
            overlay.style.display = 'none';
        });
    }
</script>

<?php include '../../includes/footer.php'; ?>