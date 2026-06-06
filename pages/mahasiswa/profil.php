<?php
// pages/mahasiswa/profil.php — Edit Profil Mahasiswa
session_start();
require_once '../../config/koneksi.php';
require_once '../../includes/functions.php';
wajibMahasiswa();

$judul_halaman = 'Profil Saya';
$halaman_aktif = 'profil';
$id_user = (int)$_SESSION['id_user'];
$id_mhs  = (int)$_SESSION['id_mahasiswa'];
$errors  = [];
$sukses  = '';

// Ambil data profil saat ini
$profil = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT pm.*, u.username FROM profil_mahasiswa pm
     JOIN users u ON pm.id_user = u.id_user
     WHERE pm.id_mahasiswa=$id_mhs"
));

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_profil'])) {
    $nama  = bersihkan($_POST['nama_lengkap'] ?? '');
    $email = bersihkan($_POST['email'] ?? '');
    $prodi = bersihkan($_POST['prodi'] ?? '');

    if (empty($nama))  $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($email)) $errors[] = 'Email wajib diisi.';
    if (!filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';

    if (empty($errors)) {
        mysqli_query($koneksi,
            "UPDATE profil_mahasiswa SET nama_lengkap='$nama', email='$email', prodi='$prodi'
             WHERE id_mahasiswa=$id_mhs"
        );
        $_SESSION['nama_lengkap'] = $nama;
        $sukses = 'Profil berhasil diperbarui!';
        $profil['nama_lengkap'] = $nama;
        $profil['email'] = $email;
        $profil['prodi'] = $prodi;
    }
}

// Proses ganti password
$errors_pw = [];
$sukses_pw = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_password'])) {
    $pw_lama   = $_POST['password_lama'] ?? '';
    $pw_baru   = $_POST['password_baru'] ?? '';
    $pw_konfirm = $_POST['konfirmasi_password'] ?? '';

    // Ambil password hash dari DB
    $user = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT password_hash FROM users WHERE id_user=$id_user"));

    if (!password_verify($pw_lama, $user['password_hash'])) {
        $errors_pw[] = 'Password lama tidak sesuai.';
    }
    if (strlen($pw_baru) < 6) {
        $errors_pw[] = 'Password baru minimal 6 karakter.';
    }
    if ($pw_baru !== $pw_konfirm) {
        $errors_pw[] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors_pw)) {
        $hash_baru = password_hash($pw_baru, PASSWORD_DEFAULT);
        mysqli_query($koneksi, "UPDATE users SET password_hash='$hash_baru' WHERE id_user=$id_user");
        $sukses_pw = 'Password berhasil diperbarui!';
    }
}

$daftar_prodi = [
    'Teknik Informatika', 'Sistem Informasi', 'Teknik Elektro',
    'Manajemen', 'Akuntansi', 'Hukum', 'Psikologi',
    'Kedokteran', 'Farmasi', 'Ilmu Komunikasi',
    'Pendidikan Matematika', 'Pendidikan Bahasa Indonesia'
];
?>
<?php include '../../includes/header.php'; ?>
<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90"></div>
    <?php include '../../includes/sidebar_mahasiswa.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-kiri">
                <button class="btn-menu" id="btnMenu">☰</button>
                <div><div class="topbar-judul">Profil Saya</div>
                <div class="topbar-sub">Kelola informasi akun kamu</div></div>
            </div>
        </div>

        <div class="page-content">
            <!-- Header Profil -->
            <div class="card" style="margin-bottom:20px">
                <div style="padding:24px;display:flex;align-items:center;gap:20px">
                    <div style="width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,#1e3a8a,#3b82f6);color:white;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;flex-shrink:0">
                        <?= strtoupper(substr($profil['nama_lengkap'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-size:20px;font-weight:800"><?= htmlspecialchars($profil['nama_lengkap']) ?></div>
                        <div style="color:#64748b;font-size:13px;margin-top:2px">
                            <?= htmlspecialchars($profil['nim']) ?> &nbsp;|&nbsp; <?= htmlspecialchars($profil['prodi']) ?>
                        </div>
                        <div style="color:#64748b;font-size:13px">@<?= htmlspecialchars($profil['username']) ?></div>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <!-- Form Edit Profil -->
                <div class="card">
                    <div class="card-header"><span class="card-judul">✏️ Edit Profil</span></div>
                    <div class="card-body">
                        <?php if ($sukses): ?>
                            <div class="alert alert-sukses"><?= $sukses ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error"><?= implode('<br>', $errors) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="aksi_profil" value="1">
                            <div class="form-grup">
                                <label>Username</label>
                                <input type="text" value="<?= htmlspecialchars($profil['username']) ?>" disabled
                                       style="background:#f8fafc;color:#64748b">
                                <small style="color:#94a3b8">Username tidak bisa diubah</small>
                            </div>
                            <div class="form-grup">
                                <label>NIM</label>
                                <input type="text" value="<?= htmlspecialchars($profil['nim']) ?>" disabled
                                       style="background:#f8fafc;color:#64748b">
                            </div>
                            <div class="form-grup">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="nama_lengkap" required
                                       value="<?= htmlspecialchars($profil['nama_lengkap']) ?>">
                            </div>
                            <div class="form-grup">
                                <label>Email *</label>
                                <input type="email" name="email" required
                                       value="<?= htmlspecialchars($profil['email']) ?>">
                            </div>
                            <div class="form-grup">
                                <label>Program Studi</label>
                                <select name="prodi">
                                    <?php foreach ($daftar_prodi as $p): ?>
                                    <option value="<?= $p ?>" <?= $profil['prodi'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-utama">💾 Simpan Perubahan</button>
                        </form>
                    </div>
                </div>

                <!-- Form Ganti Password -->
                <div class="card">
                    <div class="card-header"><span class="card-judul">🔑 Ganti Password</span></div>
                    <div class="card-body">
                        <?php if ($sukses_pw): ?>
                            <div class="alert alert-sukses"><?= $sukses_pw ?></div>
                        <?php endif; ?>
                        <?php if (!empty($errors_pw)): ?>
                            <div class="alert alert-error"><?= implode('<br>', $errors_pw) ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="aksi_password" value="1">
                            <div class="form-grup">
                                <label>Password Lama *</label>
                                <input type="password" name="password_lama" required placeholder="Masukkan password saat ini">
                            </div>
                            <div class="form-grup">
                                <label>Password Baru * <small style="color:#94a3b8">(min. 6 karakter)</small></label>
                                <input type="password" name="password_baru" required placeholder="Password baru">
                            </div>
                            <div class="form-grup">
                                <label>Konfirmasi Password Baru *</label>
                                <input type="password" name="konfirmasi_password" required placeholder="Ulangi password baru">
                            </div>
                            <button type="submit" class="btn btn-utama">🔒 Ganti Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include '../../includes/footer.php'; ?>
