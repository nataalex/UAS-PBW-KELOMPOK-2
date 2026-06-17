<?php
// ========================================================================
// pages/admin/registrasi.php — Halaman Kelola Registrasi
// ========================================================================

// ========================================================================
// 1. PENGATURAN AWAL & AUTENTIKASI
// ========================================================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';

// Memastikan hanya Admin yang sudah login yang bisa mengakses halaman ini
wajibAdmin();

$judul_halaman = 'Kelola Registrasi';
$halaman_aktif = 'registrasi';
$admin_org = $_SESSION['organisasi'] ?? 'Pusat';


// ========================================================================
// 2. LOGIKA PROSES UPDATE STATUS & HAPUS
// ========================================================================

// A. Proses Update Status (Terima/Tolak)
if (isset($_GET['aksi']) && $_GET['aksi'] === 'update_status') {
    $id  = (int)$_GET['id'];
    $sts = bersihkan($_GET['status'] ?? '');
    
    // Validasi: Cek apakah event ini milik admin yang login/pusat
    $cek = mysqli_query($koneksi, "SELECT r.id_registrasi FROM registrasi r JOIN event e ON r.id_event = e.id_event WHERE r.id_registrasi=$id AND (e.penyelenggara='$admin_org' OR '$admin_org'='Pusat')");
    
    if (in_array($sts, ['confirmed', 'pending', 'cancelled']) && mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "UPDATE registrasi SET status='$sts' WHERE id_registrasi=$id");
        redirectDenganPesan('registrasi.php', 'Status registrasi berhasil diperbarui!');
    } else {
        redirectDenganPesan('registrasi.php', 'Gagal! Anda tidak berhak mengubah data ini.', 'error');
    }
}

// B. Proses Hapus Registrasi
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)$_GET['id'];
    $cek = mysqli_query($koneksi, "SELECT r.id_registrasi FROM registrasi r JOIN event e ON r.id_event = e.id_event WHERE r.id_registrasi=$id AND (e.penyelenggara='$admin_org' OR '$admin_org'='Pusat')");
    
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "DELETE FROM registrasi WHERE id_registrasi=$id");
        redirectDenganPesan('registrasi.php', 'Registrasi berhasil dihapus.');
    }
}


// ========================================================================
// 3. PENGATURAN PENCARIAN, FILTER, & PAGINASI
// ========================================================================
$cari          = bersihkan($_GET['cari'] ?? '');
$filter_status = bersihkan($_GET['status'] ?? '');
$per_halaman   = 12;
$hal_ini       = max(1, (int)($_GET['hal'] ?? 1));
$offset        = ($hal_ini - 1) * $per_halaman;

// Query dasar (Filter hak akses organisasi)
$where = "WHERE (e.penyelenggara='$admin_org' OR '$admin_org'='Pusat')";
if ($cari)          $where .= " AND (pm.nama_lengkap LIKE '%$cari%' OR pm.nim LIKE '%$cari%' OR e.nama_event LIKE '%$cari%')";
if ($filter_status) $where .= " AND r.status='$filter_status'";

// Hitung total data
$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM registrasi r JOIN profil_mahasiswa pm ON r.id_mahasiswa = pm.id_mahasiswa JOIN event e ON r.id_event = e.id_event $where"))['total'];
$total_hal = ceil($total_rows / $per_halaman);

