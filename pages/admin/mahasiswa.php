<?php
// ============================================
// pages/admin/mahasiswa.php — Lihat Data Mahasiswa (Perfect Spacing Edition)
// ============================================

// 1. INISIALISASI & AUTENTIKASI
session_start(); // Memulai sesi untuk mengecek status login pengguna
require_once '../../config/koneksi.php'; // Memanggil file koneksi database
require_once '../../includes/functions.php'; // Memanggil fungsi bantuan (bersihkan, redirect, dll)
wajibAdmin(); // Memastikan hanya pengguna dengan role Admin yang bisa mengakses halaman ini

// 2. PENGATURAN INFORMASI HALAMAN
$judul_halaman = 'Data Mahasiswa';
$halaman_aktif = 'mahasiswa';

// 3. PENGATURAN PENCARIAN & FILTER
// Mengambil keyword pencarian dan filter prodi dari URL (metode GET), dibersihkan untuk mencegah XSS
$cari         = bersihkan($_GET['cari'] ?? '');
$filter_prodi = bersihkan($_GET['prodi'] ?? '');

// 4. PENGATURAN PAGINATION (HALAMAN)
$per_halaman  = 10; // Jumlah baris data yang ditampilkan per halaman
$hal_ini      = max(1, (int)($_GET['hal'] ?? 1)); // Mendapatkan nomor halaman saat ini (minimal 1)
$offset       = ($hal_ini - 1) * $per_halaman; // Menghitung data dimulai dari indeks ke berapa

// 5. PENYUSUNAN QUERY KONDISIONAL
$where = "WHERE 1=1"; // Kondisi dasar (selalu benar) agar mudah digabungkan dengan AND
// Jika ada input pencarian, tambahkan kondisi filter nama atau NIM
if ($cari)         $where .= " AND (pm.nama_lengkap LIKE '%$cari%' OR pm.nim LIKE '%$cari%')";
// Jika ada filter prodi yang dipilih, tambahkan kondisi filter prodi
if ($filter_prodi) $where .= " AND pm.prodi='$filter_prodi'";

// 6. MENGHITUNG TOTAL DATA UNTUK PAGINATION
// Mengambil total keseluruhan baris data yang sesuai dengan kriteria pencarian/filter
$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT COUNT(*) as total FROM profil_mahasiswa pm $where"))['total'];
$total_hal = ceil($total_rows / $per_halaman); // Membulatkan ke atas untuk total halaman

// 7. MENGAMBIL DATA MAHASISWA (BESERTA TOTAL REGISTRASI EVENT)
// Menggunakan JOIN untuk mengambil username dari tabel users, dan LEFT JOIN untuk menghitung event
$list = mysqli_query($koneksi,
    "SELECT pm.*, u.username, u.created_at,
            COUNT(r.id_registrasi) as total_registrasi
     FROM profil_mahasiswa pm
     JOIN users u ON pm.id_user = u.id_user
     LEFT JOIN registrasi r ON pm.id_mahasiswa = r.id_mahasiswa
     $where
     GROUP BY pm.id_mahasiswa
     ORDER BY pm.nama_lengkap
     LIMIT $per_halaman OFFSET $offset" // Membatasi data yang diambil sesuai pagination
);

// 8. MENGAMBIL DATA FILTER PRODI
// Mengambil daftar program studi yang ada untuk ditampilkan di dropdown filter
$list_prodi = mysqli_query($koneksi,
    "SELECT DISTINCT prodi FROM profil_mahasiswa ORDER BY prodi");
