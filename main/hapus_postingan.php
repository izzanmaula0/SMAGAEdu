<?php
require "koneksi.php";
session_start();

// Cek apakah user sudah login dan adalah guru atau admin
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['kelas_id'])) {
    $postingan_id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $kelas_id = mysqli_real_escape_string($koneksi, $_GET['kelas_id']);
    
    // Jika guru, pastikan guru mengajar di kelas ini
    if ($_SESSION['level'] == 'guru') {
        $query_kelas = "SELECT * FROM kelas WHERE id = '$kelas_id' AND guru_id = '{$_SESSION['userid']}'";
        $result_kelas = mysqli_query($koneksi, $query_kelas);
        
        if (mysqli_num_rows($result_kelas) == 0) {
            header("Location: beranda_guru.php");
            exit();
        }
    }
    
    // Hapus lampiran terlebih dahulu
    $query_lampiran = "SELECT path_file FROM lampiran_postingan WHERE postingan_id = '$postingan_id'";
    $result_lampiran = mysqli_query($koneksi, $query_lampiran);
    
    while ($lampiran = mysqli_fetch_assoc($result_lampiran)) {
        // Hapus file fisik jika ada
        if (file_exists($lampiran['path_file'])) {
            unlink($lampiran['path_file']);
        }
    }
    
    // Hapus data lampiran dari database
    mysqli_query($koneksi, "DELETE FROM lampiran_postingan WHERE postingan_id = '$postingan_id'");
    
    // Hapus emoji reactions jika ada
    mysqli_query($koneksi, "DELETE FROM emoji_reactions WHERE postingan_id = '$postingan_id'");
    
    // Hapus komentar jika ada
    mysqli_query($koneksi, "DELETE FROM komentar_postingan WHERE postingan_id = '$postingan_id'");
    
    // Hapus tugas jika ini adalah postingan tugas
    mysqli_query($koneksi, "DELETE FROM tugas WHERE postingan_id = '$postingan_id'");
    
    // Hapus postingan
    mysqli_query($koneksi, "DELETE FROM postingan_kelas WHERE id = '$postingan_id'");
    
    header("Location: kelas_guru.php?id=$kelas_id&pesan=berhasil_hapus");
} else {
    header("Location: beranda_guru.php");
}
?>