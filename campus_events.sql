-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 16, 2026 at 06:21 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campus_events`
--

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id_event` int NOT NULL,
  `id_kategori` int NOT NULL,
  `id_ruangan` int NOT NULL,
  `nama_event` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `kapasitas` int NOT NULL,
  `pembicara` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `jenis_tiket` enum('gratis','berbayar') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'gratis',
  `harga` int DEFAULT '0',
  `penyelenggara` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Pusat',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id_event`, `id_kategori`, `id_ruangan`, `nama_event`, `deskripsi`, `tanggal_mulai`, `waktu_mulai`, `kapasitas`, `pembicara`, `status`, `jenis_tiket`, `harga`, `penyelenggara`, `created_at`) VALUES
(3, 2, 1, 'manusia peduli', 'rere', '2026-09-18', '08:00:00', 100, 'Haji Ahmad Dahlah', 'published', 'gratis', 0, 'BEM', '2026-06-06 00:47:23'),
(4, 3, 2, 'beban rasa', 'datang', '2027-04-14', '08:00:00', 100, 'Haji Ahmad Dahlah', 'published', 'berbayar', 100000, 'BLM', '2026-06-06 01:00:39'),
(5, 1, 3, 'sendiri', 'oww', '2022-02-22', '08:00:00', 100, 'Haji Ahmad Dahlah', 'published', 'gratis', 0, 'HIMTIKA', '2026-06-06 01:06:21'),
(6, 2, 2, 'gtau', 'rawrr', '2026-09-25', '08:00:00', 50, 'Haji Ahmad Dahlah', 'published', 'gratis', 0, 'HIMSIKA', '2026-06-06 01:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'amarasda'),
(2, 'asda'),
(3, 'asdsdw');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_event`
--

CREATE TABLE `kategori_event` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_event`
--

INSERT INTO `kategori_event` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Seminar'),
(2, 'Workshop'),
(3, 'kajian');

-- --------------------------------------------------------

--
-- Table structure for table `profil_admin`
--

CREATE TABLE `profil_admin` (
  `id_admin` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `organisasi` enum('Pusat','BEM','BLM','HIMTIKA','HIMSIKA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Pusat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profil_admin`
--

INSERT INTO `profil_admin` (`id_admin`, `id_user`, `nama_lengkap`, `email`, `organisasi`) VALUES
(1, 1, 'Admin Pusat', 'admin@kampus.ac.id', 'Pusat'),
(2, 2, 'Ketua BEM', 'bem@kampus.ac.id', 'BEM'),
(5, 3, 'Ketua BLM', 'blm@kampus.ac.id', 'BLM'),
(6, 4, 'Ketua HIMTIKA', 'himtika@kampus.ac.id', 'HIMTIKA'),
(7, 5, 'Ketua HIMSIKA', 'himsika@kampus.ac.id', 'HIMSIKA');

-- --------------------------------------------------------

--
-- Table structure for table `profil_mahasiswa`
--

CREATE TABLE `profil_mahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nim` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prodi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profil_mahasiswa`
--

INSERT INTO `profil_mahasiswa` (`id_mahasiswa`, `id_user`, `nama_lengkap`, `nim`, `email`, `prodi`) VALUES
(3, 6, 'Ibnu Izaas Natawijaya', '216523146123', '2410631170124@student.unsika.ac.id', 'Teknik Informatika'),
(4, 7, 'ahmad abiyu razan', '83833883', '83833883@student.unsika.ac.id', 'Teknik Informatika'),
(5, 8, 'Kakawaawaw', '24101112131415', 'kakaw@stundent.untad.ac.id', 'Pendidikan Bahasa Indonesia');

-- --------------------------------------------------------

--
-- Table structure for table `registrasi`
--

CREATE TABLE `registrasi` (
  `id_registrasi` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_event` int NOT NULL,
  `tanggal_registrasi` date NOT NULL,
  `status` enum('pending','confirmed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `bukti_pembayaran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id_ruangan` int NOT NULL,
  `nama_ruangan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id_ruangan`, `nama_ruangan`, `kapasitas`) VALUES
(1, 'Aula Utama', 500),
(2, 'Ruang Lab Komputer', 40),
(3, 'Lapangan Basket', 1000),
(5, 'mars planet', 135);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','mahasiswa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mahasiswa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-05 23:24:14'),
(2, 'adminbem', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-05 23:24:14'),
(3, 'adminblm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-05 23:24:14'),
(4, 'adminhimtika', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-05 23:24:14'),
(5, 'adminhimsika', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-05 23:24:14'),
(6, 'nata', '$2y$10$BhV7uGVnpJGUt1sKPozRLukP2N81esaXIknKJ/YLq9bSUxdTfed0m', 'mahasiswa', '2026-06-09 19:09:30'),
(7, 'razan', '$2y$10$JBLjttRk0kzdGCLQsNhVx.tftqew.BlZViqVS/416zT/g4w6oPbkO', 'mahasiswa', '2026-06-16 16:46:15'),
(8, 'KakawwWW', '$2y$10$K2VlBy6OM1wKbpXYHiTiieeZnjxZWDrxJ0UflBoI.m5xOUfDCsSXm', 'mahasiswa', '2026-06-16 18:16:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_ruangan` (`id_ruangan`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `kategori_event`
--
ALTER TABLE `kategori_event`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `profil_admin`
--
ALTER TABLE `profil_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `profil_mahasiswa`
--
ALTER TABLE `profil_mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `registrasi`
--
ALTER TABLE `registrasi`
  ADD PRIMARY KEY (`id_registrasi`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_event` (`id_event`);

--
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id_ruangan`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori_event`
--
ALTER TABLE `kategori_event`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `profil_admin`
--
ALTER TABLE `profil_admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `profil_mahasiswa`
--
ALTER TABLE `profil_mahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `registrasi`
--
ALTER TABLE `registrasi`
  MODIFY `id_registrasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id_ruangan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_event` (`id_kategori`),
  ADD CONSTRAINT `event_ibfk_2` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`);

--
-- Constraints for table `profil_admin`
--
ALTER TABLE `profil_admin`
  ADD CONSTRAINT `profil_admin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `profil_mahasiswa`
--
ALTER TABLE `profil_mahasiswa`
  ADD CONSTRAINT `profil_mahasiswa_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `registrasi`
--
ALTER TABLE `registrasi`
  ADD CONSTRAINT `registrasi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `profil_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrasi_ibfk_2` FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
