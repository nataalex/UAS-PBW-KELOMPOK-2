<?php
// ============================================
// pages/admin/event.php — Kelola Event (Fixed Overlap & No Icon)
// ============================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibAdmin();

$judul_halaman = 'Kelola Event';
$halaman_aktif = 'event';
$aksi = $_GET['aksi'] ?? 'list';

// Ambil data organisasi admin yang sedang login
$admin_org = $_SESSION['organisasi'] ?? 'Pusat';

// Ambil daftar kategori & ruangan (untuk dropdown)
$list_kategori = mysqli_query($koneksi, "SELECT * FROM kategori_event ORDER BY nama_kategori");
$list_ruangan  = mysqli_query($koneksi, "SELECT * FROM ruangan ORDER BY nama_ruangan");

// ==========================================
// PROSES FORM TAMBAH / EDIT
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_event   = (int)($_POST['id_event'] ?? 0);
    $nama       = bersihkan($_POST['nama_event'] ?? '');
    $id_kat     = (int)($_POST['id_kategori'] ?? 0);
    $id_rg      = (int)($_POST['id_ruangan'] ?? 0);
    $deskripsi  = bersihkan($_POST['deskripsi'] ?? '');
    $tgl        = bersihkan($_POST['tanggal_mulai'] ?? '');
    $waktu      = bersihkan($_POST['waktu_mulai'] ?? '');
    $kapasitas  = (int)($_POST['kapasitas'] ?? 0);
    $pembicara  = bersihkan($_POST['pembicara'] ?? '');
    $status     = bersihkan($_POST['status'] ?? 'draft');
    
    // Fitur Baru: Tiket dan Kepemilikan Event
    $jenis_tiket   = bersihkan($_POST['jenis_tiket'] ?? 'gratis');
    $harga         = (int)($_POST['harga'] ?? 0);
    $penyelenggara = $admin_org; // Ambil otomatis dari session admin

    $errors = [];
    if (empty($nama))    $errors[] = 'Nama event wajib diisi.';
    if ($id_kat === 0)   $errors[] = 'Pilih kategori event.';
    if ($id_rg === 0)    $errors[] = 'Pilih ruangan event.';
    if (empty($tgl))     $errors[] = 'Tanggal mulai wajib diisi.';
    if (empty($waktu))   $errors[] = 'Waktu mulai wajib diisi.';
    if ($kapasitas <= 0) $errors[] = 'Kapasitas harus lebih dari 0.';
    if ($jenis_tiket === 'berbayar' && $harga <= 0) $errors[] = 'Harga tiket berbayar tidak boleh Rp 0.';

    if (empty($errors)) {
        if ($id_event > 0) {
            // UPDATE: Validasi biar admin ormawa gak bisa ngedit event orang lain (kecuali pusat)
            $sql = "UPDATE event SET
                        nama_event='$nama', id_kategori=$id_kat, id_ruangan=$id_rg,
                        deskripsi='$deskripsi', tanggal_mulai='$tgl', waktu_mulai='$waktu',
                        kapasitas=$kapasitas, pembicara='$pembicara', status='$status',
                        jenis_tiket='$jenis_tiket', harga=$harga
                    WHERE id_event=$id_event AND (penyelenggara='$penyelenggara' OR '$penyelenggara' = 'Pusat')";
            mysqli_query($koneksi, $sql);
            redirectDenganPesan('event.php', 'Event berhasil diperbarui!');
        } else {
            // INSERT
            $sql = "INSERT INTO event
                        (id_kategori, id_ruangan, nama_event, deskripsi, tanggal_mulai, waktu_mulai, kapasitas, pembicara, status, jenis_tiket, harga, penyelenggara)
                    VALUES ($id_kat, $id_rg, '$nama', '$deskripsi', '$tgl', '$waktu', $kapasitas, '$pembicara', '$status', '$jenis_tiket', $harga, '$penyelenggara')";
            mysqli_query($koneksi, $sql);
            redirectDenganPesan('event.php', 'Event baru berhasil ditambahkan!');
        }
    } else {
        $aksi = ($id_event > 0) ? 'edit' : 'tambah';
    }
}

