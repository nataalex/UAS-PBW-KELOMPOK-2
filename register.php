<?php
// ============================================
// register.php — Halaman Registrasi Akun Baru
// ============================================
session_start();
require_once 'config/koneksi.php';
require_once 'includes/functions.php';

// Kalau sudah login, redirect
if (sudahLogin()) {
    header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
    exit;
}

$error   = [];
$sukses  = '';
$data    = []; // simpan input supaya tidak hilang saat error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & bersihkan input
    $data['username']     = bersihkan($_POST['username'] ?? '');
    $data['nama_lengkap'] = bersihkan($_POST['nama_lengkap'] ?? '');
    $data['nim']          = bersihkan($_POST['nim'] ?? '');
    $data['email']        = bersihkan($_POST['email'] ?? '');
    $data['prodi']        = bersihkan($_POST['prodi'] ?? '');
    $password             = $_POST['password'] ?? '';
    $konfirmasi           = $_POST['konfirmasi_password'] ?? '';

    // --- VALIDASI ---
    if (empty($data['username']))     $error[] = 'Username wajib diisi.';
    if (strlen($data['username']) < 4) $error[] = 'Username minimal 4 karakter.';
    if (empty($data['nama_lengkap'])) $error[] = 'Nama lengkap wajib diisi.';
    if (empty($data['nim']))          $error[] = 'NIM wajib diisi.';
    if (empty($data['email']))        $error[] = 'Email wajib diisi.';
    if (!filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) $error[] = 'Format email tidak valid.';
    if (empty($data['prodi']))        $error[] = 'Program studi wajib dipilih.';
    if (strlen($password) < 6)        $error[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirmasi)    $error[] = 'Konfirmasi password tidak cocok.';

    // Cek username sudah dipakai atau belum
    $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username = '{$data['username']}'");
    if (mysqli_num_rows($cek) > 0)    $error[] = 'Username sudah digunakan, pilih yang lain.';

    // Cek NIM sudah terdaftar
    $cekNim = mysqli_query($koneksi, "SELECT id_mahasiswa FROM profil_mahasiswa WHERE nim = '{$data['nim']}'");
    if (mysqli_num_rows($cekNim) > 0) $error[] = 'NIM sudah terdaftar dalam sistem.';

    // Kalau tidak ada error, simpan ke database
    if (empty($error)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Simpan ke tabel users
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'mahasiswa')"
        );
        mysqli_stmt_bind_param($stmt, 'ss', $data['username'], $hash);

        if (mysqli_stmt_execute($stmt)) {
            $id_user = mysqli_insert_id($koneksi);

            // Simpan ke tabel profil_mahasiswa
            $stmt2 = mysqli_prepare($koneksi,
                "INSERT INTO profil_mahasiswa (id_user, nama_lengkap, nim, email, prodi)
                 VALUES (?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt2, 'issss',
                $id_user,
                $data['nama_lengkap'],
                $data['nim'],
                $data['email'],
                $data['prodi']
            );
            mysqli_stmt_execute($stmt2);

            $sukses = 'Akun berhasil dibuat! Silakan login dengan username kamu.';
            $data   = []; // kosongkan form
        } else {
            $error[] = 'Gagal menyimpan data. Coba lagi.';
        }
    }
}

$daftar_prodi = [
    'Teknik Informatika', 'Sistem Informasi', 'Teknik Elektro',
    'Manajemen', 'Akuntansi', 'Hukum', 'Psikologi',
    'Kedokteran', 'Farmasi', 'Ilmu Komunikasi',
    'Pendidikan Matematika', 'Pendidikan Bahasa Indonesia'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | Campus Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box" style="max-width:480px">
        <div class="login-logo">
            <span class="ikon">📝</span>
            <h1>Daftar Akun</h1>
            <p>Buat akun baru untuk ikut event kampus</p>
        </div>

        <?php if ($sukses): ?>
            <div class="alert alert-sukses"><?= $sukses ?> <a href="<?= BASE_URL ?>/index.php" style="font-weight:700;color:inherit">Login sekarang →</a></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <ul style="margin:0;padding-left:16px">
                    <?php foreach ($error as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!$sukses): ?>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-grup">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="min. 4 karakter"
                           value="<?= htmlspecialchars($data['username'] ?? '') ?>" required>
                </div>
                <div class="form-grup">
                    <label>NIM</label>
                    <input type="text" name="nim" placeholder="Nomor Induk Mahasiswa"
                           value="<?= htmlspecialchars($data['nim'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-grup">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Nama sesuai KTM"
                       value="<?= htmlspecialchars($data['nama_lengkap'] ?? '') ?>" required>
            </div>

            <div class="form-grup">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@mahasiswa.ac.id"
                       value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
            </div>

            <div class="form-grup">
                <label>Program Studi</label>
                <select name="prodi" required>
                    <option value="">-- Pilih Program Studi --</option>
                    <?php foreach ($daftar_prodi as $p): ?>
                        <option value="<?= $p ?>" <?= ($data['prodi'] ?? '') === $p ? 'selected' : '' ?>>
                            <?= $p ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-grup">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="min. 6 karakter" required>
                </div>
                <div class="form-grup">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="konfirmasi_password" placeholder="Ulangi password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-utama btn-blok" style="margin-top:8px;padding:11px">
                Buat Akun
            </button>
        </form>
        <?php endif; ?>

        <div class="login-footer">
            Sudah punya akun? <a href="<?= BASE_URL ?>/index.php">Login di sini</a>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
