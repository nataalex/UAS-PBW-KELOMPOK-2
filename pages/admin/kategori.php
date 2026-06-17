<?php
// ========================================================================
// pages/admin/kategori.php — Halaman Kelola Kategori Event
// ========================================================================

// ========================================================================
// 1. PENGATURAN AWAL & AUTENTIKASI
// ========================================================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';

// Memastikan hanya Admin yang sudah login yang bisa mengakses halaman ini
wajibAdmin();

$judul_halaman = 'Kelola Kategori';
$halaman_aktif = 'kategori';
$admin_org = $_SESSION['organisasi'] ?? 'Pusat';

// Variabel pendukung mode edit
$is_edit = false;
$id_edit = 0;
$nama_kategori_val = '';

// ========================================================================
// 2. LOGIKA PROSES TAMBAH / UPDATE KATEGORI
// ========================================================================
if (isset($_POST['simpan_kategori'])) {
    $nama_kategori = bersihkan($_POST['nama_kategori'] ?? '');
    $id_kategori   = (int)($_POST['id_kategori'] ?? 0);

    if (empty($nama_kategori)) {
        redirectDenganPesan('kategori.php', 'Nama kategori tidak boleh kosong!', 'error');
    }

    // Jika id_kategori > 0 berarti Edit, jika tidak berarti Tambah Baru
    if ($id_kategori > 0) {
        $query = "UPDATE kategori_event SET nama_kategori = '$nama_kategori' WHERE id_kategori = $id_kategori";
        if (mysqli_query($koneksi, $query)) {
            redirectDenganPesan('kategori.php', 'Kategori berhasil diperbarui!');
        } else {
            redirectDenganPesan('kategori.php', 'Gagal memperbarui kategori.', 'error');
        }
    } else {
        $query = "INSERT INTO kategori_event (nama_kategori) VALUES ('$nama_kategori')";
        if (mysqli_query($koneksi, $query)) {
            redirectDenganPesan('kategori.php', 'Kategori baru berhasil ditambahkan!');
        } else {
            redirectDenganPesan('kategori.php', 'Gagal menambahkan kategori.', 'error');
        }
    }
}

// ========================================================================
// 3. LOGIKA MODE EDIT & HAPUS
// ========================================================================

// A. Mode Edit (Mengambil data untuk ditampilkan di form)
if (isset($_GET['aksi']) && $_GET['aksi'] === 'edit') {
    $id_edit = (int)$_GET['id'];
    $res_edit = mysqli_query($koneksi, "SELECT * FROM kategori_event WHERE id_kategori = $id_edit");
    if (mysqli_num_rows($res_edit) > 0) {
        $data_edit = mysqli_fetch_assoc($res_edit);
        $nama_kategori_val = $data_edit['nama_kategori'];
        $is_edit = true;
    }
}

// B. Proses Hapus Kategori
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)$_GET['id'];
    // Cek relasi: Kategori tidak bisa dihapus jika sedang digunakan oleh event
    $cek_relasi = mysqli_query($koneksi, "SELECT id_event FROM event WHERE id_kategori = $id LIMIT 1");
    if (mysqli_num_rows($cek_relasi) > 0) {
        redirectDenganPesan('kategori.php', 'Gagal! Kategori ini masih digunakan oleh beberapa event.', 'error');
    } else {
        mysqli_query($koneksi, "DELETE FROM kategori_event WHERE id_kategori = $id");
        redirectDenganPesan('kategori.php', 'Kategori berhasil dihapus.');
    }
}

// ========================================================================
// 4. PENGATURAN PENCARIAN & PAGINASI
// ========================================================================
$cari        = bersihkan($_GET['cari'] ?? '');
$per_halaman = 10;
$hal_ini     = max(1, (int)($_GET['hal'] ?? 1));
$offset      = ($hal_ini - 1) * $per_halaman;

$where = "";
if ($cari) {
    $where = "WHERE nama_kategori LIKE '%$cari%'";
}

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kategori_event $where"))['total'];
$total_hal  = ceil($total_rows / $per_halaman);

