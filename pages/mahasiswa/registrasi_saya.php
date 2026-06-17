<?php
// ============================================
// pages/mahasiswa/registrasi_saya.php
// ============================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibMahasiswa();

$judul_halaman = 'Registrasi Saya';
$halaman_aktif = 'registrasi';
$id_mhs = (int)$_SESSION['id_mahasiswa'];

if (isset($_GET['aksi']) && $_GET['aksi'] === 'batal') {
    $id = (int)$_GET['id'];
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_registrasi FROM registrasi WHERE id_registrasi=$id AND id_mahasiswa=$id_mhs"));
    if ($cek) {
        mysqli_query($koneksi, "UPDATE registrasi SET status='cancelled' WHERE id_registrasi=$id");
        redirectDenganPesan('registrasi_saya.php', 'Registrasi berhasil dibatalkan.');
    }
}

$where = "WHERE r.id_mahasiswa=$id_mhs";
$list_reg = mysqli_query($koneksi,
    "SELECT r.*, e.nama_event, e.tanggal_mulai, e.waktu_mulai, e.kapasitas, e.jenis_tiket, e.harga, r.bukti_pembayaran,
            k.nama_kategori, ru.nama_ruangan, e.penyelenggara
     FROM registrasi r
     JOIN event e ON r.id_event = e.id_event
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     JOIN ruangan ru ON e.id_ruangan = ru.id_ruangan
     $where ORDER BY r.tanggal_registrasi DESC, r.id_registrasi DESC"
);
?>
<?php include '../../includes/header.php'; ?>

<style>
:root {
    --primary: #6C63FF;
    --primary-dark: #5A52D5;
    --primary-light: #E0DEFF;
    --secondary: #FF6584;
    --success: #2ECC71;
    --warning: #F1C40F;
    --danger: #E74C3C;
    --dark: #1A1A2E;
    --gray: #636E72;
    --light-gray: #DFE6E9;
    --bg: #F8F9FA;
    --white: #FFFFFF;
    --shadow: 0 4px 6px -1px rgba(108, 99, 255, 0.05), 0 2px 4px -1px rgba(108, 99, 255, 0.03);
    --shadow-hover: 0 10px 25px rgba(108, 99, 255, 0.1);
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

/* --- TOPBAR --- */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 24px;
    margin-bottom: 32px;
    border-bottom: 1px solid rgba(108, 99, 255, 0.1);
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

.topbar-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--dark);
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.topbar-title .icon {
    color: var(--primary);
}

.topbar-title .highlight {
    color: var(--primary);
}

.topbar-sub {
    font-size: 14px;
    color: var(--gray);
    font-weight: 500;
    margin-top: 4px;
}

/* --- KARTU UTAMA --- */
.card {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid rgba(108, 99, 255, 0.04);
}

.card-body {
    padding: 24px;
}

/* --- LIST REGISTRASI --- */
.registration-item {
    border: 1px solid var(--light-gray);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 16px;
    transition: var(--transition);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    background: var(--white);
}

.registration-item:hover {
    border-color: var(--primary-light);
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.registration-item:last-child {
    margin-bottom: 0;
}

.registration-info {
    flex: 1;
    min-width: 250px;
}

.registration-organizer {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #F0EEFF;
    color: var(--primary-dark);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.registration-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--dark);
    margin: 0 0 10px 0;
    line-height: 1.4;
}

.registration-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 13px;
    color: var(--gray);
    margin-bottom: 12px;
}

.registration-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.registration-price {
    font-weight: 700;
    color: var(--dark);
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.registration-price .free {
    color: var(--success);
    background: #D4F5E9;
    padding: 2px 10px;
    border-radius: 6px;
    font-size: 12px;
}

.link-bukti {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    margin-top: 12px;
    transition: var(--transition);
}

.link-bukti:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

/* --- AKSI & BADGE --- */
.registration-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
    min-width: 130px;
}

.badge-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 16px;
    font-size: 12px;
    font-weight: 700;
    border-radius: 20px;
    text-transform: capitalize;
    border: none;
    width: 100%;
}

