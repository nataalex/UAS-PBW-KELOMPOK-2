<?php
// pages/mahasiswa/dashboard.php — Dashboard Mahasiswa
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibMahasiswa();

$judul_halaman = 'Beranda';
$halaman_aktif = 'dashboard';
$id_mhs = (int)$_SESSION['id_mahasiswa'];

// Statistik mahasiswa ini
$total_reg = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi WHERE id_mahasiswa=$id_mhs"))['total'];

$total_confirmed = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi WHERE id_mahasiswa=$id_mhs AND status='confirmed'"))['total'];

$total_pending = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM registrasi WHERE id_mahasiswa=$id_mhs AND status='pending'"))['total'];

// Event yang akan datang (yang tersedia)
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

// Registrasi terbaru saya
$reg_saya = mysqli_query($koneksi,
    "SELECT r.*, e.nama_event, e.tanggal_mulai, e.waktu_mulai, k.nama_kategori
     FROM registrasi r
     JOIN event e ON r.id_event = e.id_event
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     WHERE r.id_mahasiswa=$id_mhs
     ORDER BY r.id_registrasi DESC
     LIMIT 5"
);
?>
<?php include '../../includes/header.php'; ?>
<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>
    <?php include '../../includes/sidebar_mahasiswa.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div>
                    <div class="topbar-judul">Selamat datang, <?= htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'])[0]) ?>! 👋</div>
                    <div class="topbar-sub"><?= htmlspecialchars($_SESSION['nim']) ?></div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php" class="btn btn-utama btn-sm">🔍 Jelajahi Event</a>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#dbeafe">📋</div>
                    <div><div class="stat-angka"><?= $total_reg ?></div><div class="stat-label">Total Registrasi</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#d1fae5">✅</div>
                    <div><div class="stat-angka"><?= $total_confirmed ?></div><div class="stat-label">Terkonfirmasi</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#fef3c7">⏳</div>
                    <div><div class="stat-angka"><?= $total_pending ?></div><div class="stat-label">Menunggu Konfirmasi</div></div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <!-- Event Mendatang -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-judul">📅 Event Mendatang</span>
                        <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php" class="btn btn-abu btn-sm">Lihat Semua</a>
                    </div>
                    <div style="padding:16px;display:flex;flex-direction:column;gap:10px">
                        <?php if (mysqli_num_rows($event_mendatang) === 0): ?>
                        <p style="color:#64748b;text-align:center;padding:20px">Tidak ada event mendatang.</p>
                        <?php else: while ($row = mysqli_fetch_assoc($event_mendatang)): ?>
                        <div style="padding:12px;border:1px solid #e2e8f0;border-radius:8px;transition:background .2s" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
                            <div style="font-weight:700;font-size:13px;margin-bottom:4px"><?= htmlspecialchars($row['nama_event']) ?></div>
                            <div style="font-size:12px;color:#64748b">
                                📅 <?= formatTanggal($row['tanggal_mulai']) ?> &nbsp;|&nbsp;
                                🏷️ <?= htmlspecialchars($row['nama_kategori']) ?>
                            </div>
                            <?php $sisa = $row['kapasitas'] - $row['jumlah_daftar']; ?>
                            <div style="font-size:12px;color:<?= $sisa <= 0 ? '#dc2626' : '#059669' ?>;margin-top:4px">
                                <?= $sisa <= 0 ? '❌ Kuota penuh' : "✅ Sisa $sisa kursi" ?>
                            </div>
                        </div>
                        <?php endwhile; endif; ?>
                    </div>
                </div>

                <!-- Registrasi Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-judul">📋 Registrasi Saya</span>
                        <a href="<?= BASE_URL ?>/pages/mahasiswa/registrasi_saya.php" class="btn btn-abu btn-sm">Lihat Semua</a>
                    </div>
                    <div class="tabel-wrapper">
                        <table>
                            <thead><tr><th>Event</th><th>Tanggal</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (mysqli_num_rows($reg_saya) === 0): ?>
                                <tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">
                                    Belum ada registrasi. <a href="<?= BASE_URL ?>/pages/mahasiswa/event.php">Cari event →</a>
                                </td></tr>
                                <?php else: while ($row = mysqli_fetch_assoc($reg_saya)): ?>
                                <tr>
                                    <td>
                                        <small style="font-weight:600"><?= htmlspecialchars(substr($row['nama_event'],0,28)) ?>...</small><br>
                                        <small style="color:#64748b"><?= htmlspecialchars($row['nama_kategori']) ?></small>
                                    </td>
                                    <td><small><?= formatTanggal($row['tanggal_mulai']) ?></small></td>
                                    <td><?= badgeStatus($row['status']) ?></td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>