// Ambil data registrasi
$list_reg = mysqli_query($koneksi,
    "SELECT r.*, pm.nama_lengkap, pm.nim, e.nama_event, e.jenis_tiket 
     FROM registrasi r 
     JOIN profil_mahasiswa pm ON r.id_mahasiswa = pm.id_mahasiswa 
     JOIN event e ON r.id_event = e.id_event 
     $where 
     ORDER BY r.tanggal_registrasi DESC, r.id_registrasi DESC 
     LIMIT $per_halaman OFFSET $offset"
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

    body { background-color: var(--bg-dashboard); margin: 0; }
    .app-wrapper { background-color: var(--bg-dashboard); min-height: 100vh; display: flex; }
    .main-content {
        margin-left: 260px; 
        width: calc(100% - 260px);
        padding: 40px; 
        background-color: var(--bg-dashboard);
        min-height: 100vh;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    /* --- TOPBAR AREA (FIXED BUG: Menghilangkan Overlap) --- */
    .topbar {
        position: relative !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important; 
        padding-bottom: 24px !important; 
        margin-bottom: 32px !important;  
        border-bottom: 2px solid var(--border-color) !important; 
        background: transparent !important;
        width: 100% !important;
    }

    .topbar-judul { font-size: 28px; font-weight: 800; color: var(--text-dark); }
    .topbar-sub { font-size: 14px; color: var(--text-muted); margin-top: 6px; }

    /* --- CSS LAINNYA --- */
    .card { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01); }
    .cari-filter { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
    .input-cari { height: 42px; padding: 0 16px; border-radius: 10px; border: 1px solid var(--border-color); width: 280px; }
    .select-premium { height: 42px; padding: 0 16px; border-radius: 10px; border: 1px solid var(--border-color); }
    .btn-cari-premium { padding: 10px 20px; background: var(--royal-blue); color: #ffffff; border-radius: 10px; border: none; cursor: pointer; }
    
    .tabel-wrapper { overflow-x: auto; }
    .tabel-wrapper table { width: 100%; border-collapse: collapse; }
    .tabel-wrapper th { font-size: 11px; text-transform: uppercase; color: var(--text-muted); padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
    .tabel-wrapper td { padding: 16px 14px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
    
    .badge { padding: 4px 10px; font-size: 11px; font-weight: 600; border-radius: 8px; }
    
    /* --- TOMBOL AKSI BERWARNA --- */
    .btn-aksi { 
        display: inline-block;
        padding: 6px 12px; 
        border-radius: 8px; 
        text-decoration: none; 
        font-size: 12px; 
        font-weight: 600;
        margin: 2px;
        color: #ffffff;
        transition: all 0.2s ease-in-out;
        border: none;
    }
    .btn-aksi:hover { opacity: 0.85; transform: translateY(-1px); }
    .btn-terima { background-color: #10B981; } /* Hijau */
    .btn-tolak { background-color: #F59E0B; } /* Kuning/Oranye */
    .btn-hapus { background-color: #EF4444; } /* Merah */

    .btn-menu { display: none; background: none; border: none; font-size: 24px; cursor: pointer; margin-right: 14px; }

    @media (max-width: 1024px) { .main-content { margin-left: 0; width: 100%; } .btn-menu { display: block; } }
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:90"></div>
    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div>
                <button class="btn-menu" id="btnMenu"></button>
                <div class="topbar-judul">Kelola Pendaftar</div>
                <div class="topbar-sub">Kelola registrasi peserta masuk <?= $admin_org !== 'Pusat' ? "($admin_org)" : '' ?></div>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>
            
            <div class="card">
                <form method="GET" action="">
                    <div class="cari-filter">
                        <input type="text" class="input-cari" name="cari" placeholder="Cari nama, NIM, atau event..." value="<?= htmlspecialchars($cari) ?>">
                        <select name="status" class="select-premium">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn-cari-premium">Cari</button>
                    </div>
                </form>

                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center;">No</th>
                                <th>Mahasiswa</th>
                                <th>Event & Tiket</th>
                                <th>Tanggal & Bukti</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list_reg) === 0): ?>
                            <tr><td colspan="6" class="empty-state" style="text-align: center; padding: 30px;">Tidak ada data registrasi ditemukan.</td></tr>
                            <?php else: $no = $offset + 1; while ($row = mysqli_fetch_assoc($list_reg)): ?>
                            <tr>
                                <td style="text-align: center;"><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong><br>
                                    <small style="color:var(--text-muted)"><?= htmlspecialchars($row['nim']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['nama_event']) ?><br>
                                    <small style="text-transform:uppercase; font-weight:700; color:var(--royal-blue);"><?= htmlspecialchars($row['jenis_tiket']) ?></small>
                                </td>
                                <td>
                                    <small><?= formatTanggal($row['tanggal_registrasi']) ?></small><br>
                                    <?php if ($row['bukti_pembayaran']): ?>
                                        <a href="../../assets/img/bukti/<?= $row['bukti_pembayaran'] ?>" target="_blank" style="font-size:11px; text-decoration:none; color: var(--royal-blue); font-weight: 600;">Lihat Bukti</a>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;"><?= badgeStatus($row['status']) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a href="?aksi=update_status&id=<?= $row['id_registrasi'] ?>&status=confirmed" class="btn-aksi btn-terima" title="Terima">Terima</a>
                                        <a href="?aksi=update_status&id=<?= $row['id_registrasi'] ?>&status=cancelled" class="btn-aksi btn-tolak" title="Tolak">Tolak</a>
                                    <?php endif; ?>
                                    <a href="#" onclick="konfirmasiHapus('?aksi=hapus&id=<?= $row['id_registrasi'] ?>', 'registrasi ini')" class="btn-aksi btn-hapus" title="Hapus">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
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
        btnMenu.addEventListener('click', () => { sidebar.style.transform = 'translateX(0)'; overlay.style.display = 'block'; });
        overlay.addEventListener('click', () => { sidebar.style.transform = 'translateX(-100%)'; overlay.style.display = 'none'; });
    }
</script>

<?php include '../../includes/footer.php'; ?>