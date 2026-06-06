<?php
// ============================================
// pages/admin/dashboard.php — Dashboard Admin
// ============================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibAdmin();

$judul_halaman = 'Dashboard';
$halaman_aktif = 'dashboard';

// --- Ambil data statistik ---
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

// Event terbaru
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

// Registrasi terbaru
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

<div class="app-wrapper">
    <!-- Overlay untuk mobile -->
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>

    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div>
                    <div class="topbar-judul">Dashboard</div>
                    <div class="topbar-sub">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</div>
                </div>
            </div>
            <div class="topbar-kanan">
                <a href="<?= BASE_URL ?>/pages/admin/event.php?aksi=tambah" class="btn btn-utama btn-sm">
                    ➕ Buat Event
                </a>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>

            <!-- Kartu Statistik -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#dbeafe">📅</div>
                    <div>
                        <div class="stat-angka"><?= $total_event ?></div>
                        <div class="stat-label">Total Event</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#d1fae5">👤</div>
                    <div>
                        <div class="stat-angka"><?= $total_mahasiswa ?></div>
                        <div class="stat-label">Mahasiswa Terdaftar</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#fef3c7">📋</div>
                    <div>
                        <div class="stat-angka"><?= $total_registrasi ?></div>
                        <div class="stat-label">Total Registrasi</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-ikon" style="background:#ede9fe">✅</div>
                    <div>
                        <div class="stat-angka"><?= $total_confirmed ?></div>
                        <div class="stat-label">Registrasi Confirmed</div>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">

                <!-- Event Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-judul">📅 Event Terbaru</span>
                        <a href="<?= BASE_URL ?>/pages/admin/event.php" class="btn btn-abu btn-sm">Lihat Semua</a>
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
                                <?php while ($row = mysqli_fetch_assoc($event_terbaru)): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_event']) ?></strong><br>
                                        <small style="color:#64748b"><?= formatTanggal($row['tanggal_mulai']) ?></small>
                                    </td>
                                    <td><small><?= htmlspecialchars($row['nama_kategori']) ?></small></td>
                                    <td><strong><?= $row['jumlah_daftar'] ?></strong>/<?= $row['kapasitas'] ?></td>
                                    <td><?= badgeStatus($row['status']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Registrasi Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-judul">📋 Registrasi Terbaru</span>
                        <a href="<?= BASE_URL ?>/pages/admin/registrasi.php" class="btn btn-abu btn-sm">Lihat Semua</a>
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
                                <?php while ($row = mysqli_fetch_assoc($reg_terbaru)): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong><br>
                                        <small style="color:#64748b"><?= htmlspecialchars($row['nim']) ?></small>
                                    </td>
                                    <td><small><?= htmlspecialchars(substr($row['nama_event'], 0, 30)) ?>...</small></td>
                                    <td><?= badgeStatus($row['status']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Info Ringkas -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
                <div class="card card-body" style="text-align:center">
                    <div style="font-size:32px;margin-bottom:8px">🏷️</div>
                    <div style="font-size:24px;font-weight:800"><?= $total_kategori ?></div>
                    <div style="font-size:13px;color:#64748b">Kategori Event</div>
                    <a href="<?= BASE_URL ?>/pages/admin/kategori.php" class="btn btn-abu btn-sm" style="margin-top:12px">Kelola</a>
                </div>
                <div class="card card-body" style="text-align:center">
                    <div style="font-size:32px;margin-bottom:8px">🏛️</div>
                    <div style="font-size:24px;font-weight:800"><?= $total_ruangan ?></div>
                    <div style="font-size:13px;color:#64748b">Ruangan Tersedia</div>
                    <a href="<?= BASE_URL ?>/pages/admin/ruangan.php" class="btn btn-abu btn-sm" style="margin-top:12px">Kelola</a>
                </div>
                <div class="card card-body" style="text-align:center">
                    <div style="font-size:32px;margin-bottom:8px">📊</div>
                    <div style="font-size:24px;font-weight:800"><?= $total_mahasiswa ?></div>
                    <div style="font-size:13px;color:#64748b">Total Mahasiswa</div>
                    <a href="<?= BASE_URL ?>/pages/admin/mahasiswa.php" class="btn btn-abu btn-sm" style="margin-top:12px">Lihat</a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>
