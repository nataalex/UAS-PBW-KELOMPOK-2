<?php
// ============================================
// pages/mahasiswa/registrasi_saya.php
// ============================================
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibMahasiswa();

$judul_halaman = 'Registrasi Saya';
$halaman_aktif = 'registrasi';
$id_mhs = (int)$_SESSION['id_mahasiswa'];

if (isset($_GET['aksi']) && $_GET['aksi'] === 'batal') {
    $id = (int)$_GET['id'];
    $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_registrasi FROM registrasi WHERE id_registrasi=$id AND id_mahasiswa=$id_mhs"));
    if ($cek) {
        mysqli_query($koneksi, "UPDATE registrasi SET status='cancelled' WHERE id_registrasi=$id");
        redirectDenganPesan('registrasi_saya.php', 'Registrasi berhasil dibatalkan.');
    }
}

$where = "WHERE r.id_mahasiswa=$id_mhs";
$list_reg = mysqli_query($koneksi,
    "SELECT r.*, e.nama_event, e.tanggal_mulai, e.waktu_mulai, e.kapasitas, e.jenis_tiket, e.harga, r.bukti_pembayaran,
            k.nama_kategori, ru.nama_ruangan, e.penyelenggara
     FROM registrasi r
     JOIN event e ON r.id_event = e.id_event
     JOIN kategori_event k ON e.id_kategori = k.id_kategori
     JOIN ruangan ru ON e.id_ruangan = ru.id_ruangan
     $where ORDER BY r.tanggal_registrasi DESC, r.id_registrasi DESC"
);
?>
<?php include '../../includes/header.php'; ?>
<div class="app-wrapper">
    <?php include '../../includes/sidebar_mahasiswa.php'; ?>
    <main class="main-content">
        <div class="topbar"><div class="topbar-judul">History Registrasi</div></div>
        <div class="page-content">
            <?php tampilkanFlash(); ?>
            <div class="card card-body">
                <?php if (mysqli_num_rows($list_reg) === 0): ?>
                    <div style="text-align:center; padding:40px; color:#64748b;">
                        Belum ada event yang didaftar. <br><br>
                        <a href="event.php" class="btn btn-utama">Cari Event Sekarang</a>
                    </div>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <?php while ($row = mysqli_fetch_assoc($list_reg)): ?>
                    <div style="border:1px solid var(--abu-border); border-radius:10px; padding:16px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
                        <div>
                            <span class="badge" style="background:#e0e7ff; color:#3730a3; margin-bottom:8px; font-size:11px;">Penyelenggara: <?= htmlspecialchars($row['penyelenggara'] ?? 'Pusat') ?></span>
                            <h3 style="font-size:16px; margin:0 0 8px 0;"><?= htmlspecialchars($row['nama_event']) ?></h3>
                            
                            <div style="color:var(--biru-utama); font-weight:bold; font-size:14px; margin-bottom: 8px;">
                                <?= $row['jenis_tiket'] === 'berbayar' ? 'Berbayar (Rp ' . number_format($row['harga'], 0, ',', '.') . ')' : 'Gratis' ?>
                            </div>
                            <?php if ($row['bukti_pembayaran']): ?>
                                <div style="margin-bottom: 8px;">
                                    <a href="../../assets/img/bukti/<?= $row['bukti_pembayaran'] ?>" target="_blank" style="font-size:12px; color:#10b981; text-decoration:underline;">📄 Lihat Bukti Pembayaran Anda</a>
                                </div>
                            <?php endif; ?>

                            <div style="display:flex; gap:16px; font-size:13px; color:#64748b;">
                                <span>📅 <?= formatTanggal($row['tanggal_mulai']) ?> <?= formatWaktu($row['waktu_mulai']) ?></span>
                                <span>🏛️ <?= htmlspecialchars($row['nama_ruangan']) ?></span>
                            </div>
                        </div>
                        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                            <?= badgeStatus($row['status']) ?>
                            <?php if ($row['status'] === 'pending'): ?>
                            <a href="#" onclick="konfirmasiHapus('?aksi=batal&id=<?= $row['id_registrasi'] ?>','pendaftaran ini')" class="btn btn-abu btn-sm">Batalkan</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>