?>
<?php include '../../includes/header.php'; // Memuat bagian <head> dan tag awal HTML ?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ============================================
       PENGATURAN VARIABEL WARNA
       ============================================ */
    :root {
        --royal-blue: #1d4ed8;       
        --deep-blue: #1e40af;        
        --bg-dashboard: #f8fafc;     
        --text-dark: #0f172a;        
        --text-muted: #64748b;       
        --border-color: #e2e8f0;     
    }

    /* ============================================
       RESET & LAYOUT UTAMA
       ============================================ */
    body {
        background-color: var(--bg-dashboard);
        margin: 0;
    }

    /* Membungkus seluruh aplikasi (Sidebar + Konten Kanan) */
    .app-wrapper {
        background-color: var(--bg-dashboard);
        min-height: 100vh;
        display: flex;
    }

    /* Area konten utama di sebelah kanan sidebar */
    .main-content {
        margin-left: 260px; /* Menyisakan ruang untuk sidebar */
        width: calc(100% - 260px);
        padding: 0 40px 40px 40px; /* Padding atas 0 agar topbar bisa menempel sempurna */
        background-color: var(--bg-dashboard);
        min-height: 100vh;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    .main-content * {
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }

    /* ============================================
       TOPBAR (HEADER ATAS) - DIPERBAIKI UNTUK STICKY
       ============================================ */
    .topbar {
        position: sticky !important; /* Membuat elemen ini menempel saat discroll */
        top: 0; /* Menempel di titik paling atas layar */
        z-index: 50; /* Memastikan elemen ini berada di atas (menutupi) tabel saat discroll */
        background-color: var(--bg-dashboard); /* Wajib ada warna agar tabel di bawahnya tidak terlihat tembus pandang */
        display: flex;
        justify-content: space-between;
        align-items: center; 
        padding-top: 40px; /* Jarak atas diletakkan di sini, bukan di .main-content */
        padding-bottom: 24px; 
        margin-bottom: 36px;  
        border-bottom: 2px solid var(--border-color); 
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

    /* Tombol hamburger menu (Hanya muncul di HP/Tablet) */
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

    /* ============================================
       KOMPONEN KARTU & FORM
       ============================================ */
    .card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.01);
    }

    .card-header-inline {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .card-judul-inline {
        margin: 0; 
        font-size: 16px; 
        font-weight: 700; 
        color: var(--text-dark);
    }

    /* Baris untuk kotak pencarian dan dropdown filter */
    .cari-filter {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap; /* Mengizinkan elemen turun ke bawah jika layar sempit */
    }

    .input-cari {
        height: 42px;
        padding: 0 16px;
        font-size: 14px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        color: var(--text-dark);
        background-color: #ffffff;
        width: 280px;
        transition: all 0.2s ease;
    }

    .input-cari:focus, .select-premium:focus {
        outline: none;
        border-color: var(--royal-blue); /* Mengubah warna border saat diklik */
        box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.15); /* Memberikan efek cahaya biru */
    }

    .select-premium {
        height: 42px;
        padding: 0 36px 0 16px;
        font-size: 14px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        color: var(--text-dark);
        background-color: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-cari-premium {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 22px;
        background: linear-gradient(135deg, var(--royal-blue) 0%, var(--deep-blue) 100%);
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        border-radius: 10px; 
        border: none;
        box-shadow: 0 4px 14px rgba(29, 78, 216, 0.25);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-cari-premium:hover {
        transform: translateY(-1px); /* Efek tombol naik sedikit saat dihover */
        box-shadow: 0 6px 20px rgba(29, 78, 216, 0.35);
    }

    .btn-batal-premium {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 42px;
        padding: 0 20px;
        background: #f1f5f9;
        color: #475569;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s;
    }

    .btn-batal-premium:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    /* ============================================
       TABEL PRESET SYSTEM
       ============================================ */
    .tabel-wrapper {
        overflow-x: auto; /* Memastikan tabel bisa digeser ke kanan di layar kecil */
    }

    .tabel-wrapper table {
        width: 100%;
        border-collapse: collapse; /* Menghilangkan jarak antar border cell */
        text-align: left;
    }

    /* Styling Kepala Tabel */
    .tabel-wrapper th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 12px 14px;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Styling Isi Tabel */
    .tabel-wrapper td {
        padding: 16px 14px;
        font-size: 13.5px;
        color: var(--text-dark);
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .tabel-wrapper tr:last-child td {
        border-bottom: none; /* Menghilangkan border bawah pada baris terakhir */
    }

    .tabel-wrapper tbody tr:hover {
        background-color: #f8fafc; /* Efek highlight saat baris ditunjuk mouse */
    }

    /* ============================================
       ELEMEN VISUAL TAMBAHAN (Avatar & Lencana)
       ============================================ */
    /* Membuat lingkaran dengan inisial huruf pertama nama */
    .avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--royal-blue);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Styling lencana/badge biru terang untuk informasi jumlah event */
    .badge-info-premium {
        background-color: #e0f2fe;
        color: #0369a1;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }

    /* Teks yang muncul jika data tidak ditemukan */
    .empty-state {
        text-align: center;
        padding: 40px 20px !important;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 500;
    }

    /* ============================================
       RESPONSIVE LAYOUT (UNTUK HP & TABLET)
       ============================================ */
    @media (max-width: 1024px) {
        /* Sidebar disembunyikan otomatis, lebar konten menjadi 100% */
        .main-content { margin-left: 0; width: 100%; padding: 0 24px 24px 24px; }
        .topbar { padding-top: 40px; }
        .btn-menu { display: block; } /* Tombol hamburger dimunculkan */
    }

    @media (max-width: 640px) {
        /* Elemen topbar dan filter diubah menjadi susunan vertikal */
        .topbar { flex-direction: column; align-items: flex-start; gap: 16px; }
        .input-cari, .select-premium, .btn-cari-premium, .btn-batal-premium { 
            width: 100%; 
            justify-content: center; 
        }
    }
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:90"></div>

    <?php include '../../includes/sidebar_admin.php'; // Memuat tampilan navigasi menu sebelah kiri ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri" style="display:flex; align-items:center;">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div>
                    <div class="topbar-judul">Data Mahasiswa</div>
                    <div class="topbar-sub">Arsip dan direktori database pengguna mahasiswa terdaftar sistem</div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); // Memanggil pesan sukses/error jika ada aksi sebelumnya ?>
            
            <div class="card">
                <div class="card-header-inline">
                    <span style="font-size: 16px;"></span>
                    <h3 class="card-judul-inline">Direktori Mahasiswa (<?= number_format($total_rows) ?>)</h3>
                </div>

                <form method="GET">
                    <div class="cari-filter">
                        <input type="text" class="input-cari" name="cari" placeholder="Cari nama atau NIM..." value="<?= htmlspecialchars($cari) ?>">
                        
                        <select name="prodi" class="select-premium">
                            <option value="">Semua Program Studi</option>
                            <?php while ($p = mysqli_fetch_assoc($list_prodi)): ?>
                                <option value="<?= htmlspecialchars($p['prodi']) ?>" <?= $filter_prodi === $p['prodi'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['prodi']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        
                        <button type="submit" class="btn-cari-premium">
                            <span>Cari</span>
                        </button>
                        
                        <a href="mahasiswa.php" class="btn-batal-premium">Reset</a>
                    </div>
                </form>

                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">No</th>
                                <th>Nama Lengkap</th>
                                <th>NIM</th>
                                <th>Program Studi</th>
                                <th>Email Berkas</th>
                                <th>Username</th>
                                <th style="text-align: center;">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list) === 0): ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    Tidak ditemukan arsip data mahasiswa yang sesuai dengan pencarian.
                                </td>
                            </tr>
                            <?php else: 
                                // Jika ada, lakukan perulangan. Nomor disesuaikan dengan offset pagination
                                $no = $offset + 1; 
                                while ($row = mysqli_fetch_assoc($list)): 
                            ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);"><?= $no++ ?></td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px">
                                        <div class="avatar-circle">
                                            <?= strtoupper(substr($row['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <span style="font-weight:600; color:var(--text-dark)"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size:12px; font-weight: 600; color: #334155; background: #f1f5f9; padding: 2px 6px; border-radius: 6px;"><?= htmlspecialchars($row['nim']) ?></code>
                                </td>
                                <td>
                                    <span style="font-weight: 500; color: var(--text-dark);"><?= htmlspecialchars($row['prodi']) ?></span>
                                </td>
                                <td>
                                    <span style="color: var(--text-muted); font-size: 13px; font-weight: 400;"><?= htmlspecialchars($row['email']) ?></span>
                                </td>
                                <td>
                                    <span style="color: var(--text-dark); font-weight: 500;"><?= htmlspecialchars($row['username']) ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-info-premium">
                                        <?= $row['total_registrasi'] ?> Event Diikuti
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_hal > 1): // Hanya tampil jika halaman lebih dari 1 ?>
                <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
                    <div style="display: flex; gap: 6px;">
                        <?php for($i = 1; $i <= $total_hal; $i++): ?>
                            <a href="?hal=<?= $i ?>&cari=<?= urlencode($cari) ?>&prodi=<?= urlencode($filter_prodi) ?>" 
                               class="btn-cari-premium" 
                               style="height: 34px; width: 34px; padding: 0; font-size: 13px; text-decoration: none; box-shadow: none;
                                      display: flex; align-items: center; justify-content: center;
                                      /* Logika gaya: Warna biru jika ini halaman aktif, putih jika bukan */
                                      background: <?= $hal_ini === $i ? 'linear-gradient(135deg, var(--royal-blue) 0%, var(--deep-blue) 100%)' : '#ffffff' ?>; 
                                      color: <?= $hal_ini === $i ? '#ffffff' : 'var(--text-dark)' ?>;
                                      border: 1px solid <?= $hal_ini === $i ? 'var(--royal-blue)' : 'var(--border-color)' ?>;
                                      border-radius: 8px;">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
</div>

<script>
    // ============================================
    // JAVASCRIPT: LOGIKA TOGGLE SIDEBAR MOBILE
    // ============================================
    const btnMenu = document.getElementById('btnMenu'); // Mengambil elemen tombol menu
    const sidebar = document.getElementById('sidebar'); // Mengambil elemen panel sidebar
    const overlay = document.getElementById('sidebarOverlay'); // Mengambil elemen background gelap transparan

    // Pastikan ketiga elemen ditemukan sebelum menjalankan fungsi
    if(btnMenu && sidebar && overlay) {
        
        // Jika tombol menu di klik, geser sidebar masuk ke layar & tampilkan background gelap
        btnMenu.addEventListener('click', () => {
            sidebar.style.transform = 'translateX(0)';
            sidebar.style.left = '0';
            overlay.style.display = 'block';
        });

        // Jika area gelap diklik, sembunyikan kembali sidebar ke kiri layar & hilangkan background gelap
        overlay.addEventListener('click', () => {
            sidebar.style.transform = 'translateX(-100%)';
            overlay.style.display = 'none';
        });
    }
</script>

<?php include '../../includes/footer.php'; // Memuat tag penutup HTML dan script bawah ?>