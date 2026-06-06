-- ============================================
-- DATABASE: campus_events
-- Sistem Pendaftaran Event Mahasiswa
-- ============================================

CREATE DATABASE IF NOT EXISTS campus_events CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_events;

-- Tabel 1: USERS (akun login)
CREATE TABLE IF NOT EXISTS users (
    id_user     INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role        ENUM('admin', 'mahasiswa') NOT NULL DEFAULT 'mahasiswa',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel 2: PROFIL_ADMIN (Tabel Asli + Tambah Kolom Organisasi)
CREATE TABLE IF NOT EXISTS profil_admin (
    id_admin    INT AUTO_INCREMENT PRIMARY KEY,
    id_user     INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL,
    organisasi  ENUM('Pusat', 'BEM', 'BLM', 'HIMTIKA', 'HIMSIKA') DEFAULT 'Pusat', 
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- Tabel 3: PROFIL_MAHASISWA (Tabel Asli, tidak ada yang diubah)
CREATE TABLE IF NOT EXISTS profil_mahasiswa (
    id_mahasiswa INT AUTO_INCREMENT PRIMARY KEY,
    id_user      INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    nim          VARCHAR(20) NOT NULL UNIQUE,
    email        VARCHAR(100) NOT NULL,
    prodi        VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- Tabel 4: KATEGORI_EVENT (Tabel Asli, tidak ada yang diubah)
CREATE TABLE IF NOT EXISTS kategori_event (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL
);

-- Tabel 5: RUANGAN (Tabel Asli, tidak ada yang diubah)
CREATE TABLE IF NOT EXISTS ruangan (
    id_ruangan   INT AUTO_INCREMENT PRIMARY KEY,
    nama_ruangan VARCHAR(100) NOT NULL,
    kapasitas    INT NOT NULL
);

-- Tabel 6: EVENT (Tabel Asli + Tambah Tiket, Harga, Penyelenggara)
CREATE TABLE IF NOT EXISTS event (
    id_event    INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    id_ruangan  INT NOT NULL,
    nama_event  VARCHAR(150) NOT NULL,
    deskripsi   TEXT NOT NULL,
    tanggal_mulai DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    kapasitas   INT NOT NULL,
    pembicara   VARCHAR(100) DEFAULT NULL,
    status      ENUM('draft', 'published', 'completed') DEFAULT 'draft',
    
    -- Fitur Baru --
    jenis_tiket ENUM('gratis', 'berbayar') DEFAULT 'gratis',
    harga       INT DEFAULT 0,
    penyelenggara VARCHAR(50) DEFAULT 'Pusat',
    
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori_event(id_kategori),
    FOREIGN KEY (id_ruangan) REFERENCES ruangan(id_ruangan)
);

-- Tabel 7: REGISTRASI (Tabel Asli + Tambah Bukti Pembayaran)
CREATE TABLE IF NOT EXISTS registrasi (
    id_registrasi INT AUTO_INCREMENT PRIMARY KEY,
    id_mahasiswa  INT NOT NULL,
    id_event      INT NOT NULL,
    tanggal_registrasi DATE NOT NULL,
    status      ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    
    -- Fitur Baru --
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    
    FOREIGN KEY (id_mahasiswa) REFERENCES profil_mahasiswa(id_mahasiswa) ON DELETE CASCADE,
    FOREIGN KEY (id_event) REFERENCES event(id_event) ON DELETE CASCADE
);


-- ============================================
-- DATA DUMMY AWAL (Biar lu bisa langsung test)
-- ============================================

-- Password untuk semua akun di bawah ini adalah: password

-- 1. Insert Akun Users
INSERT INTO users (id_user, username, password_hash, role) VALUES 
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'adminbem', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(3, 'budi_s', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa');

-- 2. Insert Detail Admin
INSERT INTO profil_admin (id_user, nama_lengkap, email, organisasi) VALUES
(1, 'Admin Pusat', 'admin@kampus.ac.id', 'Pusat'),
(2, 'Ketua BEM', 'bem@kampus.ac.id', 'BEM');

-- 3. Insert Detail Mahasiswa
INSERT INTO profil_mahasiswa (id_user, nama_lengkap, nim, email, prodi) VALUES
(3, 'Budi Santoso', '12345678', 'budi@mhs.ac.id', 'Teknik Informatika');

-- 4. Insert Kategori & Ruangan
INSERT INTO kategori_event (nama_kategori) VALUES ('Seminar'), ('Workshop'), ('Kompetisi');
INSERT INTO ruangan (nama_ruangan, kapasitas) VALUES ('Aula Utama', 500), ('Ruang Lab Komputer', 40), ('Lapangan Basket', 1000);