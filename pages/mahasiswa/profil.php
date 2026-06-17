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

$profil = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT pm.*, u.username FROM profil_mahasiswa pm
     JOIN users u ON pm.id_user = u.id_user
     WHERE pm.id_mahasiswa=$id_mhs"
));

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

$errors_pw = [];
$sukses_pw = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_password'])) {
    $pw_lama   = $_POST['password_lama'] ?? '';
    $pw_baru   = $_POST['password_baru'] ?? '';
    $pw_konfirm = $_POST['konfirmasi_password'] ?? '';

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

<style>
:root {
    --primary: #2563EB;
    --primary-dark: #1D4ED8;
    --primary-light: #60A5FA;
    --secondary: #38BDF8;
    --success: #10B981;
    --warning: #F59E0B;
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

* {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    box-sizing: border-box;
}

body {
    background: var(--bg);
    margin: 0;
    padding: 0;
}

.app-wrapper {
    background: var(--bg);
    min-height: 100vh;
    display: flex;
}

.main-content {
    margin-left: 260px; /* Disesuaikan lebar sidebar 260px */
    width: calc(100% - 260px);
    padding: 40px;
    background: var(--bg);
    min-height: 100vh;
    transition: var(--transition);
}

/* --- TOPBAR --- */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 24px;
    margin-bottom: 32px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.05);
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.btn-menu {
    display: none;
    background: var(--white);
    border: 1px solid var(--light-gray);
    border-radius: 10px;
    padding: 8px 10px;
    cursor: pointer;
    color: var(--dark);
    transition: var(--transition);
}

.btn-menu:hover {
    background: var(--primary);
    color: var(--white);
    border-color: var(--primary);
}

.topbar-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--dark);
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.topbar-title svg {
    color: var(--primary);
}

.topbar-title .highlight {
    color: var(--primary);
}

.topbar-sub {
    font-size: 14px;
    color: var(--gray);
    font-weight: 500;
    margin-top: 4px;
}

/* --- KARTU --- */
.card {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid rgba(15, 23, 42, 0.03);
    margin-bottom: 24px;
}

.card:hover {
    box-shadow: var(--shadow-hover);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(15, 23, 42, 0.04);
}

.card-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-title svg {
    color: var(--primary);
}

.card-body {
    padding: 24px;
}

/* --- PROFIL HEADER --- */
.profile-header {
    padding: 32px 24px;
    display: flex;
    align-items: center;
    gap: 24px;
    background: linear-gradient(120deg, #F8FAFC 0%, #EFF6FF 100%);
    border-bottom: 1px solid rgba(37, 99, 235, 0.1);
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 20px; /* Modern squircle look */
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 800;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}

.profile-info {
    flex: 1;
}

.profile-info h2 {
    font-size: 24px;
    font-weight: 800;
    color: var(--dark);
    margin: 0 0 6px 0;
}

.profile-info .detail {
    color: var(--gray);
    font-size: 14px;
    margin: 4px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.profile-info .detail strong {
    color: var(--dark);
    font-weight: 600;
}

.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

/* --- FORM --- */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    color: var(--dark);
    margin-bottom: 8px;
}

.form-group label .required {
    color: var(--danger);
    margin-left: 2px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--light-gray);
    border-radius: 12px;
    font-size: 14px;
    font-family: 'Inter', -apple-system, sans-serif;
    transition: var(--transition);
    background: var(--bg);
    color: var(--dark);
}

.form-group input:hover,
.form-group select:hover {
    background: #F0F9FF;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-light);
    background: var(--white);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.form-group input:disabled {
    background: var(--light-gray);
    color: var(--gray);
    cursor: not-allowed;
    opacity: 0.7;
}

.form-group input:disabled:hover {
    background: var(--light-gray);
}

.form-group .helper {
    font-size: 12px;
    color: var(--gray);
    margin-top: 6px;
    display: block;
}

.btn-primary {
    padding: 12px 28px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--white);
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition);
    font-family: 'Inter', -apple-system, sans-serif;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
}

/* --- ALERTS --- */
.alert {
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    line-height: 1.5;
}

.alert-success {
    background: #D1FAE5;
    color: #065F46;
    border: 1px solid #A7F3D0;
}

.alert-error {
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FECACA;
}

/* --- RESPONSIVE --- */
@media (max-width: 1024px) {
    .main-content { margin-left: 0; width: 100%; padding: 30px 20px 20px 20px; }
    .btn-menu { display: block; }
    .profile-grid { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .topbar { flex-direction: column; align-items: flex-start; gap: 16px; }
    .profile-header { flex-direction: column; text-align: center; gap: 16px; }
    .profile-avatar { width: 70px; height: 70px; font-size: 28px; border-radius: 16px; }
    .profile-info .detail { justify-content: center; }
}
</style>

<div class="app-wrapper">
    <div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);z-index:90;backdrop-filter:blur(4px);"></div>
    <?php include '../../includes/sidebar_mahasiswa.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="btn-menu" id="btnMenu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>
                <div>
                    <div class="topbar-title">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Profil <span class="highlight">Saya</span>
                    </div>
                    <div class="topbar-sub">Kelola informasi akun kamu</div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($profil['nama_lengkap'], 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($profil['nama_lengkap']) ?></h2>
                        <div class="detail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <strong>NIM:</strong> <?= htmlspecialchars($profil['nim']) ?>
                        </div>
                        <div class="detail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            <strong>Program Studi:</strong> <?= htmlspecialchars($profil['prodi']) ?>
                        </div>
                        <div class="detail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <strong>Username:</strong> @<?= htmlspecialchars($profil['username']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-grid">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            Edit Profil
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if ($sukses): ?>
                            <div class="alert alert-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <?= $sukses ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <div><?= implode('<br>', $errors) ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="aksi_profil" value="1">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" value="<?= htmlspecialchars($profil['username']) ?>" disabled>
                                <span class="helper">Username tidak bisa diubah</span>
                            </div>
                            <div class="form-group">
                                <label>NIM</label>
                                <input type="text" value="<?= htmlspecialchars($profil['nim']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="nama_lengkap" required value="<?= htmlspecialchars($profil['nama_lengkap']) ?>">
                            </div>
                            <div class="form-group">
                                <label>Email <span class="required">*</span></label>
                                <input type="email" name="email" required value="<?= htmlspecialchars($profil['email']) ?>">
                            </div>
                            <div class="form-group">
                                <label>Program Studi</label>
                                <select name="prodi">
                                    <?php foreach ($daftar_prodi as $p): ?>
                                    <option value="<?= $p ?>" <?= $profil['prodi'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Ganti Password
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if ($sukses_pw): ?>
                            <div class="alert alert-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <?= $sukses_pw ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errors_pw)): ?>
                            <div class="alert alert-error">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <div><?= implode('<br>', $errors_pw) ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="aksi_password" value="1">
                            <div class="form-group">
                                <label>Password Lama <span class="required">*</span></label>
                                <input type="password" name="password_lama" required placeholder="Masukkan password saat ini">
                            </div>
                            <div class="form-group">
                                <label>Password Baru <span class="required">*</span></label>
                                <input type="password" name="password_baru" required placeholder="Password baru (min. 6 karakter)">
                                <span class="helper">Minimal 6 karakter</span>
                            </div>
                            <div class="form-group">
                                <label>Konfirmasi Password Baru <span class="required">*</span></label>
                                <input type="password" name="konfirmasi_password" required placeholder="Ulangi password baru">
                            </div>
                            <button type="submit" class="btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                                Perbarui Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
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