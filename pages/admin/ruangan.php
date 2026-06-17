<?php
// ========================================================================
// pages/admin/ruangan.php — Halaman Kelola Ruangan
// ========================================================================

// ========================================================================
// 1. PENGATURAN AWAL & AUTENTIKASI
// ========================================================================
session_start(); // Memulai sesi untuk mengenali pengguna yang login
require_once '../../config/koneksi.php'; // Menghubungkan ke database
require_once '../../includes/functions.php'; // Memuat fungsi-fungsi bantuan (seperti bersihkan(), redirect(), dll)

// Memastikan hanya Admin yang sudah login yang bisa mengakses halaman ini. 
// Jika bukan admin, fungsi ini akan otomatis melempar user ke halaman login/lainnya.
wajibAdmin();

// Variabel untuk informasi halaman
$judul_halaman = 'Kelola Ruangan';
$halaman_aktif = 'ruangan';
$errors = []; // Array untuk menampung pesan error jika form tidak valid

// ========================================================================
// 2. PROSES TAMBAH / UPDATE DATA RUANGAN (Jika ada pengiriman form/POST)
// ========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil dan membersihkan data dari form untuk mencegah serangan XSS/SQL Injection
    $nama      = bersihkan($_POST['nama_ruangan'] ?? '');
    $kapasitas = (int)($_POST['kapasitas'] ?? 0);
    $id        = (int)($_POST['id_ruangan'] ?? 0);

    // Validasi input form: pastikan nama terisi dan kapasitas lebih dari 0
    if (empty($nama))    $errors[] = 'Nama ruangan wajib diisi.';
    if ($kapasitas <= 0) $errors[] = 'Kapasitas harus lebih dari 0.';

    // Jika tidak ada error pada validasi, lanjutkan proses simpan ke database
    if (empty($errors)) {
        if ($id > 0) {
            // Mode EDIT: Update data lama di database berdasarkan ID
            mysqli_query($koneksi, "UPDATE ruangan SET nama_ruangan='$nama', kapasitas=$kapasitas WHERE id_ruangan=$id");
            redirectDenganPesan('ruangan.php', 'Ruangan berhasil diperbarui!');
        } else {
            // Mode TAMBAH: Masukkan data baru ke tabel ruangan
            mysqli_query($koneksi, "INSERT INTO ruangan (nama_ruangan, kapasitas) VALUES ('$nama', $kapasitas)");
            redirectDenganPesan('ruangan.php', 'Ruangan berhasil ditambahkan!');
        }
    }
}

// ========================================================================
// 3. PROSES HAPUS RUANGAN (Jika diklik tombol Hapus)
// ========================================================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id = (int)$_GET['id']; // Mengambil ID dari URL
    
    // Validasi pencegahan hapus: Cek apakah ruangan masih dipakai di tabel event
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM event WHERE id_ruangan=$id"));
    
    if ($cek['total'] > 0) {
        // Jika masih ada event yang memakai ruangan ini, tolak penghapusan
        redirectDenganPesan('ruangan.php', 'Ruangan tidak bisa dihapus karena masih digunakan!', 'error');
    } else {
        // Jika aman, eksekusi query hapus
        mysqli_query($koneksi, "DELETE FROM ruangan WHERE id_ruangan=$id");
        redirectDenganPesan('ruangan.php', 'Ruangan berhasil dihapus.');
    }
}

// ========================================================================
// 4. AMBIL DATA RUANGAN (Untuk Ditampilkan di Tabel & Form Edit)
// ========================================================================

// Mengecek apakah user sedang mengklik tombol Edit (ada parameter 'aksi=edit' di URL)
$edit_data = null;
if (isset($_GET['aksi']) && $_GET['aksi'] === 'edit') {
    // Mengambil satu baris data ruangan yang dipilih untuk diisi ke dalam form
    $edit_data = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT * FROM ruangan WHERE id_ruangan=" . (int)$_GET['id']));
}

