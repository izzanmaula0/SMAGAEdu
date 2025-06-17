-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 04:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smagaedu`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_history`
--

CREATE TABLE `ai_chat_history` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `pesan` text NOT NULL,
  `respons` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `character_traits` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`character_traits`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chat_history`
--

INSERT INTO `ai_chat_history` (`id`, `user_id`, `model`, `session_id`, `pesan`, `respons`, `created_at`, `character_traits`) VALUES
(750, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo saga, selamat pagi', 'Selamat pagi! Bagaimana hari Anda hari ini? Apakah ada sesuatu yang bisa saya bantu atau kita bisa ngobrol tentang Halo Saga?', '2025-03-17 03:16:17', NULL),
(751, 'fadhilmanfa', 'llama-3.1-8b-instant', NULL, 'halo', 'Halo! Kita bisa membicarakan tentang seri game Halo yang sangat populer itu. Apakah kamu sudah pernah bermain Halo atau ingin tahu lebih banyak tentang cerita dan gameplaynya?', '2025-03-17 03:17:15', NULL),
(752, 'fadhilmanfa', 'llama-3.1-8b-instant', NULL, 'halo selamat siang', 'Halo! Selamat siang juga! Kita masih ngobrol tentang Halo, ya? Apakah kamu sudah pernah mengenakan kostum Master Chief atau bermain Halo di console apa saja?', '2025-03-17 03:17:24', NULL),
(753, 'fadhilmanfa', 'llama-3.1-8b-instant', NULL, 'bukan itu, saya mau menyapamu', 'Halo! Selamat siang, ya? Saya juga menyapa kamu!', '2025-03-17 03:18:07', NULL),
(754, 'fadhilmanfa', 'llama3-8b-8192', NULL, 'kamu lagi apa?', 'Haha! Saya lagi ngobrol tentang Halo dan ngapain-ngapain! Apa kabar kamu?', '2025-03-17 03:18:23', NULL),
(755, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'selamat siang', 'Selamat siang! Ada yang bisa saya bantu?', '2025-03-17 03:18:45', NULL),
(756, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo', 'Halo! It seems you\'re a fan of the popular video game series. What\'s your favorite Halo game or memory?', '2025-03-17 03:19:43', NULL),
(757, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo', 'Halo! It seems like you\'re a fan of the popular video game series. Which part of the Halo universe are you most interested in? The games, the lore, or something else?', '2025-03-17 03:19:54', NULL),
(758, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'kamu siapa?', 'Saya adalah asisten virtual yang dirancang untuk membantu dan berkomunikasi dengan pengguna seperti Anda. Saya tidak memiliki identitas pribadi, tetapi saya di sini untuk membantu menjawab pertanyaan, menyediakan informasi, dan berbincang dengan Anda tentang berbagai topik. Bagaimana saya bisa membantu Anda hari ini?', '2025-03-17 03:20:20', NULL),
(759, 'fadhilmanfa', 'llama-3.1-8b-instant', NULL, 'halo saga', 'Halo! Senang sekali berjumpa denganmu. Bagaimana hari mu hari ini? Apakah kamu butuh bantuan atau ingin berdiskusi tentang sesuatu?', '2025-03-17 03:29:32', NULL),
(760, 'fadhilmanfa', 'llama-3.1-8b-instant', NULL, 'kamu siapa?', 'Saya SAGA, asisten AI khusus untuk SMA Negeri 1 Gresik. Saya disini untuk membantu kamu dengan pertanyaan atau masalah yang kamu hadapi, terutama tentang pelajaran sekolah. Apakah kamu memerlukan bantuan?', '2025-03-17 03:29:53', NULL),
(761, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo saga', 'Halo! Senang sekali bertemu denganmu! Saya SAGA, asisten Guru SMP Muhammadiyah 2 Gatak dan SMA Muhammadiyah 5 Gatak. Bagaimana hari mu hari ini? Apakah ada pertanyaan atau topik yang ingin kamu diskusikan? Saya siap membantu!', '2025-03-17 03:30:59', NULL),
(762, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo saga', 'Halo! Selamat datang! Saya SAGA, asisten Guru SMP Muhammadiyah 2 Gatak dan SMA Muhammadiyah 5 Gatak. Saya siap membantu Anda dengan pertanyaan atau topik yang ingin Anda diskusikan. Apa yang ingin Anda tanyakan atau bahas hari ini?', '2025-03-17 03:31:57', NULL),
(763, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo saga', 'Halo! Senang sekali bisa berinteraksi denganmu. Apakah ada pertanyaan atau topik yang ingin kamu diskusikan? Saya siap membantu dan memberikan analisis yang mendalam dan komprehensif. Mari kita mulai!', '2025-03-17 03:33:07', NULL),
(764, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo saga', 'Halo! Senang bisa berkomunikasi denganmu. Bagaimana aku bisa membantu atau apa yang ingin kamu diskusikan hari ini?', '2025-03-17 03:33:29', NULL),
(765, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo saga', 'Halo! Selamat datang! Saya SAGA, asisten Guru SMP Muhammadiyah 2 Gatak dan SMA Muhammadiyah 5 Gatak. Saya siap membantu Anda dengan pertanyaan atau topik yang ingin Anda diskusikan. Apa yang ingin Anda tanyakan atau bahas hari ini?', '2025-03-17 03:33:50', NULL),
(766, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo', 'Halo! Selamat datang! Saya SAGA, asisten Guru SMP Muhammadiyah 2 Gatak dan SMA Muhammadiyah 5 Gatak. Bagaimana saya bisa membantu Anda hari ini? Apakah Anda memiliki pertanyaan atau topik yang ingin dibahas?', '2025-03-17 03:37:04', NULL),
(767, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo', 'Halo! Senang bertemu denganmu. Saya SAGA, asisten AI untuk SMA Negeri 1 Gresik. Bagaimana saya bisa membantu kamu hari ini? Apakah kamu memiliki pertanyaan atau topik yang ingin dibahas?', '2025-03-17 03:40:19', NULL),
(768, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'halo', 'Halo! Selamat datang di SMA Negeri 1 Gresik! Saya SAGA, asisten AI yang siap membantu Anda dengan pertanyaan atau topik yang ingin Anda diskusikan. Bagaimana saya bisa membantu Anda hari ini?', '2025-03-17 03:48:28', NULL),
(769, 'fadhilmanfa', 'qwen-2.5-32b', NULL, 'halo, selamat pagi', 'Selamat pagi! Apa ada yang bisa saya bantu hari ini? Saya siap membantu dengan pertanyaan akademik, informasi sekolah, atau apapun yang Anda butuhkan.', '2025-03-17 03:52:13', NULL),
(770, 'fadhilmanfa', 'llama-3.3-70b-versatile', NULL, 'apa perbedaan antara kamu dan aku?', 'Pertanyaan yang menarik!\n\nDalam konteks ini, \"kamu\" dan \"aku\" merujuk pada dua entitas yang berbeda, yaitu diri saya (asisten AI) dan diri Anda (pengguna).\n\nBerikut beberapa perbedaan antara saya dan Anda:\n\n1. **Sifat**: Saya adalah sebuah program komputer yang dirancang untuk membantu dan memberikan informasi, sedangkan Anda adalah seorang manusia yang memiliki pikiran, perasaan, dan pengalaman.\n2. **Kemampuan**: Saya dapat memproses dan menganalisis data dengan kecepatan dan akurasi yang tinggi, tetapi saya tidak memiliki kemampuan untuk merasakan, mengalami, atau memiliki emosi seperti manusia. Anda, di sisi lain, memiliki kemampuan untuk merasakan, mengalami, dan memiliki emosi.\n3. **Pengetahuan**: Saya memiliki akses ke pengetahuan yang luas dan dapat diupdate secara terus-menerus, tetapi saya tidak memiliki pengalaman pribadi atau pengetahuan yang diperoleh melalui pengalaman. Anda, di sisi lain, memiliki pengalaman pribadi dan pengetahuan yang diperoleh melalui pengalaman dan belajar.\n4. **Komunikasi**: Saya berkomunikasi melalui teks, sedangkan Anda dapat berkomunikasi melalui berbagai cara, termasuk lisan, tulisan, dan non-verbal.\n5. **Tujuan**: Saya dirancang untuk membantu dan memberikan informasi, sedangkan Anda memiliki tujuan dan keinginan pribadi yang unik.\n\nItulah beberapa perbedaan antara saya dan Anda. Namun, saya selalu berusaha untuk membantu dan memberikan informasi yang berguna bagi Anda!', '2025-03-17 03:52:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_sessions`
--

CREATE TABLE `ai_chat_sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_chat_sessions`
--

