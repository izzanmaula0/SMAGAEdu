-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 19, 2025 at 09:41 PM
-- Server version: 10.11.13-MariaDB-cll-lve
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smpp3485_smagaedu`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_messages`
--

CREATE TABLE `ai_chat_messages` (
  `id` int(11) NOT NULL,
  `ai_chat_sessions_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `respons` text NOT NULL,
  `created_at` datetime NOT NULL,
  `uses_canvas` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_sessions`
--

CREATE TABLE `ai_chat_sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `nama` varchar(1000) NOT NULL,
  `username` varchar(100) NOT NULL,
  `tahun_masuk` int(11) DEFAULT NULL,
  `tahun_lulus` int(11) DEFAULT NULL,
  `nis` int(11) DEFAULT NULL,
  `foto_profil` varchar(1000) DEFAULT NULL,
  `alamat` varchar(1000) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_soal`
--

CREATE TABLE `bank_soal` (
  `id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `jenis_soal` enum('pilihan_ganda','uraian') NOT NULL,
  `pertanyaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gambar_soal` varchar(255) DEFAULT NULL,
  `jawaban_a` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jawaban_b` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jawaban_c` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jawaban_d` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jawaban_benar` varchar(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description_id` int(11) DEFAULT NULL,
  `gambar_jawaban_a` varchar(255) DEFAULT NULL,
  `gambar_jawaban_b` varchar(255) DEFAULT NULL,
  `gambar_jawaban_c` varchar(255) DEFAULT NULL,
  `gambar_jawaban_d` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `canvas_versions`
--

CREATE TABLE `canvas_versions` (
  `id` int(11) NOT NULL,
  `ai_chat_sessions_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `file_pengumpulan_tugas`
--

CREATE TABLE `file_pengumpulan_tugas` (
  `id` int(11) NOT NULL,
  `pengumpulan_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `tipe_file` varchar(100) NOT NULL,
  `ukuran_file` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `hasil_ujian`
--

CREATE TABLE `hasil_ujian` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `jumlah_benar` int(11) DEFAULT 0,
  `jumlah_salah` int(11) DEFAULT 0,
  `tidak_dijawab` int(11) DEFAULT 0,
  `nilai` decimal(5,2) DEFAULT 0.00,
  `waktu_submit` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `informasi_sekolah`
--

CREATE TABLE `informasi_sekolah` (
  `id` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `konten` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Status aktif (1) atau nonaktif (0)'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jawaban_sementara`
--

CREATE TABLE `jawaban_sementara` (
  `id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `soal_id` int(11) NOT NULL,
  `jawaban` char(1) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_jawab` datetime DEFAULT NULL,
  `is_benar` tinyint(1) DEFAULT NULL
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
  `max_siswa` int(11) DEFAULT NULL,
  `photographer_name` varchar(255) DEFAULT NULL,
  `photographer_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `path_file` varchar(255) DEFAULT NULL,
  `hls_path` varchar(255) DEFAULT NULL
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

-- --------------------------------------------------------

--
-- Table structure for table `pengumpulan_tugas`
--

CREATE TABLE `pengumpulan_tugas` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) NOT NULL,
  `siswa_id` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `tipe_file` varchar(100) DEFAULT NULL,
  `ukuran_file` int(11) DEFAULT NULL,
  `waktu_pengumpulan` datetime NOT NULL,
  `pesan_siswa` text DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL,
  `komentar_guru` text DEFAULT NULL,
  `tanggal_penilaian` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `presentasi_aktif`
--

CREATE TABLE `presentasi_aktif` (
  `id` int(11) NOT NULL,
  `kelas_id` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `current_slide` int(11) NOT NULL DEFAULT 1,
  `total_slides` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `file_type` varchar(10) DEFAULT 'pdf',
  `zoom_scale` decimal(4,2) DEFAULT 1.50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presentasi_annotations`
--

CREATE TABLE `presentasi_annotations` (
  `id` int(11) NOT NULL,
  `kelas_id` varchar(50) NOT NULL,
  `presentation_id` varchar(255) DEFAULT NULL,
  `page` int(11) NOT NULL,
  `annotations` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presentasi_fullscreen_status`
--

CREATE TABLE `presentasi_fullscreen_status` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `siswa_nama` varchar(255) NOT NULL,
  `is_fullscreen` tinyint(1) NOT NULL DEFAULT 0,
  `browser` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nama` varchar(1000) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(1000) NOT NULL,
  `tingkat` varchar(20) DEFAULT NULL,
  `kategori` enum('SMA','Pondok','internal','trial_class') DEFAULT 'internal',
  `foto_profil` varchar(1000) NOT NULL,
  `tahun_masuk` int(11) DEFAULT NULL,
  `no_hp` int(11) DEFAULT NULL,
  `alamat` varchar(1000) DEFAULT NULL,
  `nis` int(11) DEFAULT NULL,
  `photo_type` enum('upload','avatar') DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa_favorites`
--

CREATE TABLE `siswa_favorites` (
  `id` int(11) NOT NULL,
  `siswa_username` varchar(100) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `soal_descriptions`
--

CREATE TABLE `soal_descriptions` (
  `id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_hidden` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ujian_status`
--

CREATE TABLE `ujian_status` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `ujian_id` int(11) NOT NULL,
  `status` enum('belum_mulai','sedang_ujian','selesai','bermasalah') NOT NULL DEFAULT 'belum_mulai',
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `terakhir_aktif` datetime DEFAULT NULL,
  `fullscreen_exits` int(11) NOT NULL DEFAULT 0,
  `is_paused` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_chat_sessions_id` (`ai_chat_sessions_id`);

--
-- Indexes for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indexes for table `bank_soal`
--
ALTER TABLE `bank_soal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ujian_id` (`ujian_id`);

--
-- Indexes for table `canvas_versions`
--
ALTER TABLE `canvas_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ai_chat_sessions_id_version_number` (`ai_chat_sessions_id`,`version_number`);

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
-- Indexes for table `file_pengumpulan_tugas`
--
ALTER TABLE `file_pengumpulan_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengumpulan_id` (`pengumpulan_id`);

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
-- Indexes for table `hasil_ujian`
--
ALTER TABLE `hasil_ujian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hasil` (`siswa_id`,`ujian_id`);

--
-- Indexes for table `informasi_sekolah`
--
ALTER TABLE `informasi_sekolah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jawaban_sementara`
--
ALTER TABLE `jawaban_sementara`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ujian_siswa_soal` (`ujian_id`,`siswa_id`,`soal_id`);

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
-- Indexes for table `presentasi_aktif`
--
ALTER TABLE `presentasi_aktif`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `presentasi_annotations`
--
ALTER TABLE `presentasi_annotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`,`page`,`active`);

--
-- Indexes for table `presentasi_fullscreen_status`
--
ALTER TABLE `presentasi_fullscreen_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kelas_siswa_unique` (`kelas_id`,`siswa_id`);

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
-- Indexes for table `siswa_favorites`
--
ALTER TABLE `siswa_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`siswa_username`,`kelas_id`);

--
-- Indexes for table `soal_descriptions`
--
ALTER TABLE `soal_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ujian_id` (`ujian_id`);

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
-- Indexes for table `ujian_status`
--
ALTER TABLE `ujian_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_ujian` (`siswa_id`,`ujian_id`);

--
-- Indexes for table `user_character_analysis`
--
ALTER TABLE `user_character_analysis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_date` (`user_id`,`analysis_date`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username_key` (`username`,`preference_key`);

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
-- AUTO_INCREMENT for table `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_soal`
--
ALTER TABLE `bank_soal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `canvas_versions`
--
ALTER TABLE `canvas_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catatan_guru`
--
ALTER TABLE `catatan_guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emoji_reactions`
--
ALTER TABLE `emoji_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_pengumpulan_tugas`
--
ALTER TABLE `file_pengumpulan_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hasil_ujian`
--
ALTER TABLE `hasil_ujian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `informasi_sekolah`
--
ALTER TABLE `informasi_sekolah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jawaban_sementara`
--
ALTER TABLE `jawaban_sementara`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jawaban_ujian`
--
ALTER TABLE `jawaban_ujian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas_siswa`
--
ALTER TABLE `kelas_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `komentar_postingan`
--
ALTER TABLE `komentar_postingan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `komentar_replies`
--
ALTER TABLE `komentar_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lampiran_postingan`
--
ALTER TABLE `lampiran_postingan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lampiran_tugas`
--
ALTER TABLE `lampiran_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes_postingan`
--
ALTER TABLE `likes_postingan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pg`
--
ALTER TABLE `pg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pilihan_jawaban`
--
ALTER TABLE `pilihan_jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `postingan_kelas`
--
ALTER TABLE `postingan_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `presentasi_aktif`
--
ALTER TABLE `presentasi_aktif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `presentasi_annotations`
--
ALTER TABLE `presentasi_annotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `presentasi_fullscreen_status`
--
ALTER TABLE `presentasi_fullscreen_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_knowledge`
--
ALTER TABLE `project_knowledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saga_personality`
--
ALTER TABLE `saga_personality`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa_favorites`
--
ALTER TABLE `siswa_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `soal_descriptions`
--
ALTER TABLE `soal_descriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `soal_ujian`
--
ALTER TABLE `soal_ujian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ujian`
--
ALTER TABLE `ujian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ujian_status`
--
ALTER TABLE `ujian_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_character_analysis`
--
ALTER TABLE `user_character_analysis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_topics`
--
ALTER TABLE `user_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  ADD CONSTRAINT `ai_chat_messages_ibfk_1` FOREIGN KEY (`ai_chat_sessions_id`) REFERENCES `ai_chat_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bank_soal`
--
ALTER TABLE `bank_soal`
  ADD CONSTRAINT `bank_soal_ibfk_1` FOREIGN KEY (`ujian_id`) REFERENCES `ujian` (`id`);

--
-- Constraints for table `canvas_versions`
--
ALTER TABLE `canvas_versions`
  ADD CONSTRAINT `canvas_versions_ibfk_1` FOREIGN KEY (`ai_chat_sessions_id`) REFERENCES `ai_chat_sessions` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `file_pengumpulan_tugas`
--
ALTER TABLE `file_pengumpulan_tugas`
  ADD CONSTRAINT `file_pengumpulan_tugas_ibfk_1` FOREIGN KEY (`pengumpulan_id`) REFERENCES `pengumpulan_tugas` (`id`) ON DELETE CASCADE;

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
