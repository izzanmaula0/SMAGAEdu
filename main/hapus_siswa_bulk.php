<?php
session_start();
require "koneksi.php";

// Cek session
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['siswa_ids']) && isset($_GET['kelas_id'])) {
    $siswa_ids = json_decode($_GET['siswa_ids']);
    $kelas_id = mysqli_real_escape_string($koneksi, $_GET['kelas_id']);
    
    // Verifikasi bahwa guru ini mengajar kelas ini
    $query_cek = "SELECT * FROM kelas WHERE id = '$kelas_id' AND guru_id = '{$_SESSION['userid']}'";
    $result_cek = mysqli_query($koneksi, $query_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
        $success = true;
        
        // Hapus setiap siswa dari kelas
        foreach ($siswa_ids as $siswa_id) {
            $siswa_id = mysqli_real_escape_string($koneksi, $siswa_id);
            $query_hapus = "DELETE FROM kelas_siswa WHERE siswa_id = '$siswa_id' AND kelas_id = '$kelas_id'";
            
            if (!mysqli_query($koneksi, $query_hapus)) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            header("Location: kelas_guru.php?id=$kelas_id&success=bulk_deleted");
        } else {
            header("Location: kelas_guru.php?id=$kelas_id&error=bulk_delete_failed");
        }
    } else {
        header("Location: kelas_guru.php?id=$kelas_id&error=unauthorized");
    }
} else {
    header("Location: beranda_guru.php");
}
?>