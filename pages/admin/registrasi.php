<?php
// ============================================
// pages/admin/registrasi.php — Kelola Registrasi
// ============================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibAdmin();

$judul_halaman = 'Kelola Registrasi';
$halaman_aktif = 'registrasi';
$admin_org = $_SESSION['organisasi'] ?? 'Pusat';

// Update status registrasi
if (isset($_GET['aksi']) && $_GET['aksi'] === 'update_status') {
    $id  = (int)$_GET['id'];
    $sts = bersihkan($_GET['status'] ?? '');
    
    // Cek apakah event ini milik admin yg login
    $cek = mysqli_query($koneksi, "SELECT r.id_registrasi FROM registrasi r JOIN event e ON r.id_event = e.id_event WHERE r.id_registrasi=$id AND (e.penyelenggara='$admin_org' OR '$admin_org'='Pusat')");
    
    if (in_array($sts, ['confirmed', 'pending', 'cancelled']) && mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "UPDATE registrasi SET status='$sts' WHERE id_registrasi=$id");
        redirectDenganPesan('registrasi.php', 'Status registrasi berhasil diperbarui!');
    } else {
        redirectDenganPesan('registrasi.php', 'Gagal! Anda tidak berhak mengubah data ini.', 'error');
    }
}

// Hapus registrasi
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)$_GET['id'];
    $cek = mysqli_query($koneksi, "SELECT r.id_registrasi FROM registrasi r JOIN event e ON r.id_event = e.id_event WHERE r.id_registrasi=$id AND (e.penyelenggara='$admin_org' OR '$admin_org'='Pusat')");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "DELETE FROM registrasi WHERE id_registrasi=$id");
        redirectDenganPesan('registrasi.php', 'Registrasi berhasil dihapus.');
    }
}

// Pencarian & Filter
$cari        = bersihkan($_GET['cari'] ?? '');
$filter_status = bersihkan($_GET['status'] ?? '');
$per_halaman = 12;
$hal_ini     = max(1, (int)($_GET['hal'] ?? 1));
$offset      = ($hal_ini - 1) * $per_halaman;

$where = "WHERE (e.penyelenggara='$admin_org' OR '$admin_org'='Pusat')";
if ($cari)          $where .= " AND (pm.nama_lengkap LIKE '%$cari%' OR pm.nim LIKE '%$cari%' OR e.nama_event LIKE '%$cari%')";
if ($filter_status) $where .= " AND r.status='$filter_status'";

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM registrasi r JOIN profil_mahasiswa pm ON r.id_mahasiswa = pm.id_mahasiswa JOIN event e ON r.id_event = e.id_event $where"))['total'];
$total_hal = ceil($total_rows / $per_halaman);

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
<div class="app-wrapper">
    <?php include '../../includes/sidebar_admin.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri">
                <div>
                    <div class="topbar-judul">Kelola Pendaftar</div>
                    <div class="topbar-sub">Kelola registrasi peserta masuk <?= $admin_org !== 'Pusat' ? "($admin_org)" : '' ?></div>
                </div>
            </div>
        </div>
        <div class="page-content">
            <?php tampilkanFlash(); ?>
            <div class="card">
                <form method="GET" action="">
                    <div class="cari-filter">
                        <input type="text" class="input-cari" name="cari" placeholder="Cari nama, NIM, event..." value="<?= htmlspecialchars($cari) ?>">
                        <select name="status" style="padding:8px; border-radius:8px; border:1px solid #ccc;">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-utama btn-sm">🔍 Cari</button>
                    </div>
                </form>
                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>Event</th>
                                <th>Tanggal & Bukti</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list_reg) === 0): ?>
                            <tr><td colspan="6" style="text-align:center;">Tidak ada data registrasi.</td></tr>
                            <?php else: $no = $offset + 1; while ($row = mysqli_fetch_assoc($list_reg)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong><br><small><?= htmlspecialchars($row['nim']) ?></small></td>
                                <td><?= htmlspecialchars($row['nama_event']) ?><br><small style="color:var(--biru-utama);font-weight:bold;"><?= ucfirst($row['jenis_tiket']) ?></small></td>
                                <td>
                                    <small><?= formatTanggal($row['tanggal_registrasi']) ?></small><br>
                                    <?php if ($row['bukti_pembayaran']): ?>
                                        <a href="../../assets/img/bukti/<?= $row['bukti_pembayaran'] ?>" target="_blank" style="font-size:12px; color:#10b981;">Lihat Bukti Bayar</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= badgeStatus($row['status']) ?></td>
                                <td>
                                    <div style="display:flex;gap:4px">
                                        <?php if ($row['status'] === 'pending'): ?>
                                        <a href="?aksi=update_status&id=<?= $row['id_registrasi'] ?>&status=confirmed" class="btn btn-sm" style="background:#10b981;color:white" title="Terima">✔️</a>
                                        <a href="?aksi=update_status&id=<?= $row['id_registrasi'] ?>&status=cancelled" class="btn btn-merah btn-sm" title="Tolak">❌</a>
                                        <?php endif; ?>
                                        <a href="#" onclick="konfirmasiHapus('?aksi=hapus&id=<?= $row['id_registrasi'] ?>', 'registrasi ini')" class="btn btn-merah btn-sm" title="Hapus">🗑️</a>
                                    </div>
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
<?php include '../../includes/footer.php'; ?>