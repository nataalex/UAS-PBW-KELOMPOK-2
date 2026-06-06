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

// Proses Daftar & Upload File Bukti Bayar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    $id_event    = (int)$_POST['id_event'];
    $jenis_tiket = bersihkan($_POST['jenis_tiket']);

    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_registrasi FROM registrasi WHERE id_mahasiswa=$id_mhs AND id_event=$id_event"));
    if ($cek) redirectDenganPesan('event.php', 'Kamu sudah mendaftar event ini sebelumnya!', 'error');

    $nama_file = NULL;
    if ($jenis_tiket === 'berbayar') {
        if (empty($_FILES['bukti_bayar']['name'])) redirectDenganPesan('event.php', 'Bukti pembayaran wajib diupload!', 'error');
        $ext = pathinfo($_FILES['bukti_bayar']['name'], PATHINFO_EXTENSION);
        $nama_file = time() . '_' . $id_mhs . '.' . $ext;
        move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], '../../assets/img/bukti/' . $nama_file);
    }

    $tgl = date('Y-m-d');
    mysqli_query($koneksi, "INSERT INTO registrasi (id_mahasiswa, id_event, tanggal_registrasi, status, bukti_pembayaran) 
                            VALUES ($id_mhs, $id_event, '$tgl', 'pending', " . ($nama_file ? "'$nama_file'" : "NULL") . ")");
    redirectDenganPesan('registrasi_saya.php', 'Berhasil mendaftar! Menunggu konfirmasi admin.', 'sukses');
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
<div class="app-wrapper">
    <?php include '../../includes/sidebar_mahasiswa.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-judul">Jelajahi Event Terbaru</div>
        </div>
        <div class="page-content">
            <?php tampilkanFlash(); ?>
            <div class="event-grid">
                <?php while ($row = mysqli_fetch_assoc($list_event)): ?>
                <div class="event-card">
                    <div class="event-card-gambar">
                        <span style="color:white; font-size:40px;">📅</span>
                        <div class="event-card-badge"><?= htmlspecialchars($row['nama_kategori']) ?></div>
                    </div>
                    <div class="event-card-body">
                        <span class="badge" style="background:#e0e7ff; color:#3730a3; margin-bottom:8px; font-size:11px;">Penyelenggara: <?= htmlspecialchars($row['penyelenggara'] ?? 'Pusat') ?></span>
                        <h3 class="event-card-judul"><?= htmlspecialchars($row['nama_event']) ?></h3>
                        <div class="event-card-meta">📅 <?= formatTanggal($row['tanggal_mulai']) ?> | ⏰ <?= formatWaktu($row['waktu_mulai']) ?></div>
                        <div class="event-card-meta">🏛️ <?= htmlspecialchars($row['nama_ruangan']) ?></div>
                        
                        <div style="font-weight: 800; color: var(--biru-utama); margin: 12px 0; font-size: 16px;">
                            <?= $row['jenis_tiket'] === 'berbayar' ? 'Rp ' . number_format($row['harga'], 0, ',', '.') : '<span class="badge badge-hijau">Gratis</span>' ?>
                        </div>

                        <?php if ($row['jumlah_daftar'] >= $row['kapasitas']): ?>
                            <button class="btn btn-abu btn-blok" disabled>Kuota Penuh</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-utama btn-blok" onclick="bukaModalDaftar(<?= $row['id_event'] ?>, '<?= addslashes($row['nama_event']) ?>', '<?= $row['jenis_tiket'] ?>', <?= $row['harga'] ?>)">Daftar Sekarang</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
</div>

<div id="modalDaftar" class="modal-overlay">
    <div class="modal-box">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h3 class="modal-judul">Konfirmasi Pendaftaran</h3>
                <button type="button" class="modal-tutup" onclick="tutupModal('modalDaftar')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_event" id="modal_id_event">
                <input type="hidden" name="jenis_tiket" id="modal_jenis_tiket">

                <div style="background: var(--biru-bg); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                    <h4 id="modal_nama_event" style="margin-bottom: 4px; color: var(--gelap);"></h4>
                    <p id="modal_harga" style="font-weight: bold; color: var(--biru-utama);"></p>
                </div>

                <div id="form_upload" style="display: none;" class="form-grup">
                    <label>Upload Bukti Pembayaran (Transfer/QRIS) *</label>
                    <input type="file" name="bukti_bayar" accept="image/png, image/jpeg, image/jpg" class="form-kontrol" id="input_bukti">
                    <p style="font-size: 11px; color: var(--abu-teks); margin-top: 4px;">Format: JPG/PNG.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-abu" onclick="tutupModal('modalDaftar')">Batal</button>
                <button type="submit" name="daftar" class="btn btn-utama">Kirim Pendaftaran</button>
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

    if (jenis === 'berbayar') {
        document.getElementById('modal_harga').innerText = 'Harga Tiket: Rp ' + new Intl.NumberFormat('id-ID').format(harga);
        divUpload.style.display = 'block';
        inputBukti.setAttribute('required', 'required');
    } else {
        document.getElementById('modal_harga').innerText = 'Harga Tiket: GRATIS';
        divUpload.style.display = 'none';
        inputBukti.removeAttribute('required');
    }

    bukaModal('modalDaftar');
}
</script>
<?php include '../../includes/footer.php'; ?>