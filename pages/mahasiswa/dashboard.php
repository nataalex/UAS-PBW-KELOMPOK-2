<?php
// pages/mahasiswa/dashboard.php — Dashboard Mahasiswa
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibMahasiswa();

$judul_halaman = 'Beranda';
$halaman_aktif = 'dashboard';
$id_mhs = (int)$_SESSION['id_mahasiswa'];

$total_reg = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi WHERE id_mahasiswa=$id_mhs"))['total'];

$total_confirmed = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi WHERE id_mahasiswa=$id_mhs AND status='confirmed'"))['total'];

$total_pending = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi WHERE id_mahasiswa=$id_mhs AND status='pending'"))['total'];

$event_mendatang = mysqli_query($koneksi,
    "SELECT e.*, k.nama_kategori, r.nama_ruangan,
            COUNT(reg.id_registrasi) as jumlah_daftar
     FROM event e
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     JOIN ruangan r ON e.id_ruangan = r.id_ruangan
     LEFT JOIN registrasi reg ON e.id_event = reg.id_event AND reg.status != 'cancelled'
     WHERE e.status='published' AND e.tanggal_mulai >= CURDATE()
     GROUP BY e.id_event
     ORDER BY e.tanggal_mulai ASC
     LIMIT 4"
);

$reg_saya = mysqli_query($koneksi,
    "SELECT r.*, e.nama_event, e.tanggal_mulai, e.waktu_mulai, k.nama_kategori
     FROM registrasi r
     JOIN event e ON r.id_event = e.id_event
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     WHERE r.id_mahasiswa=$id_mhs
     ORDER BY r.id_registrasi DESC
     LIMIT 5"
);

// Mengambil nama depan dan inisial untuk avatar
$nama_lengkap = htmlspecialchars($_SESSION['nama_lengkap']);
$nama_depan = explode(' ', $nama_lengkap)[0];
$inisial = strtoupper(substr($nama_depan, 0, 1));
?>
<?php include '../../includes/header.php'; ?>

<style>
:root {
    --primary: #2563EB;
    --primary-dark: #1D4ED8;
    --primary-light: #60A5FA;
    --secondary: #38BDF8;
    --success: #10B981;
    --warning: #F59E0B;
    --danger: #EF4444;
    --dark: #0F172A;
    --gray: #64748B;
    --light-gray: #E2E8F0;
    --bg: #F8FAFC;
    --white: #FFFFFF;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
    --radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    box-sizing: border-box;
}

body {
    background: var(--bg);
    margin: 0;
    padding: 0;
}

.app-wrapper {
    background: var(--bg);
    min-height: 100vh;
    display: flex;
}

.main-content {
    margin-left: 260px;
    width: calc(100% - 260px);
    padding: 40px;
    background: var(--bg);
    min-height: 100vh;
    transition: var(--transition);
}

/* --- PERBAIKAN TOPBAR --- */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 24px;
    margin-bottom: 32px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.btn-menu {
    display: none;
    background: var(--white);
    border: 1px solid var(--light-gray);
    border-radius: 10px;
    padding: 8px 10px;
    cursor: pointer;
    color: var(--dark);
    transition: var(--transition);
}

.btn-menu:hover {
    background: var(--primary);
    color: var(--white);
    border-color: var(--primary);
}

.user-greeting-wrapper {
    display: flex;
    align-items: center;
    gap: 18px;
}

.avatar-initial {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 800;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

.topbar-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--dark);
    letter-spacing: -0.5px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* KUSTOMISASI FONT TIMES NEW ROMAN UNTUK KATA "HALO," */
.greeting-text {
    font-family: 'Times New Roman', Times, serif;
    font-size: 26px;
    font-style: italic; /* Dibuat miring agar lebih elegan */
    font-weight: 700;
    color: var(--dark);
    margin-right: 2px;
}

.topbar-title .highlight {
    color: var(--primary);
}

.wave-emoji {
    display: inline-block;
    animation: wave 2.5s infinite;
    transform-origin: 70% 70%;
}

