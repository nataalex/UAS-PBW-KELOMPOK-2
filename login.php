<?php
// ============================================
// login.php — Halaman Login Versi Selera AI (Official Royal Blue UNSIKA Edition)
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
    <title>Sign In - Campus Events</title>
    <!-- Google Fonts Premium: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.2px;
        }

        body, html {
            height: 100%;
            overflow: hidden;
            background-color: #1e40af;
        }

        /* CONTAINER UTAMA: WARNA BIRU SESUAI REFERENSI IMAGE_26EBC7.PNG */
        .login-wrapper {
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 50%, #2563eb 100%);
            display: grid;
            grid-template-columns: 50% 50%;
            position: relative;
        }

        /* BACKGROUND GLOW HALUS AGAR TETAP MODERN */
        .aurora-1 {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255,255,255,0) 70%);
            top: -10%;
            left: -5%;
            z-index: 1;
            filter: blur(40px);
        }
        .aurora-2 {
            position: absolute;
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.3) 0%, rgba(255,255,255,0) 65%);
            bottom: -15%;
            right: -5%;
            z-index: 1;
            filter: blur(40px);
        }

        /* --- SISI KIRI: MINIMALIST BRANDING --- */
        .welcome-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 15%;
            z-index: 2;
        }

        /* Logo Kampus Putih Bersih */
        .app-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            color: #ffffff;
        }
        .app-logo svg {
            width: 38px;
            height: 38px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .welcome-section h1 {
            font-size: 54px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.15;
            margin-bottom: 16px;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .welcome-section h1 span {
            background: linear-gradient(to right, #ffffff, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-section p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 420px;
            line-height: 1.6;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* --- SISI KANAN: FORM VIEW --- */
        .form-section {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-right: 15%;
            z-index: 2;
        }

        /* CARD PUTIH CERAH TRANSPARAN TIPIS (SANGAT KONTRAS DENGAN BIRU UTAMA) */
        .minimal-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 45px 35px;
            box-shadow: 0 30px 60px -15px rgba(15, 23, 42, 0.3);
        }

        .form-grup {
            margin-bottom: 20px;
        }

        .form-grup label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1e3a8a; /* Warna Teks Label Menyesuaikan Tema Royal Blue */
            margin-bottom: 8px;
        }

        /* INPUT FIELD */
        .form-grup input {
            width: 100%;
            padding: 13px 16px;
            font-size: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #ffffff;
            color: #0f172a;
            transition: all 0.25s ease;
        }

        .form-grup input::placeholder {
            color: #94a3b8;
        }

        .form-grup input:focus {
            outline: none;
            border-color: #1d4ed8;
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.15);
        }

        /* CHECKBOX REMEMBER ME */
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 26px;
            color: #1e3a8a;
            font-size: 13px;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        .remember-me input {
            accent-color: #1d4ed8;
            cursor: pointer;
        }

        /* BANNER NOTIFIKASI */
        .alert {
            padding: 12px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }
        .alert-sukses { background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.2); color: #15803d; }
        .alert-error { background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.2); color: #b91c1c; }

        /* TOMBOL UTAMA DENGAN WARNA MATCHING ROYAL BLUE */
        .btn-action-premium {
            width: 100%;
            padding: 13px;
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(29, 78, 216, 0.3);
        }

        .btn-action-premium:hover {
            background: linear-gradient(135deg, #1e40af 0%, #172554 100%);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.4);
            transform: translateY(-1px);
        }

        /* FOOTER REGISTER LINK TEXT */
        .card-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 13px;
            color: #475569;
            font-weight: 500;
        }

        .card-footer a {
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .card-footer a:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        /* LAYOUT RESPONSIVE */
        @media (max-width: 992px) {
            .login-wrapper { grid-template-columns: 1fr; }
            .welcome-section { display: none; }
            .form-section { padding: 20px; }
            .minimal-card { padding: 35px 24px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- Efek Pendaran Cahaya SaaS Modern -->
    <div class="aurora-1"></div>
    <div class="aurora-2"></div>
    
    <!-- Sisi Kiri: Teks Pembuka Berkelas -->
    <div class="welcome-section">
        <div class="app-logo">
            <svg viewBox="0 0 24 24">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/>
            </svg>
        </div>
        <h1>SIGN UP!!!<br>FIND VARIOUS <span>EXCITEMENT.</span></h1>
        <p>Selamat datang di web event universitas singaperbangsa karawang</p>
    </div>

    <!-- Sisi Kanan: Form Card Minimalis -->
    <div class="form-section">
        <div class="minimal-card">
            
            <!-- Integrasi Alert PHP Lu -->
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
                    <input type="text" id="username" name="username" placeholder="Username" required autocomplete="off">
                </div>

                <div class="form-grup">
                    <label for="password">Password</label>
                    <div style="position:relative">
                        <input type="password" id="password" name="password" placeholder="Password" required style="padding-right:50px">
                        <button type="button" class="toggle-password" data-target="#password" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:15px;color:#94a3b8;">👁️</button>
                    </div>
                </div>

                <label class="remember-me">
                    <input type="checkbox" name="remember"> Jangan Lupain Aku
                </label>

                <button type="submit" class="btn-action-premium">Masuk</button>
            </form>

            <div class="card-footer">
                Pasti belum punya akun? <a href="register.php">Daftar di sini</a>
            </div>
        </div>
    </div>

</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>