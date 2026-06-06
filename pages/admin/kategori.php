<?php
// pages/admin/kategori.php — CRUD Kategori Event
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibAdmin();

$judul_halaman = 'Kelola Kategori';
$halaman_aktif = 'kategori';
$errors = [];

// PROSES FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = bersihkan($_POST['nama_kategori'] ?? '');
    $id   = (int)($_POST['id_kategori'] ?? 0);

    if (empty($nama)) {
        $errors[] = 'Nama kategori wajib diisi.';
    } else {
        if ($id > 0) {
            mysqli_query($koneksi, "UPDATE kategori_event SET nama_kategori='$nama' WHERE id_kategori=$id");
            redirectDenganPesan('kategori.php', 'Kategori berhasil diperbarui!');
        } else {
            mysqli_query($koneksi, "INSERT INTO kategori_event (nama_kategori) VALUES ('$nama')");
            redirectDenganPesan('kategori.php', 'Kategori baru berhasil ditambahkan!');
        }
    }
}

// HAPUS
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)$_GET['id'];
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM event WHERE id_kategori=$id"));
    if ($cek['total'] > 0) {
        redirectDenganPesan('kategori.php', 'Kategori tidak bisa dihapus karena masih digunakan oleh event!', 'error');
    }
    mysqli_query($koneksi, "DELETE FROM kategori_event WHERE id_kategori=$id");
    redirectDenganPesan('kategori.php', 'Kategori berhasil dihapus.');
}

// Edit data
$edit_data = null;
if (isset($_GET['aksi']) && $_GET['aksi'] === 'edit') {
    $id = (int)$_GET['id'];
    $q  = mysqli_query($koneksi, "SELECT * FROM kategori_event WHERE id_kategori=$id");
    $edit_data = mysqli_fetch_assoc($q);
}

$list_kat = mysqli_query($koneksi,
    "SELECT k.*, COUNT(e.id_event) as jumlah_event
     FROM kategori_event k
     LEFT JOIN event e ON k.id_kategori = e.id_kategori
     GROUP BY k.id_kategori
     ORDER BY k.nama_kategori"
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
                <div><div class="topbar-judul">Kelola Kategori</div>
                <div class="topbar-sub">Atur kategori event kampus</div></div>
            </div>
        </div>
        <div class="page-content">
            <?php tampilkanFlash(); ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error"><?= implode('<br>', $errors) ?></div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start">
                <!-- Form -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-judul"><?= $edit_data ? '✏️ Edit Kategori' : '➕ Tambah Kategori' ?></span>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id_kategori" value="<?= $edit_data['id_kategori'] ?>">
                            <?php endif; ?>
                            <div class="form-grup">
                                <label>Nama Kategori <span style="color:red">*</span></label>
                                <input type="text" name="nama_kategori" required
                                       placeholder="Contoh: Seminar, Workshop..."
                                       value="<?= htmlspecialchars($edit_data['nama_kategori'] ?? '') ?>">
                            </div>
                            <div style="display:flex;gap:8px">
                                <button type="submit" class="btn btn-utama"><?= $edit_data ? '💾 Simpan' : '➕ Tambah' ?></button>
                                <?php if ($edit_data): ?>
                                <a href="kategori.php" class="btn btn-abu">Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card">
                    <div class="card-header"><span class="card-judul">🏷️ Daftar Kategori</span></div>
                    <div class="tabel-wrapper">
                        <table>
                            <thead><tr><th>No</th><th>Nama Kategori</th><th>Jumlah Event</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($list_kat)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                                    <td><span style="background:#dbeafe;color:#1e40af;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600"><?= $row['jumlah_event'] ?> event</span></td>
                                    <td>
                                        <div style="display:flex;gap:4px">
                                            <a href="?aksi=edit&id=<?= $row['id_kategori'] ?>" class="btn btn-abu btn-sm">✏️</a>
                                            <a href="#" onclick="konfirmasiHapus('?aksi=hapus&id=<?= $row['id_kategori'] ?>','<?= addslashes($row['nama_kategori']) ?>')" class="btn btn-merah btn-sm">🗑️</a>
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
