<?php
// ============================================
// pages/mahasiswa/event.php — Jelajahi Event
// ============================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibMahasiswa();

$judul_halaman = 'Jelajahi Event';
$halaman_aktif = 'event';
$id_mhs = (int)$_SESSION['id_mahasiswa'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    $id_event    = (int)$_POST['id_event'];
    $jenis_tiket = bersihkan($_POST['jenis_tiket']);

    // Cek apakah mahasiswa sudah pernah daftar di event ini
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_registrasi FROM registrasi WHERE id_mahasiswa=$id_mhs AND id_event=$id_event"));
    if ($cek) redirectDenganPesan('event.php', 'Kamu sudah mendaftar event ini sebelumnya!', 'error');

    $nama_file_baru = NULL;

    // Logika Khusus Event Berbayar (Wajib Upload & Validasi)
    if ($jenis_tiket === 'berbayar') {
        if (empty($_FILES['bukti_bayar']['name']) || $_FILES['bukti_bayar']['error'] !== 0) {
            redirectDenganPesan('event.php', 'Bukti pembayaran wajib diupload!', 'error');
        }

        $nama_file_asli = $_FILES['bukti_bayar']['name'];
        $tmp_name       = $_FILES['bukti_bayar']['tmp_name'];
        $ukuran         = $_FILES['bukti_bayar']['size'];
        
        // 1. Validasi Ekstensi (Hanya boleh JPG, JPEG, PNG)
        $ekstensi_valid = ['jpg', 'jpeg', 'png'];
        $ekstensi_file  = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));

        if (!in_array($ekstensi_file, $ekstensi_valid)) {
            redirectDenganPesan('event.php', 'Format file tidak didukung! Gunakan JPG, JPEG, atau PNG.', 'error');
        }

        // 2. Validasi Ukuran File (Maksimal 2MB)
        if ($ukuran > 1097152) { // 2MB dalam bytes
            redirectDenganPesan('event.php', 'Ukuran file terlalu besar! Maksimal 10MB.', 'error');
        }

        // 3. Cek apakah folder 'bukti' sudah ada. Jika belum, PHP akan otomatis membuatnya!
        $direktori_tujuan = '../../assets/img/bukti/';
        if (!is_dir($direktori_tujuan)) {
            mkdir($direktori_tujuan, 0777, true);
        }

        // 4. Generate nama unik dan pindahkan file ke folder
        $nama_file_baru = time() . '_' . rand(10, 99) . '_' . $id_mhs . '.' . $ekstensi_file;
        $folder_tujuan  = $direktori_tujuan . $nama_file_baru;

        if (!move_uploaded_file($tmp_name, $folder_tujuan)) {
            redirectDenganPesan('event.php', 'Gagal memindahkan file ke server. Cek Hak Akses (Permissions) folder XAMPP kamu!', 'error');
            exit; // Penting: Hentikan script agar data yang gagal upload tidak masuk ke database!
        }
    }

    $tgl = date('Y-m-d');
    
    // Simpan ke Database
    $query = "INSERT INTO registrasi (id_mahasiswa, id_event, tanggal_registrasi, status, bukti_pembayaran) 
              VALUES ($id_mhs, $id_event, '$tgl', 'pending', " . ($nama_file_baru ? "'$nama_file_baru'" : "NULL") . ")";
              
    if (mysqli_query($koneksi, $query)) {
        redirectDenganPesan('registrasi_saya.php', 'Berhasil mendaftar! Menunggu konfirmasi admin.', 'sukses');
    } else {
        redirectDenganPesan('event.php', 'Gagal menyimpan ke database.', 'error');
    }
}

$cari = bersihkan($_GET['cari'] ?? '');
$filter_kat = (int)($_GET['kategori'] ?? 0);
$per_halaman = 9;
$hal_ini = max(1, (int)($_GET['hal'] ?? 1));
$offset = ($hal_ini - 1) * $per_halaman;

$where = "WHERE e.status = 'published'";
if ($cari) $where .= " AND e.nama_event LIKE '%$cari%'";
if ($filter_kat) $where .= " AND e.id_kategori=$filter_kat";

