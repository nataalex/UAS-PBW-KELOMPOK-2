<?php
// pages/admin/ruangan.php — CRUD Ruangan
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibAdmin();

$judul_halaman = 'Kelola Ruangan';
$halaman_aktif = 'ruangan';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = bersihkan($_POST['nama_ruangan'] ?? '');
    $kapasitas = (int)($_POST['kapasitas'] ?? 0);
    $id        = (int)($_POST['id_ruangan'] ?? 0);

    if (empty($nama))    $errors[] = 'Nama ruangan wajib diisi.';
    if ($kapasitas <= 0) $errors[] = 'Kapasitas harus lebih dari 0.';

    if (empty($errors)) {
        if ($id > 0) {
            mysqli_query($koneksi, "UPDATE ruangan SET nama_ruangan='$nama', kapasitas=$kapasitas WHERE id_ruangan=$id");
            redirectDenganPesan('ruangan.php', 'Ruangan berhasil diperbarui!');
        } else {
            mysqli_query($koneksi, "INSERT INTO ruangan (nama_ruangan, kapasitas) VALUES ('$nama', $kapasitas)");
            redirectDenganPesan('ruangan.php', 'Ruangan berhasil ditambahkan!');
        }
    }
}

if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)$_GET['id'];
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM event WHERE id_ruangan=$id"));
    if ($cek['total'] > 0) {
        redirectDenganPesan('ruangan.php', 'Ruangan tidak bisa dihapus karena masih digunakan!', 'error');
    }
    mysqli_query($koneksi, "DELETE FROM ruangan WHERE id_ruangan=$id");
    redirectDenganPesan('ruangan.php', 'Ruangan berhasil dihapus.');
}

$edit_data = null;
if (isset($_GET['aksi']) && $_GET['aksi'] === 'edit') {
    $edit_data = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT * FROM ruangan WHERE id_ruangan=" . (int)$_GET['id']));
}

$list_ruangan = mysqli_query($koneksi,
    "SELECT r.*, COUNT(e.id_event) as jumlah_event
     FROM ruangan r
     LEFT JOIN event e ON r.id_ruangan = e.id_ruangan
     GROUP BY r.id_ruangan
     ORDER BY r.nama_ruangan"
);
?>
<?php include '../../includes/header.php'; ?>
<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>
    <?php include '../../includes/sidebar_admin.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div><div class="topbar-judul">Kelola Ruangan</div>
                <div class="topbar-sub">Atur data ruangan untuk event</div></div>
            </div>
        </div>
        <div class="page-content">
            <?php tampilkanFlash(); ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error"><?= implode('<br>', $errors) ?></div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start">
                <div class="card">
                    <div class="card-header"><span class="card-judul"><?= $edit_data ? '✏️ Edit Ruangan' : '➕ Tambah Ruangan' ?></span></div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id_ruangan" value="<?= $edit_data['id_ruangan'] ?>">
                            <?php endif; ?>
                            <div class="form-grup">
                                <label>Nama Ruangan *</label>
                                <input type="text" name="nama_ruangan" required
                                       placeholder="Contoh: Aula Utama"
                                       value="<?= htmlspecialchars($edit_data['nama_ruangan'] ?? '') ?>">
                            </div>
                            <div class="form-grup">
                                <label>Kapasitas (orang) *</label>
                                <input type="number" name="kapasitas" min="1" required
                                       placeholder="Contoh: 200"
                                       value="<?= $edit_data['kapasitas'] ?? '' ?>">
                            </div>
                            <div style="display:flex;gap:8px">
                                <button type="submit" class="btn btn-utama"><?= $edit_data ? '💾 Simpan' : '➕ Tambah' ?></button>
                                <?php if ($edit_data): ?>
                                <a href="ruangan.php" class="btn btn-abu">Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><span class="card-judul">🏛️ Daftar Ruangan</span></div>
                    <div class="tabel-wrapper">
                        <table>
                            <thead><tr><th>No</th><th>Nama Ruangan</th><th>Kapasitas</th><th>Dipakai</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($list_ruangan)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_ruangan']) ?></strong></td>
                                    <td><?= number_format($row['kapasitas']) ?> orang</td>
                                    <td><span style="background:#dbeafe;color:#1e40af;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600"><?= $row['jumlah_event'] ?> event</span></td>
                                    <td>
                                        <div style="display:flex;gap:4px">
                                            <a href="?aksi=edit&id=<?= $row['id_ruangan'] ?>" class="btn btn-abu btn-sm">✏️</a>
                                            <a href="#" onclick="konfirmasiHapus('?aksi=hapus&id=<?= $row['id_ruangan'] ?>','<?= addslashes($row['nama_ruangan']) ?>')" class="btn btn-merah btn-sm">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>