$list_kategori = mysqli_query($koneksi, 
    "SELECT * FROM kategori_event 
     $where 
     ORDER BY nama_kategori ASC 
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

    /* --- TOPBAR AREA --- */
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

    /* --- KOMPONEN CARD & FORM --- */
    .card { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01); margin-bottom: 32px; }
    .card-judul-inline { margin: 0; font-size: 16px; font-weight: 700; color: var(--text-dark); }
    
    .form-group-premium { display: flex; gap: 12px; align-items: center; max-width: 650px; }
    .input-premium { height: 42px; padding: 0 16px; border-radius: 10px; border: 1px solid var(--border-color); flex: 1; }
    .btn-utama-premium { height: 42px; padding: 0 24px; background: var(--royal-blue); color: white; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; }
    .btn-batal-premium { height: 42px; padding: 0 20px; background: #f1f5f9; color: #475569; border-radius: 10px; text-decoration: none; display: flex; align-items: center; font-weight: 600; }

    /* --- TABEL --- */
    .tabel-wrapper { overflow-x: auto; }
    .tabel-wrapper table { width: 100%; border-collapse: collapse; }
    .tabel-wrapper th { font-size: 11px; text-transform: uppercase; color: var(--text-muted); padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
    .tabel-wrapper td { padding: 16px 14px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
    
    /* --- Tombol Aksi Berwarna --- */
    .btn-aksi { padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; margin: 0 2px; }
    .btn-edit-color { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .btn-edit-color:hover { background: #2563eb; color: white; }
    .btn-hapus-color { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; }
    .btn-hapus-color:hover { background: #ef4444; color: white; }

    .btn-menu { display: none; background: none; border: none; font-size: 24px; cursor: pointer; margin-right: 14px; }

    @media (max-width: 1024px) { .main-content { margin-left: 0; width: 100%; } .btn-menu { display: block; } }
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:90"></div>
    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div>
                <button class="btn-menu" id="btnMenu">☰</button>
                <div class="topbar-judul">Kelola Kategori</div>
                <div class="topbar-sub">Manajemen klasifikasi kegiatan sistem</div>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>
            
            <div class="card">
                <h3 class="card-judul-inline"><?= $is_edit ? 'Ubah Data Kategori' : 'Tambah Kategori Baru' ?></h3>
                <form method="POST" action="kategori.php" style="margin-top: 15px;">
                    <input type="hidden" name="id_kategori" value="<?= $id_edit ?>">
                    <div class="form-group-premium">
                        <input type="text" class="input-premium" name="nama_kategori" placeholder="Masukkan nama kategori..." value="<?= htmlspecialchars($nama_kategori_val) ?>" required>
                        <button type="submit" name="simpan_kategori" class="btn-utama-premium">Simpan</button>
                        <?php if ($is_edit): ?>
                            <a href="kategori.php" class="btn-batal-premium">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <form method="GET" action="" style="margin-bottom: 20px;">
                    <div class="cari-filter">
                        <input type="text" class="input-cari" name="cari" placeholder="Cari kategori..." value="<?= htmlspecialchars($cari) ?>">
                        <button type="submit" class="btn-utama-premium">Cari</button>
                    </div>
                </form>
                
                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center;">No</th>
                                <th>Nama Kategori</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list_kategori) === 0): ?>
                                <tr><td colspan="3" class="empty-state">Tidak ada kategori ditemukan.</td></tr>
                            <?php else: $no = $offset + 1; while ($row = mysqli_fetch_assoc($list_kategori)): ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600;"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                <td style="text-align: center;">
                                    <a href="?aksi=edit&id=<?= $row['id_kategori'] ?>" class="btn-aksi btn-edit-color">Edit</a>
                                    <a href="#" onclick="konfirmasiHapus('?aksi=hapus&id=<?= $row['id_kategori'] ?>', '<?= htmlspecialchars($row['nama_kategori']) ?>')" class="btn-aksi btn-hapus-color">Hapus</a>
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
    function konfirmasiHapus(url, nama) {
        if (confirm("Yakin hapus kategori \"" + nama + "\"?")) {
            window.location.href = url;
        }
    }
    const btnMenu = document.getElementById('btnMenu');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnMenu && sidebar && overlay) {
        btnMenu.addEventListener('click', () => { sidebar.style.transform = 'translateX(0)'; overlay.style.display = 'block'; });
        overlay.addEventListener('click', () => { sidebar.style.transform = 'translateX(-100%)'; overlay.style.display = 'none'; });
    }
</script>

<?php include '../../includes/footer.php'; ?>