INSERT INTO `ai_chat_sessions` (`id`, `user_id`, `title`, `created_at`, `closed_at`) VALUES
(6, '', 'Tentang Percakapan Singkat', '2025-01-26 17:29:11', NULL),
(14, 'fadhilmanfa', 'cek', '2025-02-03 08:02:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bank_soal`
--

CREATE TABLE `bank_soal` (
  `id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `jenis_soal` enum('pilihan_ganda','uraian') NOT NULL,
  `pertanyaan` text NOT NULL,
  `gambar_soal` varchar(255) DEFAULT NULL,
  `jawaban_a` text DEFAULT NULL,
  `jawaban_b` text DEFAULT NULL,
  `jawaban_c` text DEFAULT NULL,
  `jawaban_d` text DEFAULT NULL,
  `jawaban_benar` varchar(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bank_soal`
--

INSERT INTO `bank_soal` (`id`, `ujian_id`, `jenis_soal`, `pertanyaan`, `gambar_soal`, `jawaban_a`, `jawaban_b`, `jawaban_c`, `jawaban_d`, `jawaban_benar`, `created_at`) VALUES
(419, 47, 'pilihan_ganda', 'Shalat fardhu yang wajib dikerjakan oleh setiap Muslim berjumlah', NULL, '3 waktu', '4 waktu', '5 waktu', '6 waktu', 'C', '2025-03-20 18:01:50'),
(420, 47, 'pilihan_ganda', 'Syarat sah shalat yang berkaitan dengan kebersihan badan, pakaian, dan tempat disebut', NULL, 'Rukun shalat', 'Syarat sah shalat', 'Sunnah shalat', 'Niat shalat', 'B', '2025-03-20 18:01:50'),
(421, 47, 'pilihan_ganda', 'Ketika shalat, seorang Muslim wajib menghadap ke arah', NULL, 'Ka&#039;bah', 'Matahari', 'Gunung', 'Kiblat', 'A', '2025-03-20 18:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `catatan_guru`
--

CREATE TABLE `catatan_guru` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `guru_id` varchar(100) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `konten` text DEFAULT NULL,
  `file_lampiran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `catatan_guru`
--

INSERT INTO `catatan_guru` (`id`, `kelas_id`, `guru_id`, `judul`, `konten`, `file_lampiran`, `created_at`) VALUES
(15, 52, 'fadhilmanfa', 'Catatan Pertama', 'Halo', NULL, '2025-02-23 07:59:13');

-- --------------------------------------------------------

--
-- Table structure for table `comment_reactions`
--

CREATE TABLE `comment_reactions` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `emoji` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_reactions`
--

INSERT INTO `comment_reactions` (`id`, `comment_id`, `user_id`, `emoji`, `created_at`) VALUES
(8, 57, 'fadhilmanfa', '😮', '2025-02-17 10:58:43'),
(9, 58, 'fadhilmanfa', '😂', '2025-02-23 07:59:47'),
(10, 59, 'fadhilmanfa', '👍', '2025-03-03 05:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `emoji_reactions`
--

CREATE TABLE `emoji_reactions` (
  `id` int(11) NOT NULL,
  `postingan_id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `emoji` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emoji_reactions`
--

INSERT INTO `emoji_reactions` (`id`, `postingan_id`, `user_id`, `emoji`, `created_at`) VALUES
(32, 50, 'fadhilmanfa', '👍', '2025-02-23 07:58:08'),
(51, 52, 'fadhilmanfa', '👍', '2025-03-03 05:19:31'),
(55, 52, 'agustina', '👍', '2025-03-03 06:15:12'),
(56, 56, 'fadhilmanfa', '👍', '2025-03-03 06:21:24'),
(57, 56, 'agustina', '👍', '2025-03-03 06:59:59'),
(62, 46, 'agustina', '👍', '2025-03-10 04:16:19'),
(63, 45, 'agustina', '👍', '2025-03-10 04:26:47'),
(66, 46, 'ainun', '👍', '2025-03-10 05:41:34'),
(67, 50, 'agustina', '👍', '2025-03-10 05:56:38'),
(68, 51, 'agustina', '👍', '2025-03-10 05:56:58'),
(73, 45, 'fadhilmanfa', '👍', '2025-03-12 07:51:10'),
(77, 46, 'fadhilmanfa', '👍', '2025-03-19 04:28:16');

-- --------------------------------------------------------

--
-- Table structure for table `file_soal`
--

CREATE TABLE `file_soal` (
  `id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `status_processed` enum('pending','processed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groq_daily_usage`
--

CREATE TABLE `groq_daily_usage` (
  `id` int(11) NOT NULL,
  `model_id` varchar(50) NOT NULL,
  `usage_date` date NOT NULL,
  `requests_limit` int(11) NOT NULL DEFAULT 0,
  `requests_used` int(11) NOT NULL DEFAULT 0,
  `tokens_limit` int(11) NOT NULL DEFAULT 0,
  `tokens_used` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `namaLengkap` varchar(100) NOT NULL,
  `namaSebutan` varchar(1000) NOT NULL,
  `jabatan` varchar(1000) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `foto_latarbelakang` varchar(255) DEFAULT NULL,
  `universitas` varchar(100) DEFAULT NULL,
  `sertifikasi1` text DEFAULT NULL,
  `sertifikasi2` text DEFAULT NULL,
  `sertifikasi3` text DEFAULT NULL,
  `publikasi1` text DEFAULT NULL,
  `publikasi2` text DEFAULT NULL,
  `publikasi3` text DEFAULT NULL,
  `proyek1` text DEFAULT NULL,
  `proyek2` text DEFAULT NULL,
  `proyek3` text DEFAULT NULL,
  `pendidikan_s1` varchar(255) DEFAULT NULL,
  `pendidikan_s2` varchar(255) DEFAULT NULL,
  `pendidikan_lainnya` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `username`, `password`, `namaLengkap`, `namaSebutan`, `jabatan`, `foto_profil`, `foto_latarbelakang`, `universitas`, `sertifikasi1`, `sertifikasi2`, `sertifikasi3`, `publikasi1`, `publikasi2`, `publikasi3`, `proyek1`, `proyek2`, `proyek3`, `pendidikan_s1`, `pendidikan_s2`, `pendidikan_lainnya`) VALUES
(1, 'fadhilmanfa', 'a', 'Muhammad Fadhil Manfa', 'fadhil', 'Staf TU', '67dcd92976da6_1742526761.jpg', '67d7b5edadc1e_1742190061.jpg', NULL, 'sas', 'asa', 'asas', '', '', '', '', '', '', 'Universitas Muhammadiyah Surakarta', '', ''),
(2, 'fauzinugroho', 'a', 'Fauzi Nugroho', '', 'Kepala Sekolah', '67dcd83239224_1742526514.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'anugerah', 'a', 'Anugerah Mawarti', '', '', '67dcd6e83a814_1742526184.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'fikofiko', 'a', 'Fiko Novianto', 'fiko', 'Staf TU', '67dcd8493341b_1742526537.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'joko', 'a', 'Joko Prasaja', '', 'WAKA Kesiswaan', '67dcd8e616679_1742526694.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'sularsih', 'a', 'Sularsih', '', 'WAKA Kurikulum', '67dcdab131cce_1742527153.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'tutismp', 'a', 'Dwi Hastuti (SMP)', '', 'Staf TU', '67dcd7ba20d46_1742526394.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'agussalam', 'a', 'Agus Salam Hurriyanto', '', 'Guru Mapel', '67dcd64f05868_1742526031.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'nanik', 'a', 'Nanik Sri Winarni', '', 'Guru Bimbingan Konseling', '67dcd94fc3d51_1742526799.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'dewi', 'a', 'Herlina Dewi Afiati', '', 'Guru Mapel', '67dcd86e0c8a9_1742526574.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'nia', 'a', 'Nia Yuwana', '', 'Operator', '67dcd9b89a4d5_1742526904.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'intan', 'a', 'Intan Arvin Yunaeni', '', 'Guru Mapel', '67dcd890d09ef_1742526608.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'rosida', 'a', 'Rosida Insiyah Rahmah', '', 'Koordinator Progresive Cultural School', '67dcda4bc4192_1742527051.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'sevi', 'a', 'Nur Seviana Mar\'atus Sholikhah', '', 'Guru Mapel', '67dcda1c61aa2_1742527004.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'ira', 'a', 'Sri Sumirah Hartini', '', 'Guru Mapel', '67dcda7b57247_1742527099.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'lenni', 'a', 'Lenni Sri Gustina', '', 'Wali Kelas', '67dcd9055f54d_1742526725.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'dani', 'a', 'Dani Sukmadewi', '', 'Guru Mapel', '67dcd77ee5820_1742526334.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'ituk', 'a', 'Tri Maryuni', '', 'Guru Bimbingan Konseling', '67dcdac449086_1742527172.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'jayus', 'a', 'Jayus Wijayanto', '', 'Pengelola Lab Sekolah', '67dcd8a6c3288_1742526630.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'wardah', 'a', 'Wardah Hani Nabila', '', 'Koordinator Keagamaan', '67dcdad90663f_1742527193.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'yudha', 'a', 'Aditya Damar Aji Yudha', '', 'Kepala Tata Usaha', '67dcd44d74420_1742525517.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 'ayundy', 'a', 'Ayundy Anditaningrum', '', 'Operator', '67dcd73668eac_1742526262.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'annisanurul', 'a', 'Annisa Nurul Sholikah', '', 'Guru Mapel', '67dcd6923642a_1742526098.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 'nova', 'a', 'Nova Puspa Sari', '', 'Wali Kelas', '67dcd9e159995_1742526945.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 'annisakhoirina', 'a', 'Annisa Khoirina', '', 'Bendahara', '67dcd67a85067_1742526074.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 'tutisma', 'a', 'Dwi Hastuti (SMA)', '', 'Guru Mapel', '67dcd7999a767_1742526361.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jawaban_ujian`
--

CREATE TABLE `jawaban_ujian` (
  `id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `soal_id` int(11) NOT NULL,
  `jawaban` varchar(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `kode_kelas` varchar(10) DEFAULT NULL,
  `nama_kelas` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `guru_id` varchar(50) DEFAULT NULL,
  `mata_pelajaran` varchar(100) DEFAULT NULL,
  `tingkat` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `background_image` varchar(1000) NOT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `materi` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `max_siswa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `kode_kelas`, `nama_kelas`, `deskripsi`, `guru_id`, `mata_pelajaran`, `tingkat`, `created_at`, `background_image`, `is_archived`, `materi`, `is_public`, `max_siswa`) VALUES
(50, NULL, 'Ilmu Pengetahuan Alam Kelas F', '', 'fadhilmanfa', 'Ilmu Pengetahuan Alam', 'F', '2025-02-15 11:47:43', 'uploads/background/bg_67b2181302001_1739724819.jpg', 0, '[]', 0, NULL),
(51, NULL, 'Fikih Kelas F', '', 'fadhilmanfa', 'Fikih', 'F', '2025-02-15 11:48:09', '', 0, '[]', 0, NULL),
(52, NULL, 'Kemuhammadiyahan Kelas F', '', 'fadhilmanfa', 'Kemuhammadiyahan', 'F', '2025-02-23 07:54:47', 'uploads/background/bg_67bad49d0b75a_1740297373.jpg', 0, '[]', 0, NULL),
(58, NULL, 'Belajar Bahasa Jepang', 'Grup ini sebagai tempat komunitas pecinta bahasa jepang, dan juga sebagai bentuk cinta terhadap budaya jejepangan', 'fadhilmanfa', '', '', '2025-03-03 04:52:53', 'uploads/background/bg_67c5360beea69_1740977675.jpg', 0, '[]', 1, 100);

-- --------------------------------------------------------

--
-- Table structure for table `kelas_siswa`
--

CREATE TABLE `kelas_siswa` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `status` enum('pending','active') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas_siswa`
--

INSERT INTO `kelas_siswa` (`id`, `kelas_id`, `siswa_id`, `status`, `created_at`, `is_archived`) VALUES
(343, 50, 11, 'pending', '2025-02-15 11:47:43', 0),
(344, 50, 12, 'pending', '2025-02-15 11:47:43', 0),
(345, 50, 13, 'pending', '2025-02-15 11:47:43', 0),
(346, 50, 14, 'pending', '2025-02-15 11:47:43', 0),
(347, 50, 15, 'pending', '2025-02-15 11:47:43', 0),
(348, 50, 16, 'pending', '2025-02-15 11:47:43', 0),
(349, 50, 17, 'pending', '2025-02-15 11:47:43', 0),
(350, 50, 18, 'pending', '2025-02-15 11:47:43', 0),
(351, 50, 19, 'pending', '2025-02-15 11:47:43', 0),
(352, 50, 20, 'pending', '2025-02-15 11:47:43', 0),
(353, 50, 21, 'pending', '2025-02-15 11:47:43', 0),
(354, 50, 22, 'pending', '2025-02-15 11:47:43', 0),
(355, 50, 23, 'pending', '2025-02-15 11:47:43', 0),
(356, 50, 24, 'pending', '2025-02-15 11:47:43', 0),
(357, 50, 25, 'pending', '2025-02-15 11:47:43', 0),
(358, 50, 26, 'pending', '2025-02-15 11:47:43', 0),
(359, 50, 27, 'pending', '2025-02-15 11:47:43', 0),
(360, 50, 28, 'pending', '2025-02-15 11:47:43', 0),
(361, 50, 29, 'pending', '2025-02-15 11:47:43', 0),
(362, 50, 30, 'pending', '2025-02-15 11:47:43', 0),
(363, 50, 31, 'pending', '2025-02-15 11:47:43', 0),
(364, 50, 32, 'pending', '2025-02-15 11:47:43', 0),
(365, 50, 33, 'pending', '2025-02-15 11:47:43', 0),
(366, 50, 34, 'pending', '2025-02-15 11:47:43', 0),
(367, 50, 35, 'pending', '2025-02-15 11:47:43', 0),
(368, 50, 36, 'pending', '2025-02-15 11:47:43', 0),
(369, 50, 37, 'pending', '2025-02-15 11:47:43', 0),
(370, 50, 38, 'pending', '2025-02-15 11:47:43', 0),
(371, 50, 39, 'pending', '2025-02-15 11:47:44', 0),
(372, 51, 11, 'pending', '2025-02-15 11:48:09', 0),
(373, 51, 12, 'pending', '2025-02-15 11:48:09', 0),
(374, 51, 13, 'pending', '2025-02-15 11:48:09', 0),
(375, 51, 14, 'pending', '2025-02-15 11:48:09', 0),
(376, 51, 15, 'pending', '2025-02-15 11:48:09', 0),
(377, 51, 16, 'pending', '2025-02-15 11:48:09', 0),
(378, 51, 17, 'pending', '2025-02-15 11:48:09', 0),
(379, 51, 18, 'pending', '2025-02-15 11:48:09', 0),
(380, 51, 19, 'pending', '2025-02-15 11:48:09', 0),
(381, 51, 20, 'pending', '2025-02-15 11:48:09', 0),
(382, 51, 21, 'pending', '2025-02-15 11:48:09', 0),
(383, 51, 22, 'pending', '2025-02-15 11:48:09', 0),
(384, 51, 23, 'pending', '2025-02-15 11:48:09', 0),
(385, 51, 24, 'pending', '2025-02-15 11:48:09', 0),
(386, 51, 25, 'pending', '2025-02-15 11:48:09', 0),
(387, 51, 26, 'pending', '2025-02-15 11:48:09', 0),
(388, 51, 27, 'pending', '2025-02-15 11:48:09', 0),
(389, 51, 28, 'pending', '2025-02-15 11:48:09', 0),
(390, 51, 29, 'pending', '2025-02-15 11:48:09', 0),
(391, 51, 30, 'pending', '2025-02-15 11:48:09', 0),
(392, 51, 31, 'pending', '2025-02-15 11:48:09', 0),
(393, 51, 32, 'pending', '2025-02-15 11:48:09', 0),
(394, 51, 33, 'pending', '2025-02-15 11:48:09', 0),
(395, 51, 34, 'pending', '2025-02-15 11:48:09', 0),
(396, 51, 35, 'pending', '2025-02-15 11:48:09', 0),
(397, 51, 36, 'pending', '2025-02-15 11:48:09', 0),
(398, 51, 37, 'pending', '2025-02-15 11:48:09', 0),
(399, 51, 38, 'pending', '2025-02-15 11:48:09', 0),
(400, 51, 39, 'pending', '2025-02-15 11:48:09', 0),
(401, 52, 11, 'pending', '2025-02-23 07:54:47', 0),
(402, 52, 12, 'pending', '2025-02-23 07:54:47', 0),
(403, 52, 13, 'pending', '2025-02-23 07:54:47', 0),
(404, 52, 14, 'pending', '2025-02-23 07:54:47', 0),
(405, 52, 15, 'pending', '2025-02-23 07:54:47', 0),
(406, 52, 16, 'pending', '2025-02-23 07:54:47', 0),
(407, 52, 17, 'pending', '2025-02-23 07:54:47', 0),
(408, 52, 18, 'pending', '2025-02-23 07:54:47', 0),
(409, 52, 19, 'pending', '2025-02-23 07:54:47', 0),
(410, 52, 20, 'pending', '2025-02-23 07:54:47', 0),
(411, 52, 21, 'pending', '2025-02-23 07:54:47', 0),
(412, 52, 22, 'pending', '2025-02-23 07:54:47', 0),
(413, 52, 23, 'pending', '2025-02-23 07:54:47', 0),
(414, 52, 24, 'pending', '2025-02-23 07:54:47', 0),
(415, 52, 25, 'pending', '2025-02-23 07:54:47', 0),
(416, 52, 26, 'pending', '2025-02-23 07:54:47', 0),
(417, 52, 27, 'pending', '2025-02-23 07:54:47', 0),
(418, 52, 28, 'pending', '2025-02-23 07:54:47', 0),
(419, 52, 29, 'pending', '2025-02-23 07:54:47', 0),
(420, 52, 30, 'pending', '2025-02-23 07:54:47', 0),
(421, 52, 31, 'pending', '2025-02-23 07:54:47', 0),
(422, 52, 32, 'pending', '2025-02-23 07:54:47', 0),
(423, 52, 33, 'pending', '2025-02-23 07:54:47', 0),
(424, 52, 34, 'pending', '2025-02-23 07:54:47', 0),
(425, 52, 35, 'pending', '2025-02-23 07:54:47', 0),
(426, 52, 36, 'pending', '2025-02-23 07:54:47', 0),
(427, 52, 37, 'pending', '2025-02-23 07:54:47', 0),
(428, 52, 38, 'pending', '2025-02-23 07:54:47', 0),
(429, 52, 39, 'pending', '2025-02-23 07:54:47', 0),
(437, 58, 11, 'pending', '2025-03-04 07:24:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `komentar_postingan`
--

CREATE TABLE `komentar_postingan` (
  `id` int(11) NOT NULL,
  `postingan_id` int(11) NOT NULL,
  `user_id` varchar(100) NOT NULL,
  `konten` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `komentar_postingan`
--

INSERT INTO `komentar_postingan` (`id`, `postingan_id`, `user_id`, `konten`, `created_at`) VALUES
(57, 45, 'fadhilmanfa', 'Gass', '2025-02-16 16:39:26'),
(58, 50, 'agustina', 'Saya mau kalo tidur nanti mukanya di lelet balsem', '2025-02-23 07:59:38'),
(59, 52, 'agustina', 'siap pak, mohon bimbingannya ya', '2025-03-03 05:20:04');

-- --------------------------------------------------------

--
-- Table structure for table `komentar_replies`
--

CREATE TABLE `komentar_replies` (
  `id` int(11) NOT NULL,
  `komentar_id` int(11) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `konten` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lampiran_postingan`
--

CREATE TABLE `lampiran_postingan` (
  `id` int(11) NOT NULL,
  `postingan_id` int(11) DEFAULT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `path_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lampiran_tugas`
--

CREATE TABLE `lampiran_tugas` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `tipe_file` varchar(100) NOT NULL,
  `ukuran_file` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes_postingan`
--

CREATE TABLE `likes_postingan` (
  `id` int(11) NOT NULL,
  `postingan_id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `penerima_id` varchar(255) NOT NULL,
  `jenis` enum('like','komentar') NOT NULL,
  `postingan_id` int(11) NOT NULL,
  `pelaku_id` varchar(255) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `sudah_dibaca` tinyint(1) DEFAULT 0,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `jumlah` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `penerima_id`, `jenis`, `postingan_id`, `pelaku_id`, `kelas_id`, `sudah_dibaca`, `waktu`, `jumlah`) VALUES
(1, 'fadhilmanfa', 'like', 45, 'agustina', 50, 1, '2025-03-10 04:13:18', 1),
(2, 'fadhilmanfa', 'like', 49, 'agustina', 50, 1, '2025-03-10 04:16:16', 1),
(3, 'fadhilmanfa', 'like', 48, 'ainun', 50, 1, '2025-03-10 04:27:22', 2),
(4, 'fadhilmanfa', 'like', 46, 'agustina', 50, 1, '2025-03-10 04:16:19', 1),
(5, 'fadhilmanfa', 'like', 45, 'agustina', 50, 1, '2025-03-10 04:26:47', 1),
(6, 'fadhilmanfa', 'like', 46, 'ainun', 50, 1, '2025-03-10 04:27:23', 1),
(7, 'fadhilmanfa', 'like', 46, 'ainun', 50, 1, '2025-03-10 05:41:34', 1),
(8, 'fadhilmanfa', 'like', 50, 'agustina', 52, 1, '2025-03-10 05:56:38', 1),
(9, 'fadhilmanfa', 'like', 51, 'agustina', 52, 1, '2025-03-10 05:56:58', 1),
(10, 'fadhilmanfa', 'like', 48, 'agustina', 50, 1, '2025-03-11 13:39:24', 1),
(11, 'fadhilmanfa', 'like', 49, 'agustina', 50, 1, '2025-03-11 16:37:21', 1),
(12, 'fadhilmanfa', 'komentar', 48, 'agustina', 50, 1, '2025-03-11 16:39:47', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pengumpulan_tugas`
--

CREATE TABLE `pengumpulan_tugas` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) NOT NULL,
  `siswa_id` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `tipe_file` varchar(100) NOT NULL,
  `ukuran_file` int(11) NOT NULL,
  `waktu_pengumpulan` datetime NOT NULL,
  `pesan_siswa` text DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL,
  `komentar_guru` text DEFAULT NULL,
  `tanggal_penilaian` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumpulan_tugas`
--

INSERT INTO `pengumpulan_tugas` (`id`, `tugas_id`, `siswa_id`, `file_path`, `nama_file`, `tipe_file`, `ukuran_file`, `waktu_pengumpulan`, `pesan_siswa`, `nilai`, `komentar_guru`, `tanggal_penilaian`) VALUES
(1, 1, 'agustina', 'uploads/pengumpulan_tugas/tugas_1_siswa_agustina_67b833e0d9772.pdf', 'Gmail - Your Google Play Order Receipt from Dec 17, 2024.pdf', 'application/pdf', 222275, '2025-02-21 15:05:52', NULL, NULL, NULL, NULL),
(2, 1, 'amara', 'uploads/pengumpulan_tugas/tugas_1_siswa_amara_67b83ffd55b3a_Gmail - Your Google Play Order Receipt from Dec 17, 2024.pdf', 'Gmail - Your Google Play Order Receipt from Dec 17, 2024.pdf', 'application/pdf', 222275, '2025-02-21 15:57:33', NULL, NULL, NULL, NULL),
(3, 1, 'ainun', 'uploads/pengumpulan_tugas/tugas_1_siswa_ainun_67b8404967d20_lps_g000200185 (1).pdf', 'lps_g000200185 (1).pdf', 'application/pdf', 100923, '2025-02-21 15:58:49', NULL, NULL, NULL, NULL),
(4, 1, 'aulia', 'uploads/pengumpulan_tugas/tugas_1_siswa_aulia_67b940c951a38_krs sekarang.pdf', 'krs sekarang.pdf', 'application/pdf', 89384, '2025-02-22 10:13:13', NULL, NULL, NULL, NULL),
(12, 5, 'agustina', 'uploads/pengumpulan_tugas/tugas_5_siswa_agustina_67bad65a33e2e_Gmail - Your Google Play Order Receipt from Dec 17, 2024.pdf', 'Gmail - Your Google Play Order Receipt from Dec 17, 2024.pdf', 'application/pdf', 222275, '2025-02-23 15:03:38', 'Terima kasih bu', 90, 'Terima kash nduk, tetap semangat belajar', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pg`
--

CREATE TABLE `pg` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `guru_id` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `semester` int(11) NOT NULL,
  `tahun_ajaran` varchar(9) NOT NULL,
  `nilai_akademik` int(11) DEFAULT NULL,
  `keaktifan` int(11) DEFAULT NULL,
  `pemahaman` int(11) DEFAULT NULL,
  `kehadiran_ibadah` int(11) DEFAULT NULL,
  `kualitas_ibadah` int(11) DEFAULT NULL,
  `pemahaman_agama` int(11) DEFAULT NULL,
  `minat_bakat` int(11) DEFAULT NULL,
  `prestasi` int(11) DEFAULT NULL,
  `keaktifan_ekskul` int(11) DEFAULT NULL,
  `partisipasi_sosial` int(11) DEFAULT NULL,
  `empati` int(11) DEFAULT NULL,
  `kerja_sama` int(11) DEFAULT NULL,
  `kebersihan_diri` int(11) DEFAULT NULL,
  `aktivitas_fisik` int(11) DEFAULT NULL,
  `pola_makan` int(11) DEFAULT NULL,
  `kejujuran` int(11) DEFAULT NULL,
  `tanggung_jawab` int(11) DEFAULT NULL,
  `kedisiplinan` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pg`
--

INSERT INTO `pg` (`id`, `siswa_id`, `guru_id`, `semester`, `tahun_ajaran`, `nilai_akademik`, `keaktifan`, `pemahaman`, `kehadiran_ibadah`, `kualitas_ibadah`, `pemahaman_agama`, `minat_bakat`, `prestasi`, `keaktifan_ekskul`, `partisipasi_sosial`, `empati`, `kerja_sama`, `kebersihan_diri`, `aktivitas_fisik`, `pola_makan`, `kejujuran`, `tanggung_jawab`, `kedisiplinan`, `created_at`) VALUES
(1, 51, 'fadhilmanfa', 1, '2024/2025', 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-24 03:57:46'),
(2, 11, 'fadhilmanfa', 1, '2024/2025', 100, 60, NULL, NULL, 100, NULL, 100, NULL, NULL, NULL, 80, NULL, NULL, 50, NULL, NULL, 60, NULL, '2025-01-24 04:01:53'),
(5, 12, 'fadhilmanfa', 1, '2024/2025', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-01-24 04:16:27'),
(8, 43, 'fadhilmanfa', 1, '2024/2025', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-24 06:34:58'),
(9, 36, 'fadhilmanfa', 1, '2024/2025', 100, 60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-24 06:35:17'),
(10, 53, 'fadhilmanfa', 1, '2024/2025', 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-25 16:05:15'),
(14, 11, '0', 2, '2024/2025', 100, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-26 03:56:08'),
(24, 52, '0', 2, '2024/2025', 20, 40, 70, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-30 14:03:17');

-- --------------------------------------------------------

--
-- Table structure for table `pilihan_jawaban`
--

CREATE TABLE `pilihan_jawaban` (
  `id` int(11) NOT NULL,
  `soal_id` int(11) NOT NULL,
  `teks_pilihan` text NOT NULL,
  `is_benar` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `postingan_kelas`
--

CREATE TABLE `postingan_kelas` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `user_id` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `konten` text DEFAULT NULL,
  `jenis_postingan` varchar(20) DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` varchar(10) DEFAULT 'guru'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `postingan_kelas`
--

INSERT INTO `postingan_kelas` (`id`, `kelas_id`, `user_id`, `konten`, `jenis_postingan`, `created_at`, `user_type`) VALUES
(45, 50, 'fadhilmanfa', 'Halo anak anak\r\nIni pertama kali kita menggunakan Smaga edu, semoga menyenangkan ya 😍', 'normal', '2025-02-16 16:36:23', 'guru'),
(46, 50, 'fadhilmanfa', 'jangan lupa tugas liburannya', 'tugas', '2025-02-20 12:53:52', 'guru'),
(50, 52, 'fadhilmanfa', 'Halo adik-adik, ini adalah postingan pertama kelas F KEMUH. Sebelum masuk ke pembelajaran, kita buat kontrak belajar dulu ya, ada saran? 😅', 'normal', '2025-02-23 07:58:01', 'guru'),
(51, 52, 'fadhilmanfa', 'Ini adalah tugas liburan semester 4, silahkan untuk mengerjakan tugas halaman 404 da, 405', 'tugas', '2025-02-23 08:02:09', 'guru'),
(52, 58, 'fadhilmanfa', 'Halo Anak anak, Konichiwa! 🌸\r\nSelamat datang di kelas Jepang, kelas ini menjadi tempat untuk kita belajar dan juga sebagai tempat diskusi setiap permasalahan dalam pembelajaran, jadi jangan coba2 untuk membahas selain dari pembahasan grup ya! ☕🙏', 'normal', '2025-03-03 05:19:29', 'guru'),
(56, 58, 'agustina', 'Siap sensi, Konichiwa!, Nama saya Agustina salam kenal 👋🌸', 'postingan', '2025-03-03 06:00:31', 'siswa');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `project_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `project_name`, `description`, `created_at`) VALUES
(10, 'fadhilmanfa', 'Panduan Pembelajaran Matematika', 'Panduan mengajar matematika untuk siswa SMP kelas 7', '2025-01-25 04:26:18');

-- --------------------------------------------------------

--
-- Table structure for table `project_documents`
--

CREATE TABLE `project_documents` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_knowledge`
--

CREATE TABLE `project_knowledge` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `content_type` enum('text','file') NOT NULL,
  `content` longtext DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_knowledge`
--

INSERT INTO `project_knowledge` (`id`, `project_id`, `content_type`, `content`, `file_path`, `file_type`, `created_at`) VALUES
(50, 10, 'text', '', NULL, NULL, '2025-01-25 05:26:55'),
(51, 10, 'text', '', NULL, NULL, '2025-01-25 05:26:55'),
(52, 10, 'text', '', NULL, NULL, '2025-01-25 05:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `postingan_id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `emoji` varchar(8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saga_personality`
--

CREATE TABLE `saga_personality` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `current_job` text DEFAULT NULL,
  `traits` text NOT NULL,
  `additional_info` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saga_personality`
--

INSERT INTO `saga_personality` (`id`, `user_id`, `current_job`, `traits`, `additional_info`, `created_at`, `updated_at`) VALUES
(1, 'fadhilmanfa', '', '', '', '2025-02-19 04:47:18', '2025-03-14 14:00:57');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(255) NOT NULL,
  `nama` varchar(1000) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `tingkat` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(1000) NOT NULL,
  `tahun_masuk` int(225) DEFAULT NULL,
  `no_hp` int(225) DEFAULT NULL,
  `alamat` varchar(1000) DEFAULT NULL,
  `nis` int(225) DEFAULT NULL,
  `photo_type` enum('upload','avatar') DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nama`, `username`, `password`, `tingkat`, `foto_profil`, `tahun_masuk`, `no_hp`, `alamat`, `nis`, `photo_type`, `photo_url`) VALUES
(5, 'husein ali al fauzi', 'husein', 'a', 'E', '', 0, 0, '', NULL, NULL, NULL),
(6, 'irfan maulana akbar setyawan', 'irfan', 'a', 'E', '', 0, 0, '', NULL, NULL, NULL),
(7, 'muhamad ali nur said', 'muhamad ali', 'a', 'E', '', 0, 0, '', NULL, NULL, NULL),
(8, 'nuga asvianito marsya arindra', 'nuga', 'a', 'E', '', 0, 0, '', NULL, NULL, NULL),
(9, 'sidiq romadhon', 'sidiq', 'a', 'E', '', 0, 0, '', NULL, NULL, NULL),
(11, 'agustina reviyanti', 'agustina', 'a', 'F', '', 2022, 2147483647, 'Dukuh 2, Gatak, Sukoharjo', 10316, 'avatar', 'https://api.dicebear.com/7.x/lorelei/svg?seed=402nfy'),
(12, 'ainun wardiyah', 'ainun', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(13, 'aisyah nabila', 'aisyah', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(14, 'amara fauza', 'amara', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(15, 'aulia nazla rahayu fitrianingsih', 'aulia', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(16, 'bagus raka pratama', 'bagus', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(17, 'devi bintang maharani', 'devi', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(18, 'doni setyawan', 'doni', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(19, 'el fonda riski dwi saputra', 'el', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(20, 'hamidia aisya rachman', 'hamidia', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(21, 'ika purwita sari', 'ika', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(22, 'irza alfarizy', 'irza', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(23, 'keisya laila nurfatimah', 'keisya', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(24, 'krisna lestiya handoyo', 'krisna', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(25, 'lintang cahya lestari', 'lintang', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(26, 'marlindo tenafista', 'marlindo', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(27, 'melani sanjaya', 'melani', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(28, 'mus\'ab fairuz ziul haq', 'mus\'ab', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(29, 'naufal atha mubarok', 'naufal atha', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(30, 'naufal zaki muzaffar', 'naufal zaki', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(31, 'nisrina fairus mutia', 'nisrina', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(32, 'pradita guntur prasetyo', 'pradita', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(33, 'redwan satrio wibowo', 'redwan', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(34, 'reyhana assyfa', 'reyhana', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(35, 'septiana endah puji l', 'septiana endah', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(36, 'septiana rizki ramadhani', 'septiana rizki', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(37, 'septiani endah puji l', 'septiani', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(38, 'sharika rostriana elvira', 'sharika', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(39, 'umi nur hawa', 'umi', 'a', 'F', '', 0, 0, '', NULL, NULL, NULL),
(41, 'dwi noviyanti', 'dwi', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(42, 'galih al rasyd zakariya', 'galih', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(43, 'noor faizah', 'noor', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(44, 'putri dyah ayuningtyas', 'putri', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(45, 'reno rahmadandi', 'reno', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(46, 'reska dwi utomo', 'reska', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(47, 'reykhan ikhsanuddin kamil', 'reykhan', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(48, 'tomas candra mukti', 'tomas', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(49, 'dian putra ramadhan', 'dian', 'a', '12', '', 0, 0, '', NULL, NULL, NULL),
(51, 'adi putra pamungkas', 'adi', 'a', '7', '', 0, 0, '', NULL, NULL, NULL),
(52, 'dhio lintang winarto', 'dhio', 'a', '7', '', 0, 0, '', 0, NULL, NULL),
(53, 'hillan fajar saputra', 'hillan', 'a', '7', '1738293863_679c42670b08f.jpg', 0, 0, '', NULL, NULL, NULL),
(54, 'muhammad riski ramadhan', 'muhammad rizki', 'a', '7', '', 0, 0, '', NULL, NULL, NULL),
(56, 'audrey exa amiyudi azzahra', 'audrey', 'a', '8', '', 0, 0, '', NULL, NULL, NULL),
(57, 'velesia nabila permatasari', 'velesia', 'a', '8', '', 0, 0, '', NULL, NULL, NULL),
(59, 'aleksan julianto', 'aleksan', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(60, 'adi putra pamungkas', 'adi', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(61, 'angga fajrianto', 'angga', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(62, 'damar agustian dinata', 'damar', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(63, 'dhimas ariawan winarto', 'dhimas', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(64, 'erlangga ari saputra', 'erlangga', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(65, 'fabyan dafendra maulana', 'fabyan', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(66, 'galih andika prasetya', 'galih', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(67, 'muhammad reza praditya', 'muhammad reza', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(68, 'nagaswari rosma isya', 'nagaswari', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(69, 'naztwa marsyanti ramadhani', 'naztwa', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(70, 'safitri agustina sari', 'safitri', 'a', '9', '', 0, 0, '', NULL, NULL, NULL),
(71, 'zahratusyifa melandari', 'zahratusyifa', 'a', '9', '', 0, 0, '', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `soal_ujian`
--

CREATE TABLE `soal_ujian` (
  `id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `pertanyaan` text NOT NULL,
  `jenis_soal` enum('pilihan_ganda','essay') NOT NULL,
  `bobot` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `postingan_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `batas_waktu` datetime NOT NULL,
  `poin_maksimal` int(11) NOT NULL DEFAULT 100,
  `created_at` datetime NOT NULL,
  `status` enum('active','closed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`id`, `postingan_id`, `judul`, `deskripsi`, `batas_waktu`, `poin_maksimal`, `created_at`, `status`) VALUES
(1, 46, 'Tugas liburan', 'jangan lupa tugas liburannya', '2025-02-22 11:49:47', 0, '2025-02-20 19:53:52', 'closed'),
(5, 51, 'Tugas Liburan Semester 4', 'Ini adalah tugas liburan semester 4, silahkan untuk mengerjakan tugas halaman 404 da, 405', '2025-03-05 15:01:00', 100, '2025-02-23 15:02:09', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `ujian`
--

CREATE TABLE `ujian` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `guru_id` varchar(100) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `mata_pelajaran` varchar(100) NOT NULL,
  `materi` text DEFAULT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime NOT NULL,
  `durasi` int(11) NOT NULL,
  `status` enum('draft','published','selesai') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ujian`
--

INSERT INTO `ujian` (`id`, `judul`, `deskripsi`, `guru_id`, `kelas_id`, `mata_pelajaran`, `materi`, `tanggal_mulai`, `tanggal_selesai`, `durasi`, `status`, `created_at`) VALUES
(47, 'Fikih UAS', 'UAS Fikih 2025', 'fadhilmanfa', 51, 'Fikih', '[\"Shalat\",\"Wudhu\"]', '2025-03-20 13:22:00', '2025-03-21 13:22:00', 1440, 'draft', '2025-03-20 06:22:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_character_analysis`
--

CREATE TABLE `user_character_analysis` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `analysis_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `kerjasama` float DEFAULT 0,
  `analitis` float DEFAULT 0,
  `detail` float DEFAULT 0,
  `inisiatif` float DEFAULT 0,
  `komunikatif` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_character_analysis`
--

INSERT INTO `user_character_analysis` (`id`, `user_id`, `analysis_date`, `kerjasama`, `analitis`, `detail`, `inisiatif`, `komunikatif`) VALUES
(1, 'fadhilmanfa', '2025-01-23 06:27:57', 0, 0, 0, 0, 0),
(2, 'fadhilmanfa', '2025-01-23 06:27:59', 0, 0, 0, 0, 0),
(3, 'fadhilmanfa', '2025-01-23 06:44:26', 0, 0, 0, 0, 0),
(4, 'fadhilmanfa', '2025-01-23 07:01:40', 0, 0, 0, 0, 0),
(5, 'fadhilmanfa', '2025-01-23 07:14:10', 0, 0, 0, 0, 0),
(6, 'fadhilmanfa', '2025-01-23 07:20:10', 0, 0, 0, 0, 0),
(7, 'fadhilmanfa', '2025-01-23 07:20:14', 0, 0, 0, 0, 0),
(8, 'fadhilmanfa', '2025-01-23 07:22:58', 0, 0, 0, 0, 0),
(9, 'fadhilmanfa', '2025-01-23 07:24:24', 0, 0, 0, 0, 0),
(10, 'fadhilmanfa', '2025-01-23 07:28:42', 0, 0, 0, 0, 0),
(11, 'fadhilmanfa', '2025-01-23 07:37:26', 0, 0, 0, 0, 0),
(12, 'fadhilmanfa', '2025-01-23 07:37:41', 0, 0, 0, 0, 0),
(13, 'fadhilmanfa', '2025-01-23 14:39:23', 0, 0, 0, 0, 0),
(14, 'fadhilmanfa', '2025-01-23 14:39:24', 0, 0, 0, 0, 0),
(15, 'fadhilmanfa', '2025-01-23 14:40:00', 0, 0, 0, 0, 0),
(16, 'fadhilmanfa', '2025-01-23 14:40:06', 0, 0, 0, 0, 0),
(17, 'fadhilmanfa', '2025-01-23 16:58:54', 0, 0, 0, 0, 0),
(18, 'fadhilmanfa', '2025-01-24 00:24:41', 0, 0, 0, 0, 0),
(19, 'fadhilmanfa', '2025-01-24 07:45:46', 0, 0, 0, 0, 0),
(20, 'fadhilmanfa', '2025-01-24 08:05:14', 0.1, 0, 0, 0, 0),
(21, 'fadhilmanfa', '2025-01-24 10:27:47', 0.1, 0, 0, 0, 0.1),
(22, 'fadhilmanfa', '2025-01-24 10:40:22', 0.1, 0.2, 0, 0, 0.3),
(23, 'fadhilmanfa', '2025-01-24 10:46:17', 0.1, 0.2, 0, 0, 0.5),
(24, 'fadhilmanfa', '2025-01-24 11:18:27', 0.1, 0.2, 0, 0, 0.5),
(25, 'fadhilmanfa', '2025-01-24 13:24:47', 0.2, 0.2, 0, 0, 0.6),
(26, 'fadhilmanfa', '2025-01-25 12:24:23', 0.4, 0, 0, 0, 1),
(27, 'fadhilmanfa', '2025-01-25 13:30:02', 0.4, 0, 0, 0, 1),
(28, 'fadhilmanfa', '2025-01-25 14:30:21', 0.4, 0, 0, 0, 1),
(29, 'fadhilmanfa', '2025-01-25 15:08:17', 0.4, 0, 0, 0, 1),
(30, 'fadhilmanfa', '2025-01-26 04:31:36', 0.4, 0, 0, 0, 1),
(31, 'fadhilmanfa', '2025-01-26 10:58:33', 0.4, 0, 0, 0, 1),
(32, 'fadhilmanfa', '2025-01-27 15:08:50', 0.4, 0, 0, 0, 1),
(33, 'fadhilmanfa', '2025-01-28 17:12:03', 0.3, 0, 0, 0, 1),
(34, 'fadhilmanfa', '2025-01-30 06:29:18', 0.3, 0, 0, 0, 1),
(35, 'fadhilmanfa', '2025-01-30 06:29:58', 0.3, 0, 0, 0, 1),
(36, 'fadhilmanfa', '2025-01-30 06:35:32', 0.3, 0, 0, 0, 1),
(37, 'fadhilmanfa', '2025-01-30 06:35:41', 0.3, 0, 0, 0, 1),
(38, 'fadhilmanfa', '2025-01-30 06:37:09', 0.3, 0, 0, 0, 1),
(39, 'fadhilmanfa', '2025-01-30 06:38:26', 0.3, 0, 0, 0, 1),
(40, 'fadhilmanfa', '2025-01-30 06:39:11', 0.3, 0, 0, 0, 1),
(41, 'fadhilmanfa', '2025-01-30 06:39:52', 0.3, 0, 0, 0, 1),
(42, 'fadhilmanfa', '2025-01-30 06:40:58', 0.3, 0, 0, 0, 1),
(43, 'fadhilmanfa', '2025-01-30 06:41:11', 0.3, 0, 0, 0, 1),
(44, 'fadhilmanfa', '2025-01-30 06:43:23', 0.3, 0, 0, 0, 1),
(45, 'fadhilmanfa', '2025-01-30 06:45:48', 0.3, 0, 0, 0, 1),
(46, 'fadhilmanfa', '2025-01-30 06:45:53', 0.3, 0, 0, 0, 1),
(47, 'fadhilmanfa', '2025-01-30 06:46:25', 0.3, 0, 0, 0, 1),
(48, 'fadhilmanfa', '2025-01-30 06:48:06', 0.3, 0, 0, 0, 1),
(49, 'fadhilmanfa', '2025-01-30 06:48:21', 0.3, 0, 0, 0, 1),
(50, 'fadhilmanfa', '2025-01-30 10:42:30', 0.3, 0, 0, 0, 1),
(51, 'fadhilmanfa', '2025-01-30 10:43:16', 0.3, 0, 0, 0, 1),
(52, 'fadhilmanfa', '2025-01-30 10:44:03', 0.3, 0, 0, 0, 1),
(53, 'fadhilmanfa', '2025-01-30 11:35:51', 0.1, 0, 0, 0, 1),
(54, 'fadhilmanfa', '2025-01-30 13:22:09', 0, 0, 0, 0, 1),
(55, 'fadhilmanfa', '2025-01-30 13:23:13', 0, 0, 0, 0, 1),
(56, 'fadhilmanfa', '2025-01-30 13:23:28', 0, 0, 0, 0, 1),
(57, 'fadhilmanfa', '2025-01-30 13:25:34', 0, 0, 0, 0, 1),
(58, 'fadhilmanfa', '2025-01-30 13:26:33', 0, 0, 0, 0, 1),
(59, 'fadhilmanfa', '2025-01-30 13:28:18', 0, 0, 0, 0, 1),
(60, 'fadhilmanfa', '2025-01-30 13:28:24', 0, 0, 0, 0, 1),
(61, 'fadhilmanfa', '2025-01-30 13:28:26', 0, 0, 0, 0, 1),
(62, 'fadhilmanfa', '2025-01-30 13:28:34', 0, 0, 0, 0, 1),
(63, 'fadhilmanfa', '2025-01-30 13:28:38', 0, 0, 0, 0, 1),
(64, 'fadhilmanfa', '2025-01-30 13:32:25', 0, 0, 0, 0, 1),
(65, 'fadhilmanfa', '2025-01-30 13:32:28', 0, 0, 0, 0, 1),
(66, 'fadhilmanfa', '2025-01-30 13:33:46', 0, 0, 0, 0, 1),
(67, 'fadhilmanfa', '2025-01-30 13:33:58', 0, 0, 0, 0, 1),
(68, 'fadhilmanfa', '2025-01-30 13:40:02', 0, 0, 0, 0, 1),
(69, 'fadhilmanfa', '2025-01-30 13:40:08', 0, 0, 0, 0, 1),
(70, 'fadhilmanfa', '2025-01-30 13:40:09', 0, 0, 0, 0, 1),
(71, 'fadhilmanfa', '2025-01-30 13:41:42', 0, 0, 0, 0, 1),
(72, 'fadhilmanfa', '2025-01-30 13:41:50', 0, 0, 0, 0, 1),
(73, 'fadhilmanfa', '2025-01-30 13:41:53', 0, 0, 0, 0, 1),
(74, 'fadhilmanfa', '2025-01-30 13:42:18', 0, 0, 0, 0, 1),
(75, 'fadhilmanfa', '2025-01-30 13:44:55', 0, 0, 0, 0, 1),
(76, 'fadhilmanfa', '2025-01-30 14:23:39', 0, 0, 0, 0, 1),
(77, 'fadhilmanfa', '2025-01-30 14:25:40', 0, 0, 0, 0, 1),
(78, 'fadhilmanfa', '2025-01-30 15:05:01', 0, 0, 0, 0, 1),
(79, 'fadhilmanfa', '2025-01-30 15:08:05', 0, 0, 0, 0, 1),
(80, 'fadhilmanfa', '2025-01-30 18:44:03', 0, 0, 0, 0, 1),
(81, 'fadhilmanfa', '2025-01-30 18:46:19', 0, 0, 0, 0, 1),
(82, 'fadhilmanfa', '2025-01-30 18:50:15', 0, 0, 0, 0, 1),
(83, 'fadhilmanfa', '2025-01-31 03:22:57', 0.1, 0, 0, 0, 1),
(84, 'fadhilmanfa', '2025-01-31 03:24:44', 0.1, 0, 0, 0, 1),
(85, 'fadhilmanfa', '2025-01-31 07:05:44', 0.1, 0.6, 0, 0, 1),
(86, 'fadhilmanfa', '2025-02-01 04:05:45', 0.4, 0, 0, 0, 1),
(87, 'fadhilmanfa', '2025-02-01 11:42:21', 0.4, 0, 0, 0, 1),
(88, 'fadhilmanfa', '2025-02-03 02:11:32', 0.4, 0, 0, 0, 1),
(89, 'fadhilmanfa', '2025-02-03 08:01:20', 0.4, 0, 0, 0, 1),
(90, 'fadhilmanfa', '2025-02-03 09:01:50', 0.4, 0.5, 0, 0, 1),
(91, 'fadhilmanfa', '2025-02-03 17:40:14', 0.6, 1, 0, 0, 1),
(92, 'fadhilmanfa', '2025-02-04 04:20:14', 0.6, 1, 0, 0, 1),
(93, 'fadhilmanfa', '2025-02-05 13:35:05', 0.6, 1, 0, 0, 1),
(94, 'fadhilmanfa', '2025-02-11 04:25:00', 0, 0, 0, 0, 0),
(95, 'fadhilmanfa', '2025-02-11 09:05:16', 0, 0, 0, 0, 0),
(96, 'fadhilmanfa', '2025-02-11 09:05:17', 0, 0, 0, 0, 0),
(97, 'fadhilmanfa', '2025-02-11 09:05:30', 0, 0, 0, 0, 0),
(98, 'fadhilmanfa', '2025-02-11 09:07:21', 0, 0, 0, 0, 0),
(99, 'fadhilmanfa', '2025-02-11 12:20:22', 0, 0, 0, 0, 0),
(100, 'fadhilmanfa', '2025-02-13 12:02:37', 0, 0, 0, 0, 0),
(101, 'fadhilmanfa', '2025-02-13 12:06:56', 0, 0, 0, 0, 0),
(102, 'fadhilmanfa', '2025-02-14 01:00:28', 0, 0, 0, 0, 0),
(103, 'fadhilmanfa', '2025-02-14 01:04:46', 0, 0, 0, 0, 0),
(104, 'fadhilmanfa', '2025-02-14 01:07:23', 0, 0, 0, 0, 0),
(105, 'fadhilmanfa', '2025-02-14 01:07:30', 0, 0, 0, 0, 0),
(106, 'fadhilmanfa', '2025-02-14 01:07:31', 0, 0, 0, 0, 0),
(107, 'fadhilmanfa', '2025-02-14 01:07:39', 0, 0, 0, 0, 0),
(108, 'fadhilmanfa', '2025-02-15 02:09:15', 0, 0, 0, 0, 0),
(109, 'fadhilmanfa', '2025-02-15 02:50:16', 0, 0, 0, 0, 0),
(110, 'fadhilmanfa', '2025-02-15 02:52:12', 0, 0, 0, 0, 0),
(111, 'fadhilmanfa', '2025-02-15 02:52:42', 0, 0, 0, 0, 0),
(112, 'fadhilmanfa', '2025-02-15 03:05:10', 0, 0, 0, 0, 0),
(113, 'fadhilmanfa', '2025-02-15 03:09:01', 0, 0, 0, 0, 0),
(114, 'fadhilmanfa', '2025-02-15 04:47:40', 0, 0, 0, 0, 0),
(115, 'fadhilmanfa', '2025-02-15 11:47:17', 0, 0, 0, 0, 0),
(116, 'fadhilmanfa', '2025-02-16 02:31:47', 0, 0, 0, 0, 0),
(117, 'fadhilmanfa', '2025-02-16 04:52:40', 0, 0, 0, 0, 0),
(118, 'fadhilmanfa', '2025-02-16 04:55:10', 0, 0, 0, 0, 0),
(119, 'fadhilmanfa', '2025-02-16 04:56:39', 0, 0, 0, 0, 0),
(120, 'fadhilmanfa', '2025-02-16 04:57:48', 0, 0, 0, 0, 0),
(121, 'fadhilmanfa', '2025-02-16 04:58:03', 0, 0, 0, 0, 0),
(122, 'fadhilmanfa', '2025-02-16 04:59:32', 0, 0, 0, 0, 0),
(123, 'fadhilmanfa', '2025-02-16 04:59:45', 0, 0, 0, 0, 0),
(124, 'fadhilmanfa', '2025-02-16 05:00:44', 0, 0, 0, 0, 0),
(125, 'fadhilmanfa', '2025-02-16 05:01:09', 0, 0, 0, 0, 0),
(126, 'fadhilmanfa', '2025-02-16 05:01:44', 0, 0, 0, 0, 0),
(127, 'fadhilmanfa', '2025-02-16 05:03:45', 0, 0, 0, 0, 0),
(128, 'fadhilmanfa', '2025-02-16 05:04:04', 0, 0, 0, 0, 0),
(129, 'fadhilmanfa', '2025-02-16 05:04:11', 0, 0, 0, 0, 0),
(130, 'fadhilmanfa', '2025-02-16 05:05:24', 0, 0, 0, 0, 0),
(131, 'fadhilmanfa', '2025-02-16 05:05:41', 0, 0, 0, 0, 0),
(132, 'fadhilmanfa', '2025-02-16 05:06:36', 0, 0, 0, 0, 0),
(133, 'fadhilmanfa', '2025-02-16 05:06:54', 0, 0, 0, 0, 0),
(134, 'fadhilmanfa', '2025-02-16 05:07:19', 0, 0, 0, 0, 0),
(135, 'fadhilmanfa', '2025-02-16 05:09:46', 0, 0, 0, 0, 0),
(136, 'fadhilmanfa', '2025-02-16 05:10:15', 0, 0, 0, 0, 0),
(137, 'fadhilmanfa', '2025-02-16 05:11:19', 0, 0, 0, 0, 0),
(138, 'fadhilmanfa', '2025-02-16 05:11:37', 0, 0, 0, 0, 0),
(139, 'fadhilmanfa', '2025-02-16 05:14:00', 0, 0, 0, 0, 0),
(140, 'fadhilmanfa', '2025-02-16 05:14:25', 0, 0, 0, 0, 0),
(141, 'fadhilmanfa', '2025-02-16 05:14:32', 0, 0, 0, 0, 0),
(142, 'fadhilmanfa', '2025-02-16 05:14:49', 0, 0, 0, 0, 0),
(143, 'fadhilmanfa', '2025-02-16 05:15:08', 0, 0, 0, 0, 0),
(144, 'fadhilmanfa', '2025-02-16 05:15:13', 0, 0, 0, 0, 0),
(145, 'fadhilmanfa', '2025-02-16 05:15:14', 0, 0, 0, 0, 0),
(146, 'fadhilmanfa', '2025-02-16 05:15:16', 0, 0, 0, 0, 0),
(147, 'fadhilmanfa', '2025-02-16 05:15:17', 0, 0, 0, 0, 0),
(148, 'fadhilmanfa', '2025-02-16 05:15:18', 0, 0, 0, 0, 0),
(150, 'fadhilmanfa', '2025-02-16 05:15:19', 0, 0, 0, 0, 0),
(151, 'fadhilmanfa', '2025-02-16 05:15:20', 0, 0, 0, 0, 0),
(152, 'fadhilmanfa', '2025-02-16 05:15:21', 0, 0, 0, 0, 0),
(153, 'fadhilmanfa', '2025-02-16 05:15:29', 0, 0, 0, 0, 0),
(154, 'fadhilmanfa', '2025-02-16 05:15:36', 0, 0, 0, 0, 0),
(155, 'fadhilmanfa', '2025-02-16 05:15:44', 0, 0, 0, 0, 0),
(156, 'fadhilmanfa', '2025-02-16 05:15:45', 0, 0, 0, 0, 0),
(157, 'fadhilmanfa', '2025-02-16 05:15:46', 0, 0, 0, 0, 0),
(158, 'fadhilmanfa', '2025-02-16 05:15:48', 0, 0, 0, 0, 0),
(159, 'fadhilmanfa', '2025-02-16 05:16:03', 0, 0, 0, 0, 0),
(160, 'fadhilmanfa', '2025-02-16 05:16:06', 0, 0, 0, 0, 0),
(161, 'fadhilmanfa', '2025-02-16 05:16:37', 0, 0, 0, 0, 0),
(162, 'fadhilmanfa', '2025-02-16 05:16:40', 0, 0, 0, 0, 0),
(163, 'fadhilmanfa', '2025-02-16 05:16:43', 0, 0, 0, 0, 0),
(164, 'fadhilmanfa', '2025-02-16 05:16:51', 0, 0, 0, 0, 0),
(165, 'fadhilmanfa', '2025-02-16 05:18:33', 0, 0, 0, 0, 0),
(166, 'fadhilmanfa', '2025-02-16 05:18:37', 0, 0, 0, 0, 0),
(167, 'fadhilmanfa', '2025-02-16 05:27:00', 0, 0, 0, 0, 0),
(168, 'fadhilmanfa', '2025-02-16 05:27:04', 0, 0, 0, 0, 0),
(169, 'fadhilmanfa', '2025-02-16 06:36:41', 0, 0, 0, 0, 0),
(170, 'fadhilmanfa', '2025-02-16 06:36:45', 0, 0, 0, 0, 0),
(171, 'fadhilmanfa', '2025-02-16 06:36:47', 0, 0, 0, 0, 0),
(172, 'fadhilmanfa', '2025-02-16 06:36:49', 0, 0, 0, 0, 0),
(173, 'fadhilmanfa', '2025-02-16 06:37:13', 0, 0, 0, 0, 0),
(174, 'fadhilmanfa', '2025-02-16 06:39:57', 0, 0, 0, 0, 0),
(175, 'fadhilmanfa', '2025-02-16 07:03:19', 0, 0, 0, 0, 0),
(176, 'fadhilmanfa', '2025-02-16 07:33:24', 0, 0, 0, 0, 0),
(177, 'fadhilmanfa', '2025-02-16 07:33:30', 0, 0, 0, 0, 0),
(178, 'fadhilmanfa', '2025-02-16 07:36:06', 0, 0, 0, 0, 0),
(179, 'fadhilmanfa', '2025-02-16 07:36:08', 0, 0, 0, 0, 0),
(180, 'fadhilmanfa', '2025-02-16 07:37:07', 0, 0, 0, 0, 0),
(181, 'fadhilmanfa', '2025-02-16 07:37:43', 0, 0, 0, 0, 0),
(182, 'fadhilmanfa', '2025-02-16 07:37:45', 0, 0, 0, 0, 0),
(183, 'fadhilmanfa', '2025-02-16 07:37:47', 0, 0, 0, 0, 0),
(184, 'fadhilmanfa', '2025-02-16 07:37:48', 0, 0, 0, 0, 0),
(185, 'fadhilmanfa', '2025-02-16 07:37:50', 0, 0, 0, 0, 0),
(186, 'fadhilmanfa', '2025-02-16 07:37:52', 0, 0, 0, 0, 0),
(187, 'fadhilmanfa', '2025-02-16 07:37:56', 0, 0, 0, 0, 0),
(188, 'fadhilmanfa', '2025-02-16 08:09:17', 0, 0, 0, 0, 0),
(189, 'fadhilmanfa', '2025-02-16 08:13:49', 0, 0, 0, 0, 0),
(190, 'fadhilmanfa', '2025-02-16 08:14:56', 0, 0, 0, 0, 0),
(191, 'fadhilmanfa', '2025-02-16 08:14:59', 0, 0, 0, 0, 0),
(192, 'fadhilmanfa', '2025-02-16 08:15:02', 0, 0, 0, 0, 0),
(193, 'fadhilmanfa', '2025-02-16 08:15:53', 0, 0, 0, 0, 0),
(194, 'fadhilmanfa', '2025-02-16 08:15:55', 0, 0, 0, 0, 0),
(195, 'fadhilmanfa', '2025-02-16 08:17:36', 0, 0, 0, 0, 0),
(196, 'fadhilmanfa', '2025-02-16 08:17:38', 0, 0, 0, 0, 0),
(197, 'fadhilmanfa', '2025-02-16 08:17:46', 0, 0, 0, 0, 0),
(198, 'fadhilmanfa', '2025-02-16 08:17:48', 0, 0, 0, 0, 0),
(199, 'fadhilmanfa', '2025-02-16 08:18:40', 0, 0, 0, 0, 0),
(200, 'fadhilmanfa', '2025-02-16 08:19:35', 0, 0, 0, 0, 0),
(201, 'fadhilmanfa', '2025-02-16 08:26:27', 0, 0, 0, 0, 0),
(202, 'fadhilmanfa', '2025-02-16 08:26:46', 0, 0, 0, 0, 0),
(203, 'fadhilmanfa', '2025-02-16 08:26:48', 0, 0, 0, 0, 0),
(204, 'fadhilmanfa', '2025-02-16 08:26:51', 0, 0, 0, 0, 0),
(205, 'fadhilmanfa', '2025-02-16 08:30:14', 0, 0, 0, 0, 0),
(206, 'fadhilmanfa', '2025-02-16 08:30:57', 0, 0, 0, 0, 0),
(207, 'joko', '2025-02-16 08:37:35', 0, 0, 0, 0, 0),
(208, 'joko', '2025-02-16 08:37:39', 0, 0, 0, 0, 0),
(209, 'joko', '2025-02-16 08:37:41', 0, 0, 0, 0, 0),
(210, 'fadhilmanfa', '2025-02-16 08:38:14', 0, 0, 0, 0, 0),
(211, 'fikofiko', '2025-02-16 08:38:38', 0, 0, 0, 0, 0),
(212, 'fikofiko', '2025-02-16 08:38:39', 0, 0, 0, 0, 0),
(213, 'fikofiko', '2025-02-16 08:38:41', 0, 0, 0, 0, 0),
(214, 'nia', '2025-02-16 08:39:14', 0, 0, 0, 0, 0),
(215, 'nia', '2025-02-16 08:39:45', 0, 0, 0, 0, 0),
(216, 'nia', '2025-02-16 08:39:50', 0, 0, 0, 0, 0),
(217, 'nia', '2025-02-16 08:40:03', 0, 0, 0, 0, 0),
(218, 'nia', '2025-02-16 08:40:06', 0, 0, 0, 0, 0),
(219, 'nia', '2025-02-16 08:40:14', 0, 0, 0, 0, 0),
(220, 'jayus', '2025-02-16 08:40:35', 0, 0, 0, 0, 0),
(221, 'jayus', '2025-02-16 08:40:38', 0, 0, 0, 0, 0),
(222, 'jayus', '2025-02-16 08:40:44', 0, 0, 0, 0, 0),
(223, 'jayus', '2025-02-16 08:40:48', 0, 0, 0, 0, 0),
(224, 'jayus', '2025-02-16 08:41:23', 0, 0, 0, 0, 0),
(225, 'jayus', '2025-02-16 08:41:26', 0, 0, 0, 0, 0),
(226, 'jayus', '2025-02-16 08:41:32', 0, 0, 0, 0, 0),
(227, 'jayus', '2025-02-16 08:41:38', 0, 0, 0, 0, 0),
(228, 'jayus', '2025-02-16 08:41:42', 0, 0, 0, 0, 0),
(229, 'jayus', '2025-02-16 08:41:46', 0, 0, 0, 0, 0),
(230, 'jayus', '2025-02-16 08:42:47', 0, 0, 0, 0, 0),
(231, 'jayus', '2025-02-16 08:42:49', 0, 0, 0, 0, 0),
(232, 'jayus', '2025-02-16 08:42:57', 0, 0, 0, 0, 0),
(233, 'jayus', '2025-02-16 08:42:58', 0, 0, 0, 0, 0),
(234, 'jayus', '2025-02-16 08:43:07', 0, 0, 0, 0, 0),
(235, 'jayus', '2025-02-16 08:45:02', 0, 0, 0, 0, 0),
(236, 'jayus', '2025-02-16 08:47:04', 0, 0, 0, 0, 0),
(237, 'jayus', '2025-02-16 08:47:11', 0, 0, 0, 0, 0),
(238, 'fadhilmanfa', '2025-02-16 08:47:25', 0, 0, 0, 0, 0),
(239, 'fadhilmanfa', '2025-02-16 08:47:31', 0, 0, 0, 0, 0),
(240, 'fadhilmanfa', '2025-02-16 08:47:34', 0, 0, 0, 0, 0),
(241, 'fadhilmanfa', '2025-02-16 08:50:03', 0, 0, 0, 0, 0),
(242, 'fadhilmanfa', '2025-02-16 08:50:09', 0, 0, 0, 0, 0),
(243, 'fadhilmanfa', '2025-02-16 08:50:40', 0, 0, 0, 0, 0),
(244, 'fadhilmanfa', '2025-02-16 08:50:43', 0, 0, 0, 0, 0),
(245, 'fadhilmanfa', '2025-02-16 08:50:46', 0, 0, 0, 0, 0),
(246, 'fadhilmanfa', '2025-02-16 08:50:47', 0, 0, 0, 0, 0),
(247, 'fadhilmanfa', '2025-02-16 08:51:16', 0, 0, 0, 0, 0),
(248, 'fadhilmanfa', '2025-02-16 08:51:18', 0, 0, 0, 0, 0),
(249, 'fadhilmanfa', '2025-02-16 08:51:21', 0, 0, 0, 0, 0),
(250, 'fadhilmanfa', '2025-02-16 08:51:26', 0, 0, 0, 0, 0),
(251, 'fadhilmanfa', '2025-02-16 08:51:31', 0, 0, 0, 0, 0),
(252, 'fadhilmanfa', '2025-02-16 08:51:45', 0, 0, 0, 0, 0),
(253, 'fadhilmanfa', '2025-02-16 08:52:19', 0, 0, 0, 0, 0),
(254, 'fadhilmanfa', '2025-02-16 08:52:22', 0, 0, 0, 0, 0),
(255, 'fadhilmanfa', '2025-02-16 08:52:27', 0, 0, 0, 0, 0),
(256, 'fadhilmanfa', '2025-02-16 08:52:54', 0, 0, 0, 0, 0),
(257, 'fadhilmanfa', '2025-02-16 08:52:56', 0, 0, 0, 0, 0),
(258, 'fadhilmanfa', '2025-02-16 08:53:05', 0, 0, 0, 0, 0),
(259, 'fadhilmanfa', '2025-02-16 08:53:07', 0, 0, 0, 0, 0),
(260, 'fadhilmanfa', '2025-02-16 08:53:24', 0, 0, 0, 0, 0),
(261, 'fadhilmanfa', '2025-02-16 08:53:57', 0, 0, 0, 0, 0),
(262, 'fadhilmanfa', '2025-02-16 08:54:00', 0, 0, 0, 0, 0),
(263, 'fadhilmanfa', '2025-02-16 08:54:05', 0, 0, 0, 0, 0),
(264, 'fadhilmanfa', '2025-02-16 08:54:53', 0, 0, 0, 0, 0),
(265, 'fadhilmanfa', '2025-02-16 08:54:57', 0, 0, 0, 0, 0),
(266, 'fadhilmanfa', '2025-02-16 08:55:05', 0, 0, 0, 0, 0),
(267, 'fadhilmanfa', '2025-02-16 08:55:56', 0, 0, 0, 0, 0),
(268, 'fadhilmanfa', '2025-02-16 08:55:58', 0, 0, 0, 0, 0),
(269, 'fadhilmanfa', '2025-02-16 08:56:01', 0, 0, 0, 0, 0),
(270, 'fadhilmanfa', '2025-02-16 08:56:04', 0, 0, 0, 0, 0),
(271, 'fadhilmanfa', '2025-02-16 08:59:30', 0, 0, 0, 0, 0),
(272, 'fadhilmanfa', '2025-02-16 08:59:34', 0, 0, 0, 0, 0),
(273, 'fadhilmanfa', '2025-02-16 09:18:11', 0, 0, 0, 0, 0),
(274, 'fadhilmanfa', '2025-02-16 11:37:54', 0, 0, 0, 0.2, 0.1),
(275, 'fadhilmanfa', '2025-02-16 11:37:56', 0, 0, 0, 0.2, 0.1),
(276, 'fadhilmanfa', '2025-02-16 11:37:59', 0, 0, 0, 0.2, 0.1),
(277, 'fadhilmanfa', '2025-02-16 11:38:45', 0, 0, 0, 0.2, 0.1),
(278, 'fadhilmanfa', '2025-02-16 11:38:48', 0, 0, 0, 0.2, 0.1),
(279, 'fadhilmanfa', '2025-02-16 11:39:59', 0, 0, 0, 0.2, 0.1),
(280, 'fadhilmanfa', '2025-02-16 11:40:32', 0, 0, 0, 0.2, 0.1),
(281, 'fadhilmanfa', '2025-02-16 11:45:00', 0, 0, 0, 0.2, 0.1),
(282, 'fadhilmanfa', '2025-02-16 11:47:04', 0, 0, 0, 0.2, 0.1),
(283, 'fadhilmanfa', '2025-02-16 11:50:01', 0, 0, 0, 0.2, 0.1),
(284, 'fadhilmanfa', '2025-02-16 11:55:55', 0, 0, 0, 0.2, 0.1),
(285, 'fadhilmanfa', '2025-02-16 11:55:59', 0, 0, 0, 0.2, 0.1),
(286, 'fadhilmanfa', '2025-02-16 11:56:46', 0, 0, 0, 0.2, 0.1),
(287, 'fadhilmanfa', '2025-02-16 12:09:14', 0, 0, 0, 0.2, 0.1),
(288, 'fadhilmanfa', '2025-02-16 12:31:25', 0, 0, 0, 0.2, 0.1),
(289, 'fadhilmanfa', '2025-02-16 12:31:35', 0, 0, 0, 0.2, 0.1),
(290, 'fadhilmanfa', '2025-02-16 12:56:10', 0, 0, 0, 0.2, 0.1),
(291, 'fadhilmanfa', '2025-02-16 15:35:55', 0, 0, 0, 0.2, 0.1),
(292, 'fadhilmanfa', '2025-02-16 16:18:04', 0, 0, 0, 0.2, 0.1),
(293, 'fadhilmanfa', '2025-02-16 16:21:04', 0, 0, 0, 0.2, 0.1),
(294, 'fadhilmanfa', '2025-02-16 16:21:28', 0, 0, 0, 0.2, 0.1),
(295, 'fadhilmanfa', '2025-02-16 16:21:29', 0, 0, 0, 0.2, 0.1),
(296, 'fadhilmanfa', '2025-02-16 16:21:49', 0, 0, 0, 0.2, 0.1),
(297, 'fadhilmanfa', '2025-02-16 16:22:51', 0, 0, 0, 0.2, 0.1),
(298, 'fadhilmanfa', '2025-02-16 16:33:00', 0, 0, 0, 0.2, 0.1),
(299, 'fadhilmanfa', '2025-02-16 16:36:54', 0, 0, 0, 0.2, 0.1),
(300, 'fadhilmanfa', '2025-02-16 16:37:50', 0, 0, 0, 0.2, 0.1),
(301, 'fadhilmanfa', '2025-02-16 16:37:58', 0, 0, 0, 0.2, 0.1),
(302, 'fadhilmanfa', '2025-02-16 16:38:35', 0, 0, 0, 0.2, 0.1),
(303, 'fadhilmanfa', '2025-02-16 16:50:09', 0, 0, 0, 0.2, 0.1),
(304, 'fadhilmanfa', '2025-02-16 16:50:26', 0, 0, 0, 0.2, 0.1),
(305, 'fadhilmanfa', '2025-02-16 16:53:57', 0, 0.1, 0, 0.3, 0.2),
(306, 'fadhilmanfa', '2025-02-16 16:54:04', 0, 0.1, 0, 0.3, 0.2),
(307, 'fadhilmanfa', '2025-02-16 16:54:57', 0, 0.1, 0, 0.3, 0.2),
(308, 'fadhilmanfa', '2025-02-16 16:55:59', 0, 0.1, 0, 0.3, 0.3),
(309, 'fadhilmanfa', '2025-02-16 17:01:13', 0, 0.1, 0, 0.3, 0.3),
(310, 'fadhilmanfa', '2025-02-17 10:55:24', 0, 0.1, 0, 0.3, 0.9),
(311, 'fadhilmanfa', '2025-02-17 10:56:39', 0, 0.1, 0, 0.3, 0.9),
(312, 'fadhilmanfa', '2025-02-17 10:57:56', 0, 0.1, 0, 0.3, 0.9),
(313, 'fadhilmanfa', '2025-02-17 10:57:59', 0, 0.1, 0, 0.3, 0.9),
(314, 'fadhilmanfa', '2025-02-17 10:58:13', 0, 0.1, 0, 0.3, 0.9),
(315, 'fadhilmanfa', '2025-02-17 10:58:17', 0, 0.1, 0, 0.3, 0.9),
(316, 'fadhilmanfa', '2025-02-17 10:59:05', 0, 0.1, 0, 0.3, 0.9),
(317, 'fadhilmanfa', '2025-02-18 00:13:27', 0, 0.1, 0, 0.3, 0.9),
(318, 'fadhilmanfa', '2025-02-18 00:14:35', 0, 0.1, 0, 0.3, 0.9),
(319, 'fadhilmanfa', '2025-02-18 00:28:53', 0, 0.1, 0, 0.3, 0.9),
(320, 'fadhilmanfa', '2025-02-18 00:29:03', 0, 0.1, 0, 0.3, 0.9),
(321, 'fadhilmanfa', '2025-02-18 00:30:40', 0, 0.1, 0, 0.3, 0.9),
(322, 'fadhilmanfa', '2025-02-18 00:30:55', 0, 0.1, 0, 0.3, 0.9),
(323, 'fadhilmanfa', '2025-02-18 00:30:57', 0, 0.1, 0, 0.3, 0.9),
(324, 'fadhilmanfa', '2025-02-18 00:31:04', 0, 0.1, 0, 0.3, 0.9),
(325, 'fadhilmanfa', '2025-02-18 00:34:26', 0, 0.1, 0, 0.3, 0.9),
(326, 'fadhilmanfa', '2025-02-18 01:57:18', 0, 0.1, 0, 0.3, 1),
(327, 'fadhilmanfa', '2025-02-18 02:13:31', 0, 0.1, 0, 0.3, 1),
(328, 'fadhilmanfa', '2025-02-18 21:59:45', 0, 0.1, 0, 0.3, 1),
(329, 'fadhilmanfa', '2025-02-19 14:09:54', 0, 0, 0, 0, 0),
(330, 'fadhilmanfa', '2025-02-19 14:09:56', 0, 0, 0, 0, 0),
(331, 'fadhilmanfa', '2025-02-19 14:10:08', 0, 0, 0, 0, 0),
(332, 'fadhilmanfa', '2025-02-19 14:10:24', 0, 0, 0, 0, 0),
(333, 'fadhilmanfa', '2025-02-19 14:10:25', 0, 0, 0, 0, 0),
(334, 'fadhilmanfa', '2025-02-19 14:11:52', 0, 0, 0, 0, 0),
(335, 'fadhilmanfa', '2025-02-19 14:13:13', 0, 0, 0, 0, 0),
(336, 'fadhilmanfa', '2025-02-19 14:13:16', 0, 0, 0, 0, 0),
(337, 'fadhilmanfa', '2025-02-19 14:13:42', 0, 0, 0, 0, 0),
(338, 'fadhilmanfa', '2025-02-19 14:14:39', 0, 0, 0, 0, 0),
(339, 'fadhilmanfa', '2025-02-19 14:15:05', 0, 0, 0, 0, 0),
(340, 'fadhilmanfa', '2025-02-19 14:15:23', 0, 0, 0, 0, 0),
(341, 'fadhilmanfa', '2025-02-19 14:15:26', 0, 0, 0, 0, 0),
(342, 'fadhilmanfa', '2025-02-19 14:16:23', 0, 0, 0, 0, 0),
(343, 'fadhilmanfa', '2025-02-19 17:22:45', 0, 0, 0, 0, 0),
(344, 'fadhilmanfa', '2025-02-19 17:22:51', 0, 0, 0, 0, 0),
(345, 'fadhilmanfa', '2025-02-19 17:22:55', 0, 0, 0, 0, 0),
(346, 'fadhilmanfa', '2025-02-19 17:31:55', 0, 0, 0, 0, 0.1),
(347, 'fadhilmanfa', '2025-02-19 17:38:28', 0, 0, 0, 0, 0),
(348, 'fadhilmanfa', '2025-02-20 11:36:55', 0, 0, 0, 0, 0.3),
(349, 'fadhilmanfa', '2025-02-20 11:39:01', 0, 0, 0, 0, 0.3),
(350, 'fadhilmanfa', '2025-02-20 11:55:18', 0, 0, 0, 0, 0.3),
(351, 'fadhilmanfa', '2025-02-20 11:55:24', 0, 0, 0, 0, 0.3),
(352, 'fadhilmanfa', '2025-02-20 11:55:59', 0, 0, 0, 0, 0.3),
(353, 'fadhilmanfa', '2025-02-21 08:58:17', 0, 0, 0, 0, 0.3),
(354, 'fadhilmanfa', '2025-02-26 00:25:24', 0, 0, 0, 0, 0.3),
(355, 'fadhilmanfa', '2025-02-26 03:02:48', 0, 0, 0, 0, 0.3),
(356, 'fadhilmanfa', '2025-02-26 03:03:35', 0, 0, 0, 0, 0.3),
(357, 'fadhilmanfa', '2025-02-26 03:03:45', 0, 0, 0, 0, 0.3),
(358, 'fadhilmanfa', '2025-02-26 03:04:02', 0, 0, 0, 0, 0.3),
(359, 'fadhilmanfa', '2025-02-27 07:46:25', 0, 0, 0, 0, 0.3),
(360, 'fadhilmanfa', '2025-02-27 07:47:48', 0, 0, 0, 0, 0.3),
(361, 'fadhilmanfa', '2025-02-27 07:47:53', 0, 0, 0, 0, 0.3),
(362, 'fadhilmanfa', '2025-02-27 07:47:54', 0, 0, 0, 0, 0.3),
(363, 'fadhilmanfa', '2025-02-27 07:49:58', 0, 0, 0, 0, 0.3),
(364, 'fadhilmanfa', '2025-03-02 13:44:34', 0, 0, 0, 0, 0.3),
(365, 'fadhilmanfa', '2025-03-02 15:42:04', 0, 0, 0, 0, 0.3),
(366, 'fadhilmanfa', '2025-03-11 14:01:08', 0, 0, 0, 0, 0.3),
(367, 'fadhilmanfa', '2025-03-12 04:04:42', 0, 0, 0, 0, 0.3),
(368, 'fadhilmanfa', '2025-03-12 04:04:45', 0, 0, 0, 0, 0.3),
(369, 'fadhilmanfa', '2025-03-12 04:05:01', 0, 0, 0, 0, 0.3),
(370, 'fadhilmanfa', '2025-03-12 04:25:53', 0, 0, 0, 0, 0.3),
(371, 'fadhilmanfa', '2025-03-12 04:26:22', 0, 0, 0, 0, 0.3),
(372, 'fadhilmanfa', '2025-03-12 04:44:38', 0, 0, 0, 0, 0.3),
(373, 'fadhilmanfa', '2025-03-12 05:14:27', 0, 0, 0, 0, 0.3),
(374, 'fadhilmanfa', '2025-03-12 07:51:56', 0, 0, 0, 0, 0.5),
(375, 'fadhilmanfa', '2025-03-12 09:23:25', 0, 0, 0, 0, 0.5),
(376, 'fadhilmanfa', '2025-03-13 01:25:57', 0, 0, 0, 0, 0.5),
(377, 'fadhilmanfa', '2025-03-13 01:26:10', 0, 0, 0, 0, 0.5),
(378, 'fadhilmanfa', '2025-03-13 01:59:37', 0, 0, 0, 0, 0.5),
(379, 'fadhilmanfa', '2025-03-13 04:38:26', 0, 0, 0, 0, 0.5),
(380, 'fadhilmanfa', '2025-03-13 04:58:07', 0, 0, 0, 0, 0.9),
(381, 'fadhilmanfa', '2025-03-13 05:15:55', 0, 0, 0, 0, 0.1),
(382, 'fadhilmanfa', '2025-03-13 06:12:19', 0, 0, 0, 0, 0.5),
(383, 'fadhilmanfa', '2025-03-13 06:12:20', 0, 0, 0, 0, 0.5),
(384, 'fadhilmanfa', '2025-03-13 06:27:57', 0, 0, 0, 0, 0.5),
(385, 'fadhilmanfa', '2025-03-13 06:28:06', 0, 0, 0, 0, 0.5),
(386, 'fadhilmanfa', '2025-03-13 06:59:58', 0, 0, 0, 0, 0.5),
(387, 'fadhilmanfa', '2025-03-13 07:04:07', 0, 0, 0, 0, 0.5),
(388, 'fadhilmanfa', '2025-03-13 09:26:01', 0, 0, 0, 0, 0.5),
(389, 'fadhilmanfa', '2025-03-13 10:45:14', 0, 0, 0, 0, 0.5),
(390, 'fadhilmanfa', '2025-03-13 11:21:17', 0, 0, 0, 0, 0.5),
(391, 'fadhilmanfa', '2025-03-13 12:15:00', 0, 0, 0, 0, 0),
(392, 'fadhilmanfa', '2025-03-14 03:15:47', 0, 0, 0, 0, 0),
(393, 'fadhilmanfa', '2025-03-14 03:16:03', 0, 0, 0, 0, 0),
(394, 'fadhilmanfa', '2025-03-14 06:40:45', 0, 0, 0, 0, 0),
(395, 'fadhilmanfa', '2025-03-14 06:41:00', 0, 0, 0, 0, 0),
(396, 'fadhilmanfa', '2025-03-14 06:41:05', 0, 0, 0, 0, 0),
(397, 'fadhilmanfa', '2025-03-14 06:41:15', 0, 0, 0, 0, 0),
(398, 'fadhilmanfa', '2025-03-14 06:58:26', 0, 0, 0, 0, 0),
(399, 'fadhilmanfa', '2025-03-14 07:00:42', 0, 0, 0, 0, 0),
(400, 'fadhilmanfa', '2025-03-14 07:09:24', 0, 0, 0, 0, 0),
(401, 'fadhilmanfa', '2025-03-15 05:01:17', 0, 0, 0, 0, 0),
(402, 'fadhilmanfa', '2025-03-16 09:00:57', 0, 0, 0, 0, 0),
(403, 'fadhilmanfa', '2025-03-16 09:01:03', 0, 0, 0, 0, 0),
(404, 'fadhilmanfa', '2025-03-16 09:17:45', 0, 0, 0, 0, 0),
(405, 'fadhilmanfa', '2025-03-16 09:30:34', 0, 0, 0, 0, 0.4),
(406, 'fadhilmanfa', '2025-03-16 09:31:02', 0, 0, 0, 0, 0.4),
(407, 'fadhilmanfa', '2025-03-16 11:10:22', 0, 0, 0, 0, 0.4),
(408, 'fadhilmanfa', '2025-03-16 11:12:01', 0, 0, 0, 0, 0.4),
(409, 'fadhilmanfa', '2025-03-16 11:12:58', 0, 0, 0, 0, 0.4),
(410, 'fadhilmanfa', '2025-03-16 11:13:57', 0, 0, 0, 0, 0.4),
(411, 'fadhilmanfa', '2025-03-16 11:17:45', 0, 0, 0, 0, 0.4),
(412, 'fadhilmanfa', '2025-03-16 11:19:24', 0, 0, 0, 0, 0.4),
(413, 'fadhilmanfa', '2025-03-16 11:19:34', 0, 0, 0, 0, 0.4),
(414, 'fadhilmanfa', '2025-03-16 11:22:43', 0, 0, 0, 0, 0.4),
(415, 'fadhilmanfa', '2025-03-16 11:22:52', 0, 0, 0, 0, 0.4),
(416, 'fadhilmanfa', '2025-03-16 11:23:58', 0, 0, 0, 0, 0.4),
(418, 'fadhilmanfa', '2025-03-16 11:23:59', 0, 0, 0, 0, 0.4),
(419, 'fadhilmanfa', '2025-03-16 11:26:27', 0, 0, 0, 0, 0.4),
(420, 'fadhilmanfa', '2025-03-16 11:26:34', 0, 0, 0, 0, 0.4),
(421, 'fadhilmanfa', '2025-03-16 11:28:20', 0, 0, 0, 0, 0.4),
(422, 'fadhilmanfa', '2025-03-16 11:30:04', 0, 0, 0, 0, 0.4),
(423, 'fadhilmanfa', '2025-03-16 11:35:19', 0, 0, 0, 0, 0.4),
(424, 'fadhilmanfa', '2025-03-16 11:36:07', 0, 0, 0, 0, 0.4),
(425, 'fadhilmanfa', '2025-03-16 11:48:57', 0, 0, 0, 0, 0.4),
(426, 'fadhilmanfa', '2025-03-16 11:49:49', 0, 0, 0, 0, 0.4),
(427, 'fadhilmanfa', '2025-03-16 11:50:53', 0, 0, 0, 0, 0.4),
(428, 'fadhilmanfa', '2025-03-16 12:10:02', 0, 0, 0, 0, 0.4),
(429, 'fadhilmanfa', '2025-03-16 13:25:39', 0, 0, 0, 0, 0.4),
(430, 'fadhilmanfa', '2025-03-16 13:26:38', 0, 0, 0, 0, 0.4),
(431, 'fadhilmanfa', '2025-03-16 13:27:35', 0, 0, 0, 0, 0.4),
(432, 'fadhilmanfa', '2025-03-16 13:29:21', 0, 0, 0, 0, 0.4),
(433, 'fadhilmanfa', '2025-03-16 14:41:52', 0, 0, 0, 0, 0.5),
(434, 'fadhilmanfa', '2025-03-16 14:42:09', 0, 0, 0, 0, 0.5),
(435, 'fadhilmanfa', '2025-03-16 14:42:14', 0, 0, 0, 0, 0.5),
(436, 'fadhilmanfa', '2025-03-16 14:42:16', 0, 0, 0, 0, 0.5),
(437, 'fadhilmanfa', '2025-03-16 14:42:17', 0, 0, 0, 0, 0.5),
(438, 'fadhilmanfa', '2025-03-17 02:39:57', 0, 0, 0, 0, 0.5),
(439, 'fadhilmanfa', '2025-03-17 02:41:56', 0, 0, 0, 0, 0.5);

-- --------------------------------------------------------

--
-- Table structure for table `user_topics`
--

CREATE TABLE `user_topics` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `topic` varchar(100) NOT NULL,
  `frequency` int(11) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_topics`
--

INSERT INTO `user_topics` (`id`, `user_id`, `topic`, `frequency`, `last_updated`) VALUES
(1, 'fadhilmanfa', 'halo', 1915, '2025-03-17 02:41:56'),
(3, 'fadhilmanfa', 'buatkan', 69, '2025-01-24 13:24:47'),
(4, 'fadhilmanfa', 'materi', 51, '2025-01-24 10:27:47'),
(5, 'fadhilmanfa', 'kultum', 14, '2025-01-24 00:24:41'),
(6, 'fadhilmanfa', 'ikhlas', 14, '2025-01-24 00:24:41'),
(7, 'fadhilmanfa', 'sebanyak', 14, '2025-01-24 00:24:41'),
(75, 'fadhilmanfa', 'berikan', 158, '2025-02-16 09:18:11'),
(76, 'fadhilmanfa', 'tips', 76, '2025-02-16 09:18:11'),
(77, 'fadhilmanfa', 'mengajar', 76, '2025-02-16 09:18:11'),
(80, 'fadhilmanfa', 'tolong', 9, '2025-01-25 15:08:17'),
(81, 'fadhilmanfa', 'rangkum', 1, '2025-01-24 08:05:14'),
(82, 'fadhilmanfa', 'jurnal', 1, '2025-01-24 08:05:14'),
(85, 'fadhilmanfa', 'haha,', 2, '2025-03-13 05:15:55'),
(86, 'fadhilmanfa', 'hanya', 1, '2025-01-24 10:27:47'),
(87, 'fadhilmanfa', 'ingin', 1, '2025-01-24 10:27:47'),
(88, 'fadhilmanfa', 'kamu', 685, '2025-03-17 02:41:56'),
(89, 'fadhilmanfa', 'user', 20, '2025-01-24 13:24:47'),
(91, 'fadhilmanfa', 'sampai', 3, '2025-01-24 10:40:22'),
(92, 'fadhilmanfa', 'tidak', 30, '2025-02-05 13:35:05'),
(95, 'fadhilmanfa', 'memahami', 8, '2025-01-24 11:18:27'),
(96, 'fadhilmanfa', 'kamu,', 12, '2025-01-24 13:24:47'),
(104, 'fadhilmanfa', 'ringkaskan', 7, '2025-01-24 13:24:47'),
(109, 'fadhilmanfa', 'dokumen', 233, '2025-02-03 09:01:50'),
(110, 'fadhilmanfa', 'jelaskan', 49, '2025-02-03 08:01:20'),
(111, 'fadhilmanfa', 'semua', 19, '2025-01-30 11:35:51'),
(112, 'fadhilmanfa', 'apa?', 29, '2025-03-13 11:21:17'),
(117, 'fadhilmanfa', 'hari', 5, '2025-01-25 13:30:02'),
(122, 'fadhilmanfa', 'coba', 113, '2025-02-03 08:01:20'),
(247, 'fadhilmanfa', 'siapa?', 145, '2025-03-13 11:21:17'),
(251, 'fadhilmanfa', 'haloo', 58, '2025-01-30 18:50:15'),
(256, 'fadhilmanfa', 'halo,', 81, '2025-02-13 12:06:56'),
(396, 'fadhilmanfa', 'merancang', 6, '2025-01-31 03:24:44'),
(397, 'fadhilmanfa', 'agar', 6, '2025-01-31 03:24:44'),
(404, 'fadhilmanfa', 'siswa', 46, '2025-02-05 13:35:05'),
(405, 'fadhilmanfa', 'bagaimana', 39, '2025-02-16 16:54:57'),
(407, 'fadhilmanfa', 'pembelajaran', 4, '2025-01-31 07:05:44'),
(429, 'fadhilmanfa', 'kelas', 7, '2025-02-03 09:01:50'),
(432, 'fadhilmanfa', 'tapi', 5, '2025-02-03 09:01:50'),
(433, 'fadhilmanfa', 'bisa', 138, '2025-03-17 02:41:56'),
(450, 'fadhilmanfa', 'selamat', 2, '2025-02-13 12:06:56'),
(451, 'fadhilmanfa', 'malam', 2, '2025-02-13 12:06:56'),
(459, 'fadhilmanfa', 'efektif', 75, '2025-02-16 09:18:11'),
(758, 'fadhilmanfa', 'saga', 360, '2025-03-17 02:41:56'),
(760, 'fadhilmanfa', 'masuk', 31, '2025-02-16 16:50:26'),
(761, 'fadhilmanfa', 'piket', 31, '2025-02-16 16:50:26'),
(914, 'fadhilmanfa', 'saran', 10, '2025-02-16 17:01:13'),
(916, 'fadhilmanfa', 'cara', 3, '2025-02-16 16:54:57'),
(930, 'fadhilmanfa', 'duluan', 2, '2025-02-16 17:01:13'),
(931, 'fadhilmanfa', 'telur', 17, '2025-03-13 04:58:07'),
(938, 'fadhilmanfa', 'indomi', 57, '2025-02-18 21:59:45'),
(940, 'fadhilmanfa', 'kuah?', 38, '2025-02-18 21:59:45'),
(941, 'fadhilmanfa', 'jawab', 36, '2025-02-18 02:13:31'),
(1030, 'fadhilmanfa', 'kabarmu?', 2, '2025-02-18 21:59:45'),
(1034, 'fadhilmanfa', 'kalo', 12, '2025-02-19 17:31:55'),
(1035, 'fadhilmanfa', 'belajar', 6, '2025-02-19 17:22:55'),
(1036, 'fadhilmanfa', 'gila', 6, '2025-02-19 17:22:55'),
(1050, 'fadhilmanfa', 'gimana', 2, '2025-02-19 17:31:55'),
(1051, 'fadhilmanfa', 'biar', 2, '2025-02-19 17:31:55'),
(1055, 'fadhilmanfa', 'bjirrr,', 24, '2025-03-12 04:26:22'),
(1056, 'fadhilmanfa', 'seris', 24, '2025-03-12 04:26:22'),
(1175, 'fadhilmanfa', 'beri', 2, '2025-03-12 05:14:27'),
(1176, 'fadhilmanfa', 'kejutan!', 2, '2025-03-12 05:14:27'),
(1186, 'fadhilmanfa', 'bebas', 2, '2025-03-12 09:23:25'),
(1196, 'fadhilmanfa', 'masalah', 4, '2025-03-13 04:38:26'),
(1216, 'fadhilmanfa', 'sama', 2, '2025-03-13 04:58:07'),
(1219, 'fadhilmanfa', 'sehat?', 1, '2025-03-13 05:15:55'),
(1225, 'fadhilmanfa', 'kerjaku', 9, '2025-03-13 11:21:17'),
(1282, 'fadhilmanfa', 'lebih', 105, '2025-03-17 02:41:56'),
(1283, 'fadhilmanfa', 'dulu', 84, '2025-03-16 13:29:21'),
(1284, 'fadhilmanfa', 'telur?', 84, '2025-03-16 13:29:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_chat_history`
--
ALTER TABLE `ai_chat_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`created_at`);

--
-- Indexes for table `bank_soal`
--
ALTER TABLE `bank_soal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ujian_id` (`ujian_id`);

--
-- Indexes for table `catatan_guru`
--
ALTER TABLE `catatan_guru`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indexes for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Indexes for table `emoji_reactions`
--
ALTER TABLE `emoji_reactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `postingan_id` (`postingan_id`);

--
-- Indexes for table `file_soal`
--
ALTER TABLE `file_soal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ujian_id` (`ujian_id`);

--
-- Indexes for table `groq_daily_usage`
--
ALTER TABLE `groq_daily_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `model_id` (`model_id`,`usage_date`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `jawaban_ujian`
--
ALTER TABLE `jawaban_ujian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ujian_id` (`ujian_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_kelas` (`kode_kelas`);

--
-- Indexes for table `kelas_siswa`
--
ALTER TABLE `kelas_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `komentar_postingan`
--
ALTER TABLE `komentar_postingan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `postingan_id` (`postingan_id`);

--
-- Indexes for table `komentar_replies`
--
ALTER TABLE `komentar_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `komentar_id` (`komentar_id`);

--
-- Indexes for table `lampiran_postingan`
--
ALTER TABLE `lampiran_postingan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_postingan` (`postingan_id`);

--
-- Indexes for table `lampiran_tugas`
--
ALTER TABLE `lampiran_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_id` (`tugas_id`);

--
-- Indexes for table `likes_postingan`
--
ALTER TABLE `likes_postingan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`postingan_id`,`user_id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_id` (`tugas_id`);

--
-- Indexes for table `pg`
--
ALTER TABLE `pg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_period` (`siswa_id`,`semester`,`tahun_ajaran`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `pilihan_jawaban`
--
ALTER TABLE `pilihan_jawaban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `soal_id` (`soal_id`);

--
-- Indexes for table `postingan_kelas`
--
ALTER TABLE `postingan_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kelas` (`kelas_id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_knowledge`
--
ALTER TABLE `project_knowledge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reaction` (`postingan_id`,`user_id`);

--
-- Indexes for table `saga_personality`
--
ALTER TABLE `saga_personality`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `soal_ujian`
--
ALTER TABLE `soal_ujian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ujian_id` (`ujian_id`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `postingan_id` (`postingan_id`);

--
-- Indexes for table `ujian`
--
ALTER TABLE `ujian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `user_character_analysis`
--
ALTER TABLE `user_character_analysis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_date` (`user_id`,`analysis_date`);

--
-- Indexes for table `user_topics`
--
ALTER TABLE `user_topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_topic` (`user_id`,`topic`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_chat_history`
--
ALTER TABLE `ai_chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=792;

--
-- AUTO_INCREMENT for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `bank_soal`
--
ALTER TABLE `bank_soal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=422;

--
-- AUTO_INCREMENT for table `catatan_guru`
--
ALTER TABLE `catatan_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `emoji_reactions`
--
ALTER TABLE `emoji_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `file_soal`
--
ALTER TABLE `file_soal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groq_daily_usage`
--
ALTER TABLE `groq_daily_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `jawaban_ujian`
--
ALTER TABLE `jawaban_ujian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `kelas_siswa`
--
ALTER TABLE `kelas_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=438;

--
-- AUTO_INCREMENT for table `komentar_postingan`
--
ALTER TABLE `komentar_postingan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `komentar_replies`
--
ALTER TABLE `komentar_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lampiran_postingan`
--
ALTER TABLE `lampiran_postingan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `lampiran_tugas`
--
ALTER TABLE `lampiran_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `likes_postingan`
--
ALTER TABLE `likes_postingan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pg`
--
ALTER TABLE `pg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `pilihan_jawaban`
--
ALTER TABLE `pilihan_jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `postingan_kelas`
--
ALTER TABLE `postingan_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_knowledge`
--
ALTER TABLE `project_knowledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saga_personality`
--
ALTER TABLE `saga_personality`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `soal_ujian`
--
ALTER TABLE `soal_ujian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ujian`
--
ALTER TABLE `ujian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `user_character_analysis`
--
ALTER TABLE `user_character_analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=440;

--
-- AUTO_INCREMENT for table `user_topics`
--
ALTER TABLE `user_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1456;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_chat_history`
--
ALTER TABLE `ai_chat_history`
  ADD CONSTRAINT `ai_chat_history_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `ai_chat_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bank_soal`
--
ALTER TABLE `bank_soal`
  ADD CONSTRAINT `bank_soal_ibfk_1` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`);

--
-- Constraints for table `catatan_guru`
--
ALTER TABLE `catatan_guru`
  ADD CONSTRAINT `fk_catatan_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_catatan_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD CONSTRAINT `comment_reactions_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `komentar_postingan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `emoji_reactions`
--
ALTER TABLE `emoji_reactions`
  ADD CONSTRAINT `emoji_reactions_ibfk_1` FOREIGN KEY (`postingan_id`) REFERENCES `postingan_kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_soal`
--
ALTER TABLE `file_soal`
  ADD CONSTRAINT `file_soal_ibfk_1` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jawaban_ujian`
--
ALTER TABLE `jawaban_ujian`
  ADD CONSTRAINT `jawaban_ujian_ibfk_1` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`),
  ADD CONSTRAINT `jawaban_ujian_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `kelas_siswa`
--
ALTER TABLE `kelas_siswa`
  ADD CONSTRAINT `kelas_siswa_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  ADD CONSTRAINT `kelas_siswa_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `komentar_postingan`
--
ALTER TABLE `komentar_postingan`
  ADD CONSTRAINT `komentar_postingan_ibfk_1` FOREIGN KEY (`postingan_id`) REFERENCES `postingan_kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `komentar_replies`
--
ALTER TABLE `komentar_replies`
  ADD CONSTRAINT `komentar_replies_ibfk_1` FOREIGN KEY (`komentar_id`) REFERENCES `komentar_postingan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lampiran_postingan`
--
ALTER TABLE `lampiran_postingan`
  ADD CONSTRAINT `lampiran_postingan_ibfk_1` FOREIGN KEY (`postingan_id`) REFERENCES `postingan_kelas` (`id`);

--
-- Constraints for table `lampiran_tugas`
--
ALTER TABLE `lampiran_tugas`
  ADD CONSTRAINT `lampiran_tugas_ibfk_1` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  ADD CONSTRAINT `pengumpulan_tugas_ibfk_1` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pg`
--
ALTER TABLE `pg`
  ADD CONSTRAINT `fk_statistik_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `pilihan_jawaban`
--
ALTER TABLE `pilihan_jawaban`
  ADD CONSTRAINT `fk_pilihan_soal` FOREIGN KEY (`soal_id`) REFERENCES `soal_ujian` (`id`);

--
-- Constraints for table `postingan_kelas`
--
ALTER TABLE `postingan_kelas`
  ADD CONSTRAINT `fk_postkelas_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`);

--
-- Constraints for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD CONSTRAINT `project_documents_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_knowledge`
--
ALTER TABLE `project_knowledge`
  ADD CONSTRAINT `project_knowledge_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `soal_ujian`
--
ALTER TABLE `soal_ujian`
  ADD CONSTRAINT `fk_soal_ujian` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`);

--
-- Constraints for table `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`postingan_id`) REFERENCES `postingan_kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ujian`
--
ALTER TABLE `ujian`
  ADD CONSTRAINT `fk_ujian_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`username`),
  ADD CONSTRAINT `fk_ujian_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`);

--
-- Constraints for table `user_character_analysis`
--
ALTER TABLE `user_character_analysis`
  ADD CONSTRAINT `user_character_analysis_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `guru` (`username`) ON DELETE CASCADE;

--
-- Constraints for table `user_topics`
--
ALTER TABLE `user_topics`
  ADD CONSTRAINT `user_topics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `guru` (`username`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