// ==========================================
// PROSES HAPUS
// ==========================================
if ($aksi === 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Cek apakah event ini milik admin yang login
    $cek_milik = mysqli_query($koneksi, "SELECT id_event FROM event WHERE id_event=$id AND (penyelenggara='$admin_org' OR '$admin_org'='Pusat')");
    
    if (mysqli_num_rows($cek_milik) > 0) {
        // Hapus dulu registrasi yang terkait
        mysqli_query($koneksi, "DELETE FROM registrasi WHERE id_event=$id");
        mysqli_query($koneksi, "DELETE FROM event WHERE id_event=$id");
        redirectDenganPesan('event.php', 'Event berhasil dihapus.', 'sukses');
    } else {
        redirectDenganPesan('event.php', 'Gagal! Anda tidak berhak menghapus event organisasi lain.', 'error');
    }
}

// ==========================================
// AMBIL DATA UNTUK FORM EDIT
// ==========================================
$edit_data = null;
if ($aksi === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Admin hanya bisa mengambil data edit miliknya sendiri, kecuali Pusat
    $q = mysqli_query($koneksi, "SELECT * FROM event WHERE id_event=$id AND (penyelenggara='$admin_org' OR '$admin_org'='Pusat')");
    $edit_data = mysqli_fetch_assoc($q);
    if (!$edit_data) redirectDenganPesan('event.php', 'Event tidak ditemukan atau Anda tidak berhak mengaksesnya.', 'error');
}

// ==========================================
// AMBIL DAFTAR EVENT (dengan pencarian & paginasi)
// ==========================================
$per_halaman  = 8;
$hal_ini      = max(1, (int)($_GET['hal'] ?? 1));
$offset       = ($hal_ini - 1) * $per_halaman;
$cari         = bersihkan($_GET['cari'] ?? '');
$filter_kat   = (int)($_GET['kategori'] ?? 0);
$filter_status = bersihkan($_GET['status'] ?? '');

$where = "WHERE 1=1";
if ($cari)          $where .= " AND e.nama_event LIKE '%$cari%'";
if ($filter_kat)   $where .= " AND e.id_kategori=$filter_kat";
if ($filter_status) $where .= " AND e.status='$filter_status'";

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM event e $where"))['total'];

$total_hal = ceil($total_rows / $per_halaman);