$total_rows = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM event e $where"))['total'];
$total_hal = ceil($total_rows / $per_halaman);

$list_event = mysqli_query($koneksi,
    "SELECT e.*, k.nama_kategori, r.nama_ruangan,
            (SELECT COUNT(*) FROM registrasi WHERE id_event = e.id_event AND status != 'cancelled') as jumlah_daftar
     FROM event e
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     JOIN ruangan r ON e.id_ruangan = r.id_ruangan
     $where ORDER BY e.tanggal_mulai ASC LIMIT $per_halaman OFFSET $offset"
);
$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori_event");
?>
<?php include '../../includes/header.php'; ?>

<style>
:root {
    --primary: #2563EB;
    --primary-dark: #1D4ED8;
    --primary-light: #60A5FA;
    --secondary: #38BDF8;
    --success: #10B981;
    --danger: #EF4444;
    --dark: #0F172A;
    --gray: #64748B;
    --light-gray: #E2E8F0;
    --bg: #F8FAFC;
    --white: #FFFFFF;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
    --radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

* { box-sizing: border-box; }

.app-wrapper { background: var(--bg); min-height: 100vh; display: flex; }

.main-content {
    margin-left: 260px;
    width: calc(100% - 260px);
    padding: 40px;
    background: var(--bg);
    min-height: 100vh;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 30px;
    margin-bottom: 40px;
    border-bottom: 2px solid rgba(15, 23, 42, 0.05);
}

.topbar-title { font-size: 28px; font-weight: 800; color: var(--dark); letter-spacing: -0.5px; display: flex; align-items: center; gap: 12px; }
.topbar-title svg { color: var(--primary); }

.filter-bar { display: flex; gap: 14px; margin-bottom: 32px; flex-wrap: wrap; }
.filter-bar input, .filter-bar select {
    padding: 12px 18px; border: 1px solid var(--light-gray); border-radius: 12px; font-size: 14px; background: var(--white); transition: var(--transition); font-family: 'Inter', sans-serif; color: var(--dark);
}
.filter-bar input:focus, .filter-bar select:focus { outline: none; border-color: var(--primary-light); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
.filter-bar input { flex: 1; min-width: 180px; }
.filter-bar select { min-width: 160px; }
.filter-bar button {
    padding: 12px 28px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: var(--white); border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: var(--transition); font-family: 'Inter', sans-serif;
}
.filter-bar button:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37, 99, 235, 0.25); }

.event-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 1200px; }
.event-card {
    background: var(--white); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); transition: var(--transition); border: 1px solid rgba(15, 23, 42, 0.03); width: 100%; display: flex; flex-direction: column;
}
.event-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-hover); }

.event-card-image {
    height: 160px; background-color: var(--dark);
    background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.4) 0%, transparent 60%), radial-gradient(circle at 90% 80%, rgba(56, 189, 248, 0.3) 0%, transparent 60%);
    display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid rgba(255,255,255,0.05);
}
.event-card-image svg { width: 54px; height: 54px; color: rgba(255, 255, 255, 0.15); }
.event-card-badge {
    position: absolute; top: 14px; right: 14px; background: rgba(255, 255, 255, 0.95); padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; color: var(--dark); box-shadow: 0 4px 6px rgba(0,0,0,0.1); letter-spacing: 0.3px; text-transform: uppercase;
}

.event-card-body { padding: 22px; display: flex; flex-direction: column; flex: 1; }
.event-card-organizer { display: inline-flex; background: #DBEAFE; color: var(--primary-dark); padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 12px; align-self: flex-start; }
.event-card-title { font-size: 16px; font-weight: 800; color: var(--dark); margin: 0 0 10px 0; line-height: 1.4; }
.event-card-meta { font-size: 13px; color: var(--gray); margin-bottom: 6px; display: flex; align-items: center; gap: 8px; font-weight: 500; }
.event-card-price { font-size: 18px; font-weight: 800; color: var(--primary); margin: 16px 0 20px 0; margin-top: auto; }
.event-card-price .free { color: var(--success); font-weight: 700; }

.btn-block { width: 100%; padding: 12px; border: none; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; transition: var(--transition); font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: var(--white); }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3); }
.btn-gray { background: var(--light-gray); color: var(--gray); cursor: not-allowed; }