@keyframes wave {
    0% { transform: rotate( 0.0deg) }
    10% { transform: rotate(14.0deg) }  
    20% { transform: rotate(-8.0deg) }
    30% { transform: rotate(14.0deg) }
    40% { transform: rotate(-4.0deg) }
    50% { transform: rotate(10.0deg) }
    60% { transform: rotate( 0.0deg) }
    100% { transform: rotate( 0.0deg) }
}

.topbar-sub {
    display: flex;
    align-items: center;
    gap: 8px;
}

.badge-nim {
    background: var(--white);
    border: 1px solid var(--light-gray);
    color: var(--gray);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-role {
    background: #DBEAFE;
    color: var(--primary-dark);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.btn-primary-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--primary);
    color: var(--white);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    transition: var(--transition);
}

.btn-primary-premium:hover {
    transform: translateY(-2px);
    background: var(--primary-dark);
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.3);
    color: var(--white);
}

/* --- KONTEN BAWAH --- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid rgba(15, 23, 42, 0.03);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon.primary { background: #DBEAFE; color: var(--primary); }
.stat-icon.success { background: #D1FAE5; color: var(--success); }
.stat-icon.warning { background: #FEF3C7; color: var(--warning); }

.stat-number {
    font-size: 28px;
    font-weight: 800;
    color: var(--dark);
    line-height: 1;
    margin-bottom: 6px;
}

.stat-label {
    font-size: 13px;
    color: var(--gray);
    font-weight: 500;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.card {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid rgba(15, 23, 42, 0.03);
}

.card:hover {
    box-shadow: var(--shadow-hover);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 16px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.03);
}

.card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title .icon {
    color: var(--primary);
    display: flex;
    align-items: center;
}

.btn-link {
    font-size: 13px;
    font-weight: 600;
    color: var(--primary);
    text-decoration: none;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-link:hover {
    color: var(--primary-dark);
    transform: translateX(4px);
}

.card-body {
    padding: 20px 24px 24px 24px;
}

.event-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.event-item {
    padding: 16px;
    border: 1px solid var(--light-gray);
    border-radius: 12px;
    background: var(--white);
    transition: var(--transition);
    cursor: pointer;
    text-decoration: none;
    display: block;
}

.event-item:hover {
    border-color: var(--primary-light);
    background: #F8FAFC;
    transform: translateX(4px);
}

.event-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--dark);
    margin-bottom: 6px;
}

.event-meta {
    font-size: 12px;
    color: var(--gray);
    font-weight: 500;
}

.event-status {
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.table-wrapper {
    overflow-x: auto;
}

.table-wrapper table {
    width: 100%;
    border-collapse: collapse;
}

.table-wrapper th {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--gray);
    padding: 12px 16px;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--light-gray);
    text-align: left;
}

.table-wrapper td {
    padding: 14px 16px;
    font-size: 13px;
    color: var(--dark);
    border-bottom: 1px solid rgba(15, 23, 42, 0.03);
    vertical-align: middle;
}

.table-wrapper tr:last-child td {
    border-bottom: none;
}

.badge-status {
    display: inline-flex;
    align-items: center;
    padding: 4px 14px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: capitalize;
    border: none;
}

.badge-confirmed { background: #D1FAE5; color: #065F46; }
.badge-pending { background: #FEF3C7; color: #92400E; }
.badge-cancelled { background: #FEE2E2; color: #991B1B; }

.empty-state {
    text-align: center;
    padding: 40px 16px;
    color: var(--gray);
    font-size: 14px;
    font-weight: 500;
}

.empty-state a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    margin-top: 8px;
}

@media (max-width: 1024px) {
    .main-content { margin-left: 0; width: 100%; padding: 30px 20px 20px 20px; }
    .btn-menu { display: block; }
    .stats-grid { grid-template-columns: 1fr; gap: 16px; }
    .dashboard-grid { grid-template-columns: 1fr; gap: 20px; }
}

@media (max-width: 640px) {
    .topbar { flex-direction: column; align-items: flex-start; gap: 20px; }
    .btn-primary-premium { width: 100%; justify-content: center; }
    .user-greeting-wrapper { gap: 12px; }
    .avatar-initial { width: 44px; height: 44px; font-size: 18px; }
    .topbar-title { font-size: 20px; }
}
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.6); z-index:90; backdrop-filter:blur(4px);"></div>
    
    <?php include '../../includes/sidebar_mahasiswa.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn-menu" id="btnMenu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
                <div class="user-greeting-wrapper">
                    <div class="avatar-initial">
                        <?= $inisial ?>
                    </div>
                    <div>
                        <div class="topbar-title">
                            <span class="greeting-text">Halo,</span> 
                            <span class="highlight"><?= $nama_depan ?></span> 
                            <span class="wave-emoji">🍿</span>
                        </div>
                        <div class="topbar-sub">
                            <span class="badge-nim">NIM: <?= htmlspecialchars($_SESSION['nim']) ?></span>
                            <span class="badge-role">Mahasiswa</span>
                        </div>
                    </div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php" class="btn-primary-premium">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Jelajahi Event
            </a>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <div>
                        <div class="stat-number"><?= $total_reg ?></div>
                        <div class="stat-label">Total Registrasi</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <div class="stat-number"><?= $total_confirmed ?></div>
                        <div class="stat-label">Terkonfirmasi</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="stat-number"><?= $total_pending ?></div>
                        <div class="stat-label">Menunggu Konfirmasi</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">
                            <span class="icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            </span> 
                            Event Mendatang
                        </span>
                        <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php" class="btn-link">Lihat Semua &rarr;</a>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($event_mendatang) === 0): ?>
                            <div class="empty-state">
                                Tidak ada event mendatang saat ini.
                            </div>
                        <?php else: ?>
                            <div class="event-list">
                                <?php while ($row = mysqli_fetch_assoc($event_mendatang)): ?>
                                    <div class="event-item">
                                        <div class="event-name"><?= htmlspecialchars($row['nama_event']) ?></div>
                                        <div class="event-meta">
                                            <?= formatTanggal($row['tanggal_mulai']) ?>  •  <?= htmlspecialchars($row['nama_kategori']) ?>
                                        </div>
                                        <?php $sisa = $row['kapasitas'] - $row['jumlah_daftar']; ?>
                                        <div class="event-status" style="color: <?= $sisa <= 0 ? 'var(--danger)' : 'var(--success)' ?>;">
                                            <?= $sisa <= 0 ? 'Kuota Penuh' : "Tersisa $sisa kursi" ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">
                            <span class="icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            </span> 
                            Registrasi Terbaru
                        </span>
                        <a href="<?= BASE_URL ?>/pages/mahasiswa/registrasi_saya.php" class="btn-link">Lihat Semua &rarr;</a>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Tanggal</th>
                                        <th style="text-align: center;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($reg_saya) === 0): ?>
                                    <tr>
                                        <td colspan="3" class="empty-state">
                                            Belum ada riwayat registrasi.<br>
                                            <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php">Mulai Daftar Event &rarr;</a>
                                        </td>
                                    </tr>
                                    <?php else: while ($row = mysqli_fetch_assoc($reg_saya)): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight:600; color:var(--dark);"><?= htmlspecialchars($row['nama_event']) ?></div>
                                            <small style="color:var(--gray); font-size:11px;"><?= htmlspecialchars($row['nama_kategori']) ?></small>
                                        </td>
                                        <td>
                                            <span style="font-weight:500;"><?= formatTanggal($row['tanggal_mulai']) ?></span>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php 
                                                $status_class = $row['status'] === 'confirmed' ? 'badge-confirmed' : 
                                                               ($row['status'] === 'pending' ? 'badge-pending' : 'badge-cancelled');
                                            ?>
                                            <span class="badge-status <?= $status_class ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
            sidebar.classList.add('open');
            overlay.style.display = 'block';
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.style.display = 'none';
        });
    }
</script>

<?php include '../../includes/footer.php'; ?>