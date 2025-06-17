<?php
session_start();
require "koneksi.php";

// Cek apakah user sudah login dan adalah guru
if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if(isset($_GET['id']) && isset($_GET['kelas_id'])) {
    $catatan_id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $kelas_id = mysqli_real_escape_string($koneksi, $_GET['kelas_id']);
    $guru_id = $_SESSION['userid'];

    // Verifikasi bahwa catatan ini milik guru yang bersangkutan dan di kelas yang benar
    $query_check = "SELECT c.*, k.guru_id as kelas_guru_id 
                   FROM catatan_guru c
                   JOIN kelas k ON c.kelas_id = k.id 
                   WHERE c.id = '$catatan_id' 
                   AND c.kelas_id = '$kelas_id' 
                   AND k.guru_id = '$guru_id'";

    $result_check = mysqli_query($koneksi, $query_check);

    if(mysqli_num_rows($result_check) > 0) {
        $catatan = mysqli_fetch_assoc($result_check);
        
        // Hapus file lampiran jika ada
        if(!empty($catatan['file_lampiran']) && file_exists($catatan['file_lampiran'])) {
            unlink($catatan['file_lampiran']);
        }

        // Hapus catatan dari database
        $query_delete = "DELETE FROM catatan_guru WHERE id = '$catatan_id'";
        
        if(mysqli_query($koneksi, $query_delete)) {
            header("Location: kelas_guru.php?id=" . $kelas_id . "&success=catatan_deleted");
        } else {
            header("Location: kelas_guru.php?id=" . $kelas_id . "&error=delete_failed");
        }
    } else {
        header("Location: beranda_guru.php");
    }
} else {
    header("Location: beranda_guru.php");
}
?>