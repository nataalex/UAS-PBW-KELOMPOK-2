<?php
// ============================================
// includes/functions.php
// Fungsi-fungsi pembantu yang digunakan
// di seluruh halaman aplikasi
// ============================================

// Pastikan file ini tidak diakses langsung lewat URL
if (!defined('DB_NAME')) {
    header('Location: ../index.php');
    exit;
}

// --- FUNGSI SESSION & AUTH ---

// Cek apakah user sudah login
function sudahLogin() {
    return isset($_SESSION['id_user']);
}

// Cek role user saat ini
function getRoleUser() {
    return $_SESSION['role'] ?? '';
}

// Paksa redirect jika belum login
function wajibLogin() {
    if (!sudahLogin()) {
        header('Location: ' . BASE_URL . '/index.php?pesan=harap_login');
        exit;
    }
}

// Paksa redirect jika bukan admin
function wajibAdmin() {
    wajibLogin();
    if (getRoleUser() !== 'admin') {
        header('Location: ' . BASE_URL . '/pages/mahasiswa/dashboard.php');
        exit;
    }
}

// Paksa redirect jika bukan mahasiswa
function wajibMahasiswa() {
    wajibLogin();
    if (getRoleUser() !== 'mahasiswa') {
        header('Location: ' . BASE_URL . '/pages/admin/dashboard.php');
        exit;
    }
}

// --- FUNGSI SANITASI & KEAMANAN ---

// Bersihkan input dari karakter berbahaya
function bersihkan($data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return mysqli_real_escape_string($koneksi, $data);
}

// --- FUNGSI FORMAT TAMPILAN ---

// Format tanggal dari Y-m-d menjadi "15 Juli 2025"
function formatTanggal($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
    ];
    $t = explode('-', $tanggal);
    return (int)$t[2] . ' ' . $bulan[(int)$t[1]] . ' ' . $t[0];
}

// Format waktu dari H:i:s menjadi "09:00 WIB"
function formatWaktu($waktu) {
    if (!$waktu) return '-';
    return substr($waktu, 0, 5) . ' WIB';
}

// Tampilkan badge status registrasi dengan warna
function badgeStatus($status) {
    $map = [
        'confirmed' => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Terkonfirmasi'],
        'pending'   => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Menunggu'],
        'cancelled' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Dibatalkan'],
        'published' => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Dipublikasi'],
        'draft'     => ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => 'Draft'],
        'selesai'   => ['bg' => '#ede9fe', 'color' => '#5b21b6', 'label' => 'Selesai'],
    ];
    $s = $map[$status] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'label' => $status];
    return "<span style='background:{$s['bg']};color:{$s['color']};padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600'>{$s['label']}</span>";
}

// Hitung sisa kuota event
function sisaKuota($id_event) {
    global $koneksi;
    $result = mysqli_query($koneksi,
        "SELECT e.kapasitas,
                COUNT(r.id_registrasi) as terdaftar
         FROM event e
         LEFT JOIN registrasi r ON e.id_event = r.id_event
             AND r.status != 'cancelled'
         WHERE e.id_event = " . (int)$id_event . "
         GROUP BY e.id_event"
    );
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['kapasitas'] - $row['terdaftar'];
    }
    return 0;
}

// Tampilkan pesan notifikasi (sukses/error)
function tampilkanPesan($pesan, $tipe = 'sukses') {
    $bg    = $tipe === 'sukses' ? '#d1fae5' : '#fee2e2';
    $color = $tipe === 'sukses' ? '#065f46' : '#991b1b';
    $icon  = $tipe === 'sukses' ? '✅' : '❌';
    echo "<div style='background:{$bg};color:{$color};padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px'>
            {$icon} {$pesan}
          </div>";
}

// Redirect dengan pesan (simpan di session)
function redirectDenganPesan($url, $pesan, $tipe = 'sukses') {
    $_SESSION['flash_pesan'] = $pesan;
    $_SESSION['flash_tipe']  = $tipe;
    header('Location: ' . $url);
    exit;
}

// Tampilkan & hapus pesan flash dari session
function tampilkanFlash() {
    if (isset($_SESSION['flash_pesan'])) {
        tampilkanPesan($_SESSION['flash_pesan'], $_SESSION['flash_tipe'] ?? 'sukses');
        unset($_SESSION['flash_pesan'], $_SESSION['flash_tipe']);
    }
}
?>
