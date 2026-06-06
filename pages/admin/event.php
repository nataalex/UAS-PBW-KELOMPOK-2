<?php
// ============================================
// pages/admin/event.php — Kelola Event (CRUD)
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
if ($cari)         $where .= " AND e.nama_event LIKE '%$cari%'";
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

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>
    <?php include '../../includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div>
                    <div class="topbar-judul">Kelola Event</div>
                    <div class="topbar-sub">Tambah, edit, dan hapus event kampus</div>
                </div>
            </div>
            <div class="topbar-kanan">
                <a href="?aksi=tambah" class="btn btn-utama">➕ Tambah Event</a>
            </div>
        </div>

        <div class="page-content">
            <?php tampilkanFlash(); ?>

            <?php if (!empty($errors ?? [])): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $e) echo "• $e<br>"; ?>
                </div>
            <?php endif; ?>

            <?php if ($aksi === 'tambah' || $aksi === 'edit'): ?>
            <div class="card" style="margin-bottom:20px">
                <div class="card-header">
                    <span class="card-judul"><?= $aksi === 'edit' ? '✏️ Edit Event' : '➕ Tambah Event Baru' ?></span>
                    <a href="event.php" class="btn btn-abu btn-sm">← Kembali</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($aksi === 'edit'): ?>
                            <input type="hidden" name="id_event" value="<?= $edit_data['id_event'] ?>">
                        <?php endif; ?>

                        <div class="form-grup">
                            <label>Nama Event <span style="color:red">*</span></label>
                            <input type="text" name="nama_event" required
                                   placeholder="Contoh: Seminar Nasional Teknologi 2025"
                                   value="<?= htmlspecialchars($edit_data['nama_event'] ?? $_POST['nama_event'] ?? '') ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-grup">
                                <label>Kategori <span style="color:red">*</span></label>
                                <select name="id_kategori" required>
                                    <option value="">-- Pilih Kategori --</option>
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
                                    <option value="">-- Pilih Ruangan --</option>
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
                            <textarea name="deskripsi" placeholder="Jelaskan tentang event ini..."><?= htmlspecialchars($edit_data['deskripsi'] ?? $_POST['deskripsi'] ?? '') ?></textarea>
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
                                <select name="jenis_tiket" class="form-kontrol" onchange="document.getElementById('grup_harga').style.display = (this.value === 'berbayar') ? 'block' : 'none'">
                                    <option value="gratis" <?= ($edit_data['jenis_tiket'] ?? '') == 'gratis' ? 'selected' : '' ?>>Gratis</option>
                                    <option value="berbayar" <?= ($edit_data['jenis_tiket'] ?? '') == 'berbayar' ? 'selected' : '' ?>>Berbayar</option>
                                </select>
                            </div>
                            <div class="form-grup" id="grup_harga" style="display: <?= ($edit_data['jenis_tiket'] ?? 'gratis') == 'berbayar' ? 'block' : 'none' ?>;">
                                <label>Harga Tiket (Rp)</label>
                                <input type="number" name="harga" class="form-kontrol" value="<?= $edit_data['harga'] ?? 0 ?>" placeholder="Contoh: 50000">
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

                        <div style="display:flex;gap:10px;margin-top:16px">
                            <button type="submit" class="btn btn-utama">
                                <?= $aksi === 'edit' ? '💾 Simpan Perubahan' : '✅ Tambah Event' ?>
                            </button>
                            <a href="event.php" class="btn btn-abu">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <span class="card-judul">📋 Daftar Event (<?= $total_rows ?>)</span>
                </div>

                <form method="GET" action="">
                    <div class="cari-filter">
                        <input type="text" class="input-cari" name="cari"
                               placeholder="Cari nama event..."
                               value="<?= htmlspecialchars($cari) ?>">
                        <select name="kategori" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit">
                            <option value="">Semua Kategori</option>
                            <?php
                            mysqli_data_seek($list_kategori, 0);
                            while ($k = mysqli_fetch_assoc($list_kategori)):
                                $sel = $filter_kat == $k['id_kategori'] ? 'selected' : '';
                            ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= $sel ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <select name="status" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit">
                            <option value="">Semua Status</option>
                            <option value="draft"     <?= $filter_status === 'draft'     ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $filter_status === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="selesai"   <?= $filter_status === 'selesai'   ? 'selected' : '' ?>>Selesai</option>
                        </select>
                        <button type="submit" class="btn btn-utama btn-sm">🔍 Cari</button>
                        <a href="event.php" class="btn btn-abu btn-sm">Reset</a>
                    </div>
                </form>

                <div class="tabel-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Event</th>
                                <th>Kategori & Tiket</th>
                                <th>Tanggal & Waktu</th>
                                <th>Ruangan & Kuota</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list_event) === 0): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:30px;color:#64748b">
                                    😕 Tidak ada event ditemukan.
                                </td>
                            </tr>
                            <?php else: $no = $offset + 1; while ($row = mysqli_fetch_assoc($list_event)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_event']) ?></strong><br>
                                    <?php if ($row['pembicara']): ?>
                                    <small style="color:#64748b">🎤 <?= htmlspecialchars($row['pembicara']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($row['nama_kategori']) ?></small><br>
                                    <small style="font-weight:bold;color:var(--biru-utama)">
                                        <?= $row['jenis_tiket'] === 'berbayar' ? 'Rp ' . number_format($row['harga'], 0, ',', '.') : 'Gratis' ?>
                                    </small>
                                </td>
                                <td>
                                    <small><?= formatTanggal($row['tanggal_mulai']) ?></small><br>
                                    <small style="color:#64748b"><?= formatWaktu($row['waktu_mulai']) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($row['nama_ruangan']) ?></small><br>
                                    <?php $sisa = $row['kapasitas'] - $row['jumlah_daftar']; ?>
                                    <small><?= $row['jumlah_daftar'] ?>/<?= $row['kapasitas'] ?> Terisi</small>
                                    <div class="kuota-bar" style="width:80px; height:4px; margin-top:2px;">
                                        <div class="kuota-isi" style="width:<?= min(100, round($row['jumlah_daftar']/$row['kapasitas']*100)) ?>%;background:<?= $sisa <= 0 ? '#dc2626' : '#3b82f6' ?>"></div>
                                    </div>
                                </td>
                                <td><?= badgeStatus($row['status']) ?></td>
                                <td>
                                    <?php 
                                    // Pengecekan Hak Akses Tombol Edit/Hapus
                                    if ($row['penyelenggara'] === $admin_org || $admin_org === 'Pusat'): 
                                    ?>
                                        <div style="display:flex;gap:4px">
                                            <a href="?aksi=edit&id=<?= $row['id_event'] ?>"
                                               class="btn btn-abu btn-sm" title="Edit">✏️</a>
                                            <a href="#" onclick="konfirmasiHapus('?aksi=hapus&id=<?= $row['id_event'] ?>', '<?= addslashes($row['nama_event']) ?>')"
                                               class="btn btn-merah btn-sm" title="Hapus">🗑️</a>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge" style="background:#e0e7ff; color:#3730a3; padding: 4px 8px; font-size: 11px;">Milik <?= htmlspecialchars($row['penyelenggara']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_hal > 1): ?>
                <div class="paginasi">
                    <span>Menampilkan <?= $offset + 1 ?>–<?= min($offset + $per_halaman, $total_rows) ?> dari <?= $total_rows ?> event</span>
                    <div class="paginasi-tombol">
                        <?php if ($hal_ini > 1): ?>
                            <a href="?hal=<?= $hal_ini-1 ?>&cari=<?= urlencode($cari) ?>&kategori=<?= $filter_kat ?>&status=<?= $filter_status ?>">← Prev</a>
                        <?php else: ?>
                            <span class="nonaktif">← Prev</span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_hal; $i++): ?>
                            <a href="?hal=<?= $i ?>&cari=<?= urlencode($cari) ?>&kategori=<?= $filter_kat ?>&status=<?= $filter_status ?>"
                               class="<?= $i === $hal_ini ? 'aktif' : '' ?>"><?= $i ?></a>
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

<?php include '../../includes/footer.php'; ?>