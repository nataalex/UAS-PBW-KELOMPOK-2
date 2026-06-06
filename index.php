<?php
// ============================================
// index.php — Halaman Login
// ============================================
session_start();
require_once 'config/koneksi.php';
require_once 'includes/functions.php';

// Kalau sudah login, langsung redirect ke halaman yang sesuai
if (sudahLogin()) {
    if (getRoleUser() === 'admin') {
        header('Location: ' . BASE_URL . '/pages/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
    }
    exit;
}

$error = '';

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = bersihkan($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; 

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                $query_profil = mysqli_query($koneksi, "SELECT * FROM profil_admin WHERE id_user = " . $user['id_user']);
                $profil = mysqli_fetch_assoc($query_profil);

                $_SESSION['id_admin']     = $profil['id_admin'];
                $_SESSION['nama_lengkap'] = $profil['nama_lengkap'];
                // SIMPAN ORGANISASI ADMIN
                $_SESSION['organisasi']   = $profil['organisasi']; 

                header('Location: ' . BASE_URL . '/pages/admin/dashboard.php');
            } else {
                $query_profil = mysqli_query($koneksi, "SELECT * FROM profil_mahasiswa WHERE id_user = " . $user['id_user']);
                $profil = mysqli_fetch_assoc($query_profil);

                $_SESSION['id_mahasiswa'] = $profil['id_mahasiswa'];
                $_SESSION['nama_lengkap'] = $profil['nama_lengkap'];
                $_SESSION['nim']          = $profil['nim'];

                header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Campus Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="login-page">

<div class="login-box">
    <div class="login-logo">
        <span class="ikon">🎓</span>
        <h1>Campus Events</h1>
        <p>Portal pendaftaran event kampus</p>
    </div>

    <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-sukses">Anda berhasil logout.</div>
    <?php endif; ?>
    <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'harap_login'): ?>
        <div class="alert alert-error">Silakan login terlebih dahulu.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-grup">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username kamu" required>
        </div>

        <div class="form-grup">
            <label for="password">Password</label>
            <div style="position:relative">
                <input type="password" id="password" name="password" placeholder="Masukkan password kamu" required style="padding-right:44px">
                <button type="button" class="toggle-password" data-target="#password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px">👁️</button>
            </div>
        </div>

        <button type="submit" class="btn btn-utama btn-blok" style="margin-top:8px;padding:11px">Masuk</button>
    </form>

    <div class="login-footer">
        Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar di sini</a>
    </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>