$list_event = mysqli_query($koneksi,
    "SELECT e.*, k.nama_kategori, r.nama_ruangan,
            COUNT(reg.id_registrasi) as jumlah_daftar
     FROM event e
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     JOIN ruangan r ON e.id_ruangan = r.id_ruangan
     LEFT JOIN registrasi reg ON e.id_event = reg.id_event AND reg.status != 'cancelled'
     $where
     GROUP BY e.id_event
     ORDER BY e.tanggal_mulai DESC
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

    body {
        background-color: var(--bg-dashboard);
        margin: 0;
    }

    .app-wrapper {
        background-color: var(--bg-dashboard);
        min-height: 100vh;
        display: flex;
    }

    .main-content {
        margin-left: 260px; 
        width: calc(100% - 260px);
        padding: 40px; /* Padding dikembalikan normal karena bug melayang sudah diatasi */
        background-color: var(--bg-dashboard);
        min-height: 100vh;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    .main-content * {
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    /* --- TOPBAR AREA (KODE YANG DIPERBAIKI UNTUK MENCEGAH OVERLAP) --- */
    .topbar {
        position: relative !important; /* Memaksa topbar tidak melayang */
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important; 
        padding-bottom: 24px !important; 
        margin-bottom: 32px !important;  
        border-bottom: 2px solid var(--border-color) !important; 
        background: transparent !important;
        width: 100% !important;
        height: auto !important; /* Mereset tinggi jika sebelumnya di-set dari header.php */
        z-index: 10 !important;
    }

    .topbar-judul {
        font-size: 28px; 
        font-weight: 800; 
        color: var(--text-dark);
        letter-spacing: -0.75px;
        line-height: 1.2;
    }

    .topbar-sub {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 6px;
        font-weight: 500;
    }

    .btn.btn-utama {
        display: inline-flex;
        align-items: center;
        justify-content: center; /* Memastikan teks di tengah setelah ikon dihapus */
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--deep-blue) 100%);
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        border-radius: 12px; 
        border: none;
        box-shadow: 0 4px 14px rgba(29, 78, 216, 0.25);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn.btn-utama:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(29, 78, 216, 0.4);
    }

    /* --- FORM & CARD OVERRIDE --- */
    .card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
        background: transparent !important;
    }

    .card-judul {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .btn-abu {
        font-size: 12px;
        font-weight: 600;
        color: var(--royal-blue);
        text-decoration: none;
        padding: 8px 16px;
        background: #eff6ff;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-abu:hover {
        background: var(--royal-blue);
        color: #ffffff;
    }

    .btn-merah {
        font-size: 12px;
        font-weight: 600;
        color: #dc2626;
        text-decoration: none;
        padding: 8px 12px;
        background: #fee2e2;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-merah:hover {
        background: #dc2626;
        color: #ffffff;
    }

    /* CARI & FILTER AREA STYLE */
    .cari-filter {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .input-cari {
        flex: 1;
        min-width: 200px;
        padding: 10px 16px;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }

    .input-cari:focus {
        border-color: var(--royal-blue);
    }

    .cari-filter select {
        padding: 10px 16px;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        font-size: 14px;
        background-color: #fff;
        outline: none;
    }

    /* --- TABEL DESIGN --- */
    .tabel-wrapper {
        overflow-x: auto;
    }

    .tabel-wrapper table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .tabel-wrapper th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 12px 14px;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #f1f5f9;
    }

    .tabel-wrapper td {
        padding: 16px 14px;
        font-size: 14px;
        color: var(--text-dark);
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .tabel-wrapper tbody tr:hover {
        background-color: #f8fafc;
    }

    /* BAR KUOTA PROGRESS */
    .kuota-bar {
        background-color: #f1f5f9;
        border-radius: 99px;
        overflow: hidden;
    }
    .kuota-isi {
        height: 100%;
        border-radius: 99px;
    }

    /* BADGE UNIFORM */
    .badge, [class*="badge-"], [class*="status-"] {
        display: inline-flex !important;
        align-items: center;
        padding: 4px 10px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        text-transform: capitalize !important;
        border: none !important;
    }
    .badge-success, .status-published, .status-confirmed, .badge-aktif { background: #d1fae5 !important; color: #065f46 !important; }
    .badge-warning, .status-draft, .badge-pending { background: #fef3c7 !important; color: #92400e !important; }
    .badge-danger, .status-cancelled, .status-selesai { background: #fee2e2 !important; color: #991b1b !important; }

    /* PAGINASI UNIFORM */
    .paginasi {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 24px;
        font-size: 13px;
        color: var(--text-muted);
    }

    .paginasi-tombol {
        display: flex;
        gap: 6px;
    }

    .paginasi-tombol a, .paginasi-tombol span {
        padding: 8px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-dark);
        font-weight: 500;
    }

    .paginasi-tombol a.aktif {
        background: var(--royal-blue);
        color: #ffffff;
        border-color: var(--royal-blue);
    }

    .paginasi-tombol .nonaktif {
        color: #cbd5e1;
        cursor: not-allowed;
    }

    /* FORM GROUP UTILITY */
    .form-grup {
        margin-bottom: 18px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-grup label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .form-grup input, .form-grup select, .form-grup textarea {
        padding: 10px 14px;
        border: 1.5px solid var(--border-color);
        border-radius: 10px;
        font-size: 14px;
        outline: none;
    }

    .form-grup input:focus, .form-grup select:focus, .form-grup textarea:focus {
        border-color: var(--royal-blue);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }


    .btn-menu {
        display: none;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-dark);
        margin-right: 14px;
        padding: 0;
    }

    /* RESPONSIVE FILTER & CONTENT */
    @media (max-width: 1024px) {
        .main-content { margin-left: 0; width: 100%; padding: 40px 24px 24px 24px; }
        .btn-menu { display: block; }
    }

    @media (max-width: 640px) {
        .topbar { flex-direction: column; align-items: flex-start; gap: 16px; }
        .btn.btn-utama { width: 100%; justify-content: center; }
        .form-row { grid-template-columns: 1fr; }
        .cari-filter { flex-direction: column; }
        .cari-filter * { width: 100%; }
    }
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>
    
    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri" style="display:flex; align-items:center;">
                <button class="btn-menu" id="btnMenu"></button>
                <div>
                    <div class="topbar-judul">Kelola Event</div>
                    <div class="topbar-sub">Tambah, edit, dan hapus event kampus</div>
                </div>
            </div>
            <div class="topbar-kanan">
                <a href="?aksi=tambah" class="btn btn-utama">
                    Tambah Event
                </a>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>

            <?php if (!empty($errors ?? [])): ?>
                <div class="alert alert-error" style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:14px; font-weight:500;">
                    <?php foreach ($errors as $e) echo "• $e<br>"; ?>
                </div>
            <?php endif; ?>

            <?php if ($aksi === 'tambah' || $aksi === 'edit'): ?>
            <div class="card" style="margin-bottom:32px">
                <div class="card-header">
                    <span class="card-judul"><?= $aksi === 'edit' ? 'Edit Event' : 'Tambah Event Baru' ?></span>
                    <a href="event.php" class="btn-abu" style="padding: 6px 14px;">Kembali</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($aksi === 'edit'): ?>
                            <input type="hidden" name="id_event" value="<?= $edit_data['id_event'] ?>">
                        <?php endif; ?>

                        <div class="form-grup">
                            <label>Nama Event <span style="color:red">*</span></label>
                            <input type="text" name="nama_event" required
                                   placeholder="Contoh: Seminar Nasional Teknologi 2026"
                                   value="<?= htmlspecialchars($edit_data['nama_event'] ?? $_POST['nama_event'] ?? '') ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-grup">
                                <label>Kategori <span style="color:red">*</span></label>
                                <select name="id_kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php
                                    mysqli_data_seek($list_kategori, 0);
                                    while ($k = mysqli_fetch_assoc($list_kategori)):
                                        $sel = ($edit_data['id_kategori'] ?? $_POST['id_kategori'] ?? '') == $k['id_kategori'] ? 'selected' : '';
                                    ?>
                                    <option value="<?= $k['id_kategori'] ?>" <?= $sel ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-grup">
                                <label>Ruangan <span style="color:red">*</span></label>
                                <select name="id_ruangan" required>
                                    <option value="">Pilih Ruangan</option>
                                    <?php
                                    mysqli_data_seek($list_ruangan, 0);
                                    while ($r = mysqli_fetch_assoc($list_ruangan)):
                                        $sel = ($edit_data['id_ruangan'] ?? $_POST['id_ruangan'] ?? '') == $r['id_ruangan'] ? 'selected' : '';
                                    ?>
                                    <option value="<?= $r['id_ruangan'] ?>" <?= $sel ?>>
                                        <?= htmlspecialchars($r['nama_ruangan']) ?> (Kapasitas: <?= $r['kapasitas'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-grup">
                            <label>Deskripsi Event</label>
                            <textarea name="deskripsi" rows="4" placeholder="Jelaskan tentang event ini..."><?= htmlspecialchars($edit_data['deskripsi'] ?? $_POST['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-grup">
                                <label>Tanggal Mulai <span style="color:red">*</span></label>
                                <input type="date" name="tanggal_mulai" required
                                       value="<?= $edit_data['tanggal_mulai'] ?? $_POST['tanggal_mulai'] ?? '' ?>">
                            </div>
                            <div class="form-grup">
                                <label>Waktu Mulai <span style="color:red">*</span></label>
                                <input type="time" name="waktu_mulai" required
                                       value="<?= $edit_data['waktu_mulai'] ?? $_POST['waktu_mulai'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-grup">
                                <label>Kapasitas Peserta <span style="color:red">*</span></label>
                                <input type="number" name="kapasitas" min="1" required
                                       placeholder="Jumlah maksimal peserta"
                                       value="<?= $edit_data['kapasitas'] ?? $_POST['kapasitas'] ?? '' ?>">
                            </div>
                            <div class="form-grup">
                                <label>Pembicara / Panitia</label>
                                <input type="text" name="pembicara"
                                       placeholder="Nama pembicara atau penyelenggara"
                                       value="<?= htmlspecialchars($edit_data['pembicara'] ?? $_POST['pembicara'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-grup">
                                <label>Jenis Tiket</label>
                                <select name="jenis_tiket" onchange="document.getElementById('grup_harga').style.display = (this.value === 'berbayar') ? 'block' : 'none'">
                                    <option value="gratis" <?= ($edit_data['jenis_tiket'] ?? '') == 'gratis' ? 'selected' : '' ?>>Gratis</option>
                                    <option value="berbayar" <?= ($edit_data['jenis_tiket'] ?? '') == 'berbayar' ? 'selected' : '' ?>>Berbayar</option>
                                </select>
                            </div>
                            <div class="form-grup" id="grup_harga" style="display: <?= ($edit_data['jenis_tiket'] ?? 'gratis') == 'berbayar' ? 'block' : 'none' ?>;">
                                <label>Harga Tiket (Rp)</label>
                                <input type="number" name="harga" value="<?= $edit_data['harga'] ?? 0 ?>" placeholder="Contoh: 50000">
                            </div>
                        </div>

                        <div class="form-grup">
                            <label>Status Publikasi</label>
                            <select name="status">
                                <?php
                                $statuses = ['draft' => 'Draft (Belum Dipublikasi)', 'published' => 'Published (Aktif)', 'selesai' => 'Selesai'];
                                foreach ($statuses as $val => $label):
                                    $sel = ($edit_data['status'] ?? 'draft') === $val ? 'selected' : '';
                                ?>
                                <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display:flex;gap:10px;margin-top:24px">
                            <button type="submit" class="btn btn-utama">
                                <?= $aksi === 'edit' ? 'Simpan Perubahan' : 'Tambah Event' ?>
                            </button>
                            <a href="event.php" class="btn-abu" style="text-align:center; display:inline-flex; align-items:center;">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <span class="card-judul">Daftar Event Kampus (<?= $total_rows ?>)</span>
                </div>

                <form method="GET" action="">
                    <div class="cari-filter">
                        <input type="text" class="input-cari" name="cari"
                               placeholder="Cari nama event..."
                               value="<?= htmlspecialchars($cari) ?>">
                        <select name="kategori">
                            <option value="">Semua Kategori</option>
                            <?php
                            mysqli_data_seek($list_kategori, 0);
                            while ($k = mysqli_fetch_assoc($list_kategori)):
                                $sel = $filter_kat == $k['id_kategori'] ? 'selected' : '';
                            ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= $sel ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="draft"     <?= $filter_status === 'draft'     ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $filter_status === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="selesai"   <?= $filter_status === 'selesai'   ? 'selected' : '' ?>>Selesai</option>
                        </select>
                        <button type="submit" class="btn btn-utama" style="padding: 10px 20px; border-radius:10px;">Cari</button>
                        <a href="event.php" class="btn-abu" style="display:inline-flex; align-items:center;">Reset</a>
                    </div>
                </form>

                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px">No</th>
                                <th>Nama Event</th>
                                <th>Kategori & Tiket</th>
                                <th>Tanggal & Waktu</th>
                                <th>Ruangan & Kuota</th>
                                <th>Status</th>
                                <th style="width: 100px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list_event) === 0): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px;color:#64748b; font-weight: 500;">
                                     Tidak ada data event ditemukan.
                                </td>
                            </tr>
                            <?php else: $no = $offset + 1; while ($row = mysqli_fetch_assoc($list_event)): ?>
                            <tr>
                                <td><strong><?= $no++ ?></strong></td>
                                <td>
                                    <span style="font-weight:700; color:var(--text-dark)"><?= htmlspecialchars($row['nama_event']) ?></span><br>
                                    <?php if ($row['pembicara']): ?>
                                    <small style="color:var(--text-muted); font-weight: 500;">Oleh <?= htmlspecialchars($row['pembicara']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: #475569; font-size:13px;"><?= htmlspecialchars($row['nama_kategori']) ?></span><br>
                                    <span style="font-weight:700;color:var(--royal-blue); font-size:12px;">
                                        <?= $row['jenis_tiket'] === 'berbayar' ? 'Rp ' . number_format($row['harga'], 0, ',', '.') : 'Gratis' ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--text-dark);"><?= formatTanggal($row['tanggal_mulai']) ?></span><br>
                                    <small style="color:var(--text-muted); font-weight:500;"><?= formatWaktu($row['waktu_mulai']) ?> WIB</small>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($row['nama_ruangan']) ?></span><br>
                                    <?php $sisa = $row['kapasitas'] - $row['jumlah_daftar']; ?>
                                    <small style="color: var(--text-muted); font-weight:600;"><?= $row['jumlah_daftar'] ?>/<?= $row['kapasitas'] ?> Terisi</small>
                                    <div class="kuota-bar" style="width:100px; height:5px; margin-top:4px;">
                                        <div class="kuota-isi" style="width:<?= min(100, round($row['jumlah_daftar']/$row['kapasitas']*100)) ?>%;background:<?= $sisa <= 0 ? '#dc2626' : 'var(--royal-blue)' ?>"></div>
                                    </div>
                                </td>
                                <td><?= badgeStatus($row['status']) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($row['penyelenggara'] === $admin_org || $admin_org === 'Pusat'): ?>
                                        <div style="display:flex; gap:6px; justify-content: center;">
                                            <a href="?aksi=edit&id=<?= $row['id_event'] ?>" class="btn-abu" style="padding: 6px 10px; font-size:13px;" title="Edit">Edit</a>
                                            <a href="?aksi=hapus&id=<?= $row['id_event'] ?>" class="btn-merah btn-hapus-custom" data-nama="<?= htmlspecialchars($row['nama_event']) ?>" style="padding: 6px 10px; font-size:13px;" title="Hapus">Hapus</a>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge" style="background:#f1f5f9; color:#475569; padding: 4px 8px; font-size:11px;">Milik <?= htmlspecialchars($row['penyelenggara']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_hal > 1): ?>
                <div class="paginasi">
                    <span style="font-weight: 500;">Menampilkan <?= $offset + 1 ?>–<?= min($offset + $per_halaman, $total_rows) ?> dari <?= $total_rows ?> total event</span>
                    <div class="paginasi-tombol">
                        <?php if ($hal_ini > 1): ?>
                            <a href="?hal=<?= $hal_ini-1 ?>&cari=<?= urlencode($cari) ?>&kategori=<?= $filter_kat ?>&status=<?= $filter_status ?>">← Prev</a>
                        <?php else: ?>
                            <span class="nonaktif">← Prev</span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_hal; $i++): ?>
                            <a href="?hal=<?= $i ?>&cari=<?= urlencode($cari) ?>&kategori=<?= $filter_kat ?>&status=<?= $filter_status ?>" class="<?= $i === $hal_ini ? 'aktif' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($hal_ini < $total_hal): ?>
                            <a href="?hal=<?= $hal_ini+1 ?>&cari=<?= urlencode($cari) ?>&kategori=<?= $filter_kat ?>&status=<?= $filter_status ?>">Next →</a>
                        <?php else: ?>
                            <span class="nonaktif">Next →</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
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
            sidebar.style.transform = 'translateX(0)';
            sidebar.style.left = '0';
            overlay.style.display = 'block';
        });

        overlay.addEventListener('click', () => {
            sidebar.style.transform = 'translateX(-100%)';
            overlay.style.display = 'none';
        });
    }
</script>

<script>
document.querySelectorAll('.btn-hapus-custom').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault(); 
        
        const urlHapus = this.getAttribute('href');
        const namaEvent = this.getAttribute('data-nama');

        Swal.fire({
            title: 'Hapus Event?',
            text: `Yakin ingin menghapus "${namaEvent}"? Data tidak bisa dikembalikan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6', 
            cancelButtonColor: '#d33',     
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-4' 
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = urlHapus;
            }
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>