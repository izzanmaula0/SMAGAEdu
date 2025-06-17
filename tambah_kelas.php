<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit'])) {
    mysqli_begin_transaction($koneksi);

    try {
        // Get basic data
        $guru_id = $_SESSION['userid'];
        $is_public = isset($_POST['jenis_kelas']) ? (int)$_POST['jenis_kelas'] : 0;
        
        // Process description
        $deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($koneksi, $_POST['deskripsi']) : '';
        
        // Process materi array
        $materi = isset($_POST['materi']) ? $_POST['materi'] : [];
        $materi_json = json_encode(array_values(array_filter($materi)));
        
        // Handle max_siswa
        $max_siswa = isset($_POST['max_siswa']) && !empty($_POST['max_siswa']) ? (int)$_POST['max_siswa'] : null;

        // Set values based on class type
        if ($is_public) {
            // For public classes
            $nama_kelas = mysqli_real_escape_string($koneksi, $_POST['judul_kelas']);
            $mata_pelajaran = "";
            $tingkat = "";
        } else {
            // For private classes
            $mata_pelajaran = mysqli_real_escape_string($koneksi, $_POST['mata_pelajaran']);
            $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
            $nama_kelas = "$mata_pelajaran Kelas $tingkat";
        }

        // Insert into database
        $query_kelas = "INSERT INTO kelas (nama_kelas, deskripsi, guru_id, mata_pelajaran, tingkat, materi, is_public, max_siswa) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($koneksi, $query_kelas);
        mysqli_stmt_bind_param($stmt, "ssssssii", $nama_kelas, $deskripsi, $guru_id, $mata_pelajaran, $tingkat, $materi_json, $is_public, $max_siswa);

        if (mysqli_stmt_execute($stmt)) {
            $kelas_id = mysqli_insert_id($koneksi);

            // Process selected students only for private classes
            if (!$is_public && isset($_POST['siswa_ids']) && is_array($_POST['siswa_ids'])) {
                $query_siswa = "INSERT INTO kelas_siswa (kelas_id, siswa_id) VALUES (?, ?)";
                $stmt_siswa = mysqli_prepare($koneksi, $query_siswa);

                foreach ($_POST['siswa_ids'] as $siswa_id) {
                    mysqli_stmt_bind_param($stmt_siswa, "ii", $kelas_id, $siswa_id);
                    mysqli_stmt_execute($stmt_siswa);
                }
            }

            mysqli_commit($koneksi);
            header("Location: kelas_guru.php?id=" . $kelas_id);
            exit();
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: beranda_guru.php?pesan=gagal_tambah_kelas");
        exit();
    }
} else {
    header("Location: beranda_guru.php");
    exit();
}