// Mengambil semua data ruangan untuk ditampilkan di tabel daftar.
// Menggunakan LEFT JOIN ke tabel event untuk menghitung berapa event yang memakai ruangan ini (Terpakai).
$list_ruangan = mysqli_query($koneksi,
    "SELECT r.*, COUNT(e.id_event) as jumlah_event
     FROM ruangan r
     LEFT JOIN event e ON r.id_ruangan = e.id_ruangan
     GROUP BY r.id_ruangan
     ORDER BY r.nama_ruangan"
);
?>

<?php include '../../includes/header.php'; ?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Deklarasi variabel warna utama agar mudah diubah-ubah */
    :root {
        --royal-blue: #1d4ed8;       
        --deep-blue: #1e40af;        
        --bg-dashboard: #f8fafc;     
        --text-dark: #0f172a;        
        --text-muted: #64748b;       
        --border-color: #e2e8f0;     
    }

    /* Reset margin dan pengaturan background utama */
    body { background-color: var(--bg-dashboard); margin: 0; }
    .app-wrapper { background-color: var(--bg-dashboard); min-height: 100vh; display: flex; }
    
    /* Pengaturan ruang kerja (kanan dari sidebar) */
    .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 40px; background-color: var(--bg-dashboard); min-height: 100vh; box-sizing: border-box; transition: all 0.3s ease; }
    
    /* --- PENGATURAN TOPBAR (DIPERBAIKI AGAR TETAP DIAM DI ATAS/STICKY) --- */
    .topbar { 
        position: sticky !important; 
        top: 0; 
        z-index: 50; /* Memastikan topbar berada di atas elemen lain saat discroll */
        background-color: var(--bg-dashboard); /* Wajib ada warna agar konten di bawah tidak tembus */
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding-top: 20px; /* Tambahan padding atas agar tidak terlalu rapat ke tepi browser */
        padding-bottom: 24px; 
        margin-bottom: 36px; 
        border-bottom: 2px solid var(--border-color); 
        width: 100%; 
    }
    .topbar-judul { font-size: 28px; font-weight: 800; color: var(--text-dark); }
    .topbar-sub { font-size: 14px; color: var(--text-muted); margin-top: 6px; }

    /* --- PENGATURAN KOMPONEN KARTU (KOTAK PUTIH) --- */
    .card { background: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01); margin-bottom: 20px; }
    .grid-container { display: grid; grid-template-columns: 340px 1fr; gap: 28px; align-items: start; }
    .card-judul-inline { margin: 0 0 20px 0; font-size: 16px; font-weight: 700; color: var(--text-dark); }
    
    /* --- PENGATURAN FORM --- */
    .form-grup { margin-bottom: 20px; }
    .form-grup label { display: block; font-size: 13px; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
    .input-premium { width: 100%; height: 42px; padding: 0 16px; border-radius: 10px; border: 1px solid var(--border-color); }
    .btn-submit { width: 100%; height: 42px; background: var(--royal-blue); color: white; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; }
    .btn-batal { display: block; text-align: center; margin-top: 10px; color: var(--text-muted); text-decoration: none; font-size: 13px; }
    
    /* --- PENGATURAN TABEL --- */
    .tabel-wrapper { overflow-x: auto; }
    .tabel-wrapper table { width: 100%; border-collapse: collapse; }
    .tabel-wrapper th { font-size: 11px; text-transform: uppercase; color: var(--text-muted); padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
    .tabel-wrapper td { padding: 16px 14px; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
    
    /* --- TOMBOL AKSI BERWARNA (Edit & Hapus) --- */
    .btn-aksi { 
        display: inline-block;
        padding: 6px 16px; 
        border-radius: 8px; 
        text-decoration: none; 
        font-size: 12px; 
        font-weight: 600;
        color: #ffffff; 
        margin: 0 4px;
        transition: all 0.2s ease-in-out;
        border: none;
    }
    .btn-aksi:hover { opacity: 0.85; transform: translateY(-1px); } /* Efek hover pada tombol */
    .btn-edit { background-color: #3B82F6; } /* Warna Biru untuk Edit */
    .btn-hapus { background-color: #EF4444; } /* Warna Merah untuk Hapus */

    /* Pengaturan untuk perangkat layar kecil (HP/Tablet) */
    .btn-menu { display: none; background: none; border: none; font-size: 24px; cursor: pointer; margin-right: 14px; }
    @media (max-width: 1024px) { .main-content { margin-left: 0; width: 100%; padding: 40px 24px; } .btn-menu { display: block; } .grid-container { grid-template-columns: 1fr; } }
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:90"></div>
    
    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div style="display: flex; align-items: center;">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div>
                    <div class="topbar-judul">Kelola Ruangan</div>
                    <div class="topbar-sub">Atur operasional, kapasitas, dan alokasi tempat</div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert-error" style="background:#fef2f2; padding:12px; border-radius:10px; margin-bottom:20px; color:#991b1b;">
                    <?= implode('<br>', $errors) ?>
                </div>
            <?php endif; ?>

            <div class="grid-container">
                <div class="card">
                    <h3 class="card-judul-inline"><?= $edit_data ? 'Edit Data Ruangan' : 'Tambah Ruangan Baru' ?></h3>
                    
                    <form method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id_ruangan" value="<?= $edit_data['id_ruangan'] ?>">
                        <?php endif; ?>
                        
                        <div class="form-grup">
                            <label>Nama Ruangan *</label>
                            <input type="text" class="input-premium" name="nama_ruangan" required value="<?= htmlspecialchars($edit_data['nama_ruangan'] ?? '') ?>">
                        </div>
                        
                        <div class="form-grup">
                            <label>Kapasitas (orang) *</label>
                            <input type="number" class="input-premium" name="kapasitas" min="1" required value="<?= htmlspecialchars($edit_data['kapasitas'] ?? '') ?>">
                        </div>
                        
                        <button type="submit" class="btn-submit"><?= $edit_data ? 'Simpan Perubahan' : 'Tambah Ruangan' ?></button>
                        
                        <?php if ($edit_data): ?>
                            <a href="ruangan.php" class="btn-batal">Batal Edit</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card">
                    <h3 class="card-judul-inline">Daftar Ruangan</h3>
                    <div class="tabel-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align: center;">No</th>
                                    <th>Nama Ruangan</th>
                                    <th>Kapasitas</th>
                                    <th>Terpakai</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($list_ruangan) === 0): ?>
                                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">Belum ada ruangan terdaftar.</td></tr>
                                <?php else: $no = 1; while ($row = mysqli_fetch_assoc($list_ruangan)): ?>
                                <tr>
                                    <td style="text-align: center;"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['nama_ruangan']) ?></td>
                                    <td><?= number_format($row['kapasitas']) ?> orang</td>
                                    <td><?= $row['jumlah_event'] ?> event</td>
                                    <td style="text-align: center;">
                                        <a href="?aksi=edit&id=<?= $row['id_ruangan'] ?>" class="btn-aksi btn-edit">Edit</a>
                                        <a href="#" onclick="konfirmasiHapus('?aksi=hapus&id=<?= $row['id_ruangan'] ?>', '<?= htmlspecialchars($row['nama_ruangan']) ?>')" class="btn-aksi btn-hapus">Hapus</a>
                                    </td>
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

<script>
    // JS: Fungsi untuk menampilkan alert konfirmasi sebelum menghapus data
    function konfirmasiHapus(url, nama) {
        if (confirm("Yakin hapus ruangan \"" + nama + "\"?")) {
            window.location.href = url; // Jika user menekan "OK", halaman dialihkan ke link hapus
        }
    }
    
    // JS: Logika responsif untuk menampilkan sidebar di layar mobile
    const btnMenu = document.getElementById('btnMenu');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if(btnMenu && sidebar && overlay) {
        btnMenu.addEventListener('click', () => { sidebar.style.transform = 'translateX(0)'; overlay.style.display = 'block'; });
        overlay.addEventListener('click', () => { sidebar.style.transform = 'translateX(-100%)'; overlay.style.display = 'none'; });
    }
</script>

<?php include '../../includes/footer.php'; ?>