.badge-confirmed { background: #D4F5E9; color: #0A7A4A; }
.badge-pending { background: #FEF5D4; color: #B7950B; }
.badge-cancelled { background: #FDE2E2; color: #B03A2E; }

.btn-sm {
    padding: 8px 16px;
    font-size: 13px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
}

.btn-danger {
    background: #FDE2E2;
    color: #B03A2E;
}

.btn-danger:hover {
    background: #FCCCCC;
    transform: translateY(-2px);
}

.btn-primary {
    padding: 12px 28px;
    background: var(--primary);
    color: var(--white);
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(108, 99, 255, 0.25);
    color: var(--white);
}

/* --- EMPTY STATE --- */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state .icon-wrapper {
    width: 80px;
    height: 80px;
    background: #F0EEFF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px auto;
    color: var(--primary);
}

.empty-state h3 {
    color: var(--dark);
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 700;
}

.empty-state p {
    color: var(--gray);
    margin: 0 0 24px 0;
    font-size: 14px;
}

/* --- RESPONSIVE --- */
@media (max-width: 1024px) {
    .main-content { margin-left: 0; width: 100%; padding: 30px 20px 20px 20px; }
    .btn-menu { display: block; }
}

@media (max-width: 640px) {
    .topbar { padding-bottom: 20px; margin-bottom: 24px; }
    .topbar-left { align-items: flex-start; }
    .topbar-title { font-size: 22px; }
    
    .registration-item { flex-direction: column; align-items: stretch; padding: 16px; }
    .registration-actions { align-items: stretch; gap: 10px; }
    .registration-meta { flex-direction: column; gap: 8px; }
}
</style>

<div class="app-wrapper">
    <!-- Overlay untuk Sidebar Mobile -->
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(26, 26, 46, 0.6); z-index:90; backdrop-filter:blur(4px);"></div>
    
    <?php include '../../includes/sidebar_mahasiswa.php'; ?>
    
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn-menu" id="btnMenu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
                <div>
                    <div class="topbar-title">
                        <svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        History <span class="highlight">Registrasi</span>
                    </div>
                    <div class="topbar-sub">Daftar semua event yang telah kamu daftar</div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>
            <div class="card">
                <div class="card-body">
                    <?php if (mysqli_num_rows($list_reg) === 0): ?>
                        <div class="empty-state">
                            <div class="icon-wrapper">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M9 16l2 2 4-4"/></svg>
                            </div>
                            <h3>Belum ada registrasi</h3>
                            <p>Kamu belum mendaftar event apapun saat ini.</p>
                            <a href="event.php" class="btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                Cari Event Sekarang
                            </a>
                        </div>
                    <?php else: ?>
                        <?php while ($row = mysqli_fetch_assoc($list_reg)): ?>
                        <div class="registration-item">
                            <div class="registration-info">
                                <span class="registration-organizer">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <?= htmlspecialchars($row['penyelenggara'] ?? 'Pusat') ?>
                                </span>
                                <h3 class="registration-title"><?= htmlspecialchars($row['nama_event']) ?></h3>
                                
                                <div class="registration-meta">
                                    <span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        <?= formatTanggal($row['tanggal_mulai']) ?> <?= formatWaktu($row['waktu_mulai']) ?>
                                    </span>
                                    <span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <?= htmlspecialchars($row['nama_ruangan']) ?>
                                    </span>
                                    <span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                        <?= htmlspecialchars($row['nama_kategori']) ?>
                                    </span>
                                </div>

                                <div class="registration-price">
                                    <?= $row['jenis_tiket'] === 'berbayar' ? 'Rp ' . number_format($row['harga'], 0, ',', '.') : '<span class="free">Gratis</span>' ?>
                                </div>

                                <?php if ($row['bukti_pembayaran']): ?>
                                    <a href="../../assets/img/bukti/<?= $row['bukti_pembayaran'] ?>" target="_blank" class="link-bukti">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        Lihat Bukti Pembayaran
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="registration-actions">
                                <?php 
                                    $status_class = $row['status'] === 'confirmed' ? 'badge-confirmed' : 
                                                   ($row['status'] === 'pending' ? 'badge-pending' : 'badge-cancelled');
                                ?>
                                <span class="badge-status <?= $status_class ?>">
                                    <?= $row['status'] ?>
                                </span>
                                
                                <?php if ($row['status'] === 'pending'): ?>
                                    <a href="#" onclick="konfirmasiHapus('?aksi=batal&id=<?= $row['id_registrasi'] ?>','pendaftaran ini')" class="btn-sm btn-danger">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        Batalkan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Fungsi konfirmasi hapus bawaan
function konfirmasiHapus(link, pesan) {
    if (confirm('Yakin ingin membatalkan ' + pesan + '?')) {
        window.location.href = link;
    }
}

// Logika toggle sidebar untuk responsivitas mobile
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