.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; }
.modal-overlay.active { display: flex; }
.modal-box { background: var(--white); border-radius: var(--radius); max-width: 480px; width: 90%; box-shadow: 0 25px 50px rgba(0,0,0,0.2); animation: modalSlide 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
@keyframes modalSlide { from { transform: translateY(30px) scale(0.95); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }

.modal-header { padding: 20px 24px; border-bottom: 1px solid var(--light-gray); display: flex; justify-content: space-between; align-items: center; }
.modal-title { font-size: 18px; font-weight: 700; color: var(--dark); margin: 0; }
.modal-close { background: var(--light-gray); border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--gray); transition: var(--transition); }
.modal-close:hover { background: var(--danger); color: var(--white); transform: rotate(90deg); }

.modal-body { padding: 24px; }
.modal-body .info-box { background: #F8FAFC; padding: 16px; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--light-gray); }
.modal-body .info-box h4 { margin: 0 0 6px 0; color: var(--dark); font-size: 16px; line-height: 1.4; }
.modal-body .info-box p { margin: 0; color: var(--primary); font-weight: 800; font-size: 16px; }

.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-weight: 600; font-size: 14px; color: var(--dark); margin-bottom: 8px; }
.form-group input[type="file"] { width: 100%; padding: 10px; border: 2px dashed #CBD5E1; border-radius: 12px; background: var(--bg); transition: var(--transition); font-family: 'Inter', sans-serif; color: var(--gray); }
.form-group input[type="file"]:hover { border-color: var(--primary-light); background: #F0F9FF; }

.modal-footer { padding: 16px 24px 24px 24px; display: flex; gap: 12px; justify-content: flex-end; }
.modal-footer .btn { padding: 12px 24px; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: var(--transition); font-family: 'Inter', sans-serif; font-size: 14px; }
.modal-footer .btn-cancel { background: var(--light-gray); color: var(--gray); }
.modal-footer .btn-cancel:hover { background: #CBD5E1; color: var(--dark); }
.modal-footer .btn-submit { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: var(--white); }
.modal-footer .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3); }

.empty-state { text-align: center; padding: 80px 20px; color: var(--gray); background: var(--white); border-radius: var(--radius); border: 1px dashed #CBD5E1; margin-top: 20px; }
.empty-state svg { color: #CBD5E1; margin-bottom: 16px; }
.empty-state h3 { color: var(--dark); margin-bottom: 8px; font-size: 18px; }

@media (max-width: 1200px) { .event-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 1024px) { .main-content { margin-left: 0; width: 100%; padding: 30px 20px 20px 20px; } }
@media (max-width: 768px) { .event-grid { grid-template-columns: 1fr; } .filter-bar { flex-direction: column; } .filter-bar input, .filter-bar select, .filter-bar button { width: 100%; } }
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:90; backdrop-filter:blur(4px);"></div>

    <?php include '../../includes/sidebar_mahasiswa.php'; ?>
    
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                Jelajahi Event
            </div>
        </div>
        <div class="page-content">
            <?php tampilkanFlash(); ?>

            <form method="GET" class="filter-bar">
                <input type="text" name="cari" placeholder="Cari nama event..." value="<?= htmlspecialchars($cari) ?>">
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= $filter_kat == $kat['id_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Filter Pencarian</button>
            </form>

            <div class="event-grid">
                <?php while ($row = mysqli_fetch_assoc($list_event)): ?>
                <div class="event-card">
                    <div class="event-card-image">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <span class="event-card-badge"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                    </div>
                    <div class="event-card-body">
                        <span class="event-card-organizer"><?= htmlspecialchars($row['penyelenggara'] ?? 'Pusat') ?></span>
                        <h3 class="event-card-title"><?= htmlspecialchars($row['nama_event']) ?></h3>
                        
                        <div class="event-card-meta">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <?= formatTanggal($row['tanggal_mulai']) ?> • <?= formatWaktu($row['waktu_mulai']) ?>
                        </div>
                        <div class="event-card-meta">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($row['nama_ruangan']) ?>
                        </div>
                        
                        <div class="event-card-price">
                            <?= $row['jenis_tiket'] === 'berbayar' ? 'Rp ' . number_format($row['harga'], 0, ',', '.') : '<span class="free">Gratis</span>' ?>
                        </div>

                        <?php if ($row['jumlah_daftar'] >= $row['kapasitas']): ?>
                            <button class="btn-block btn-gray" disabled>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                Kuota Penuh
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn-block btn-primary" onclick="bukaModalDaftar(<?= $row['id_event'] ?>, '<?= addslashes($row['nama_event']) ?>', '<?= $row['jenis_tiket'] ?>', <?= $row['harga'] ?>)">
                                Daftar Sekarang
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <?php if (mysqli_num_rows($list_event) === 0): ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/></svg>
                    <h3>Tidak ada event ditemukan</h3>
                    <p>Coba ubah kata kunci atau filter yang Anda gunakan</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div id="modalDaftar" class="modal-overlay">
    <div class="modal-box">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h3 class="modal-title">Konfirmasi Pendaftaran</h3>
                <button type="button" class="modal-close" onclick="tutupModal('modalDaftar')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_event" id="modal_id_event">
                <input type="hidden" name="jenis_tiket" id="modal_jenis_tiket">

                <div class="info-box">
                    <h4 id="modal_nama_event"></h4>
                    <p id="modal_harga"></p>
                </div>

                <div id="kotak_pembayaran" style="display: none; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
                    <h5 style="margin: 0 0 8px 0; color: var(--dark); font-size: 14px; font-weight: 700;">Metode Pembayaran (DANA)</h5>
                    <div style="font-size: 22px; font-weight: 900; color: var(--primary); margin-bottom: 8px; letter-spacing: 1px;">0821 1222 7425</div>
                    <p style="margin: 0; font-size: 13px; color: var(--gray); line-height: 1.5;">
                        * Silakan transfer sesuai total tagihan ke nomor DANA di atas.<br>
                        * Setelah mendaftar, mohon <strong>tunggu konfirmasi</strong> dari admin.
                    </p>
                </div>

                <div id="form_upload" style="display: none;" class="form-group">
                    <label>Upload Bukti Pembayaran <span style="color:var(--danger)">*</span></label>
                    <input type="file" name="bukti_bayar" accept="image/png, image/jpeg, image/jpg" id="input_bukti">
                    <p style="font-size: 12px; color: var(--gray); margin-top: 6px;">Format yang didukung: JPG/PNG. Maksimal 2MB</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="tutupModal('modalDaftar')">Batal</button>
                <button type="submit" name="daftar" class="btn btn-submit">Kirim Pendaftaran</button>
            </div>
        </form>
    </div>
</div>

<script>
function bukaModalDaftar(id, nama, jenis, harga) {
    document.getElementById('modal_id_event').value = id;
    document.getElementById('modal_jenis_tiket').value = jenis;
    document.getElementById('modal_nama_event').innerText = nama;
    
    const divUpload = document.getElementById('form_upload');
    const inputBukti = document.getElementById('input_bukti');
    const kotakPembayaran = document.getElementById('kotak_pembayaran');

    if (jenis === 'berbayar') {
        document.getElementById('modal_harga').innerText = 'Total Pembayaran: Rp ' + new Intl.NumberFormat('id-ID').format(harga);
        
        divUpload.style.display = 'block';
        kotakPembayaran.style.display = 'block';
        inputBukti.setAttribute('required', 'required');
    } else {
        document.getElementById('modal_harga').innerText = 'Event ini Gratis';
        
        divUpload.style.display = 'none';
        kotakPembayaran.style.display = 'none';
        inputBukti.removeAttribute('required');
    }

    document.getElementById('modalDaftar').classList.add('active');
}

function tutupModal(id) {
    document.getElementById(id).classList.remove('active');
}

const btnMenu = document.getElementById('btnMenu');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');

if(btnMenu && sidebar && overlay) {
    btnMenu.addEventListener('click', () => {
        sidebar.classList.add('open');
        overlay.style.display = 'block';
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
    });
}
</script>

<?php include '../../includes/footer.php'; ?>