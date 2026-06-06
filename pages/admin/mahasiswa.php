<?php
// pages/admin/mahasiswa.php — Lihat Data Mahasiswa
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibAdmin();

$judul_halaman = 'Data Mahasiswa';
$halaman_aktif = 'mahasiswa';

$cari        = bersihkan($_GET['cari'] ?? '');
$filter_prodi = bersihkan($_GET['prodi'] ?? '');
$per_halaman = 10;
$hal_ini     = max(1, (int)($_GET['hal'] ?? 1));
$offset      = ($hal_ini - 1) * $per_halaman;

$where = "WHERE 1=1";
if ($cari)        $where .= " AND (pm.nama_lengkap LIKE '%$cari%' OR pm.nim LIKE '%$cari%')";
if ($filter_prodi) $where .= " AND pm.prodi='$filter_prodi'";

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM profil_mahasiswa pm $where"))['total'];
$total_hal = ceil($total_rows / $per_halaman);

$list = mysqli_query($koneksi,
    "SELECT pm.*, u.username, u.created_at,
            COUNT(r.id_registrasi) as total_registrasi
     FROM profil_mahasiswa pm
     JOIN users u ON pm.id_user = u.id_user
     LEFT JOIN registrasi r ON pm.id_mahasiswa = r.id_mahasiswa
     $where
     GROUP BY pm.id_mahasiswa
     ORDER BY pm.nama_lengkap
     LIMIT $per_halaman OFFSET $offset"
);

// Ambil daftar prodi untuk filter
$list_prodi = mysqli_query($koneksi,
    "SELECT DISTINCT prodi FROM profil_mahasiswa ORDER BY prodi");
?>
<?php include '../../includes/header.php'; ?>
<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>
    <?php include '../../includes/sidebar_admin.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div><div class="topbar-judul">Data Mahasiswa</div>
                <div class="topbar-sub">Daftar mahasiswa terdaftar di sistem</div></div>
            </div>
        </div>
        <div class="page-content">
            <?php tampilkanFlash(); ?>
            <div class="card">
                <div class="card-header">
                    <span class="card-judul">👤 Data Mahasiswa (<?= $total_rows ?>)</span>
                </div>
                <form method="GET">
                    <div class="cari-filter">
                        <input type="text" class="input-cari" name="cari"
                               placeholder="Cari nama atau NIM..."
                               value="<?= htmlspecialchars($cari) ?>">
                        <select name="prodi" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit">
                            <option value="">Semua Prodi</option>
                            <?php while ($p = mysqli_fetch_assoc($list_prodi)): ?>
                            <option value="<?= htmlspecialchars($p['prodi']) ?>"
                                    <?= $filter_prodi === $p['prodi'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['prodi']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-utama btn-sm">🔍 Cari</button>
                        <a href="mahasiswa.php" class="btn btn-abu btn-sm">Reset</a>
                    </div>
                </form>
                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th><th>Nama Lengkap</th><th>NIM</th>
                                <th>Program Studi</th><th>Email</th><th>Username</th><th>Event Diikuti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list) === 0): ?>
                            <tr><td colspan="7" style="text-align:center;padding:30px;color:#64748b">
                                Tidak ada data mahasiswa.
                            </td></tr>
                            <?php else: $no = $offset + 1; while ($row = mysqli_fetch_assoc($list)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div style="width:32px;height:32px;border-radius:50%;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0">
                                            <?= strtoupper(substr($row['nama_lengkap'],0,1)) ?>
                                        </div>
                                        <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong>
                                    </div>
                                </td>
                                <td><code style="font-size:12px"><?= htmlspecialchars($row['nim']) ?></code></td>
                                <td><small><?= htmlspecialchars($row['prodi']) ?></small></td>
                                <td><small><?= htmlspecialchars($row['email']) ?></small></td>
                                <td><small><?= htmlspecialchars($row['username']) ?></small></td>
                                <td>
                                    <span style="background:#dbeafe;color:#1e40af;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600">
                                        <?= $row['total_registrasi'] ?> event
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_hal > 1): ?>
                <div class="paginasi">
                    <span><?= $total_rows ?> mahasiswa</span>
                    <div class="paginasi-tombol">
                        <?php for ($i = 1; $i <= $total_hal; $i++): ?>
                        <a href="?hal=<?= $i ?>&cari=<?= urlencode($cari) ?>&prodi=<?= urlencode($filter_prodi) ?>"
                           class="<?= $i === $hal_ini ? 'aktif' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>
