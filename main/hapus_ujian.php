<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if(isset($_GET['id'])) {
    $ujian_id = $_GET['id'];
    $guru_id = $_SESSION['userid'];
    
    // Start transaction
    mysqli_begin_transaction($koneksi);
    
    try {
        // Cek kepemilikan ujian
        $query_check = "SELECT id FROM ujian WHERE id = ? AND guru_id = ?";
        $stmt_check = mysqli_prepare($koneksi, $query_check);
        mysqli_stmt_bind_param($stmt_check, "is", $ujian_id, $guru_id);
        mysqli_stmt_execute($stmt_check);
        $result = mysqli_stmt_get_result($stmt_check);
        
        if(mysqli_num_rows($result) > 0) {
            // Hapus jawaban
            $query_delete_jawaban = "DELETE FROM jawaban_ujian WHERE ujian_id = ?";
            $stmt_jawaban = mysqli_prepare($koneksi, $query_delete_jawaban);
            mysqli_stmt_bind_param($stmt_jawaban, "i", $ujian_id);
            mysqli_stmt_execute($stmt_jawaban);
            
            // Hapus soal
            $query_delete_soal = "DELETE FROM bank_soal WHERE ujian_id = ?";
            $stmt_soal = mysqli_prepare($koneksi, $query_delete_soal);
            mysqli_stmt_bind_param($stmt_soal, "i", $ujian_id);
            mysqli_stmt_execute($stmt_soal);
            
            // Hapus ujian
            $query_delete_ujian = "DELETE FROM ujian WHERE id = ?";
            $stmt_ujian = mysqli_prepare($koneksi, $query_delete_ujian);
            mysqli_stmt_bind_param($stmt_ujian, "i", $ujian_id);
            
            if(mysqli_stmt_execute($stmt_ujian)) {
                mysqli_commit($koneksi);
                header("Location: ujian_guru.php?pesan=hapus_berhasil");
            } else {
                throw new Exception("Gagal menghapus ujian");
            }
        } else {
            throw new Exception("Ujian tidak ditemukan");
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: ujian_guru.php?pesan=hapus_gagal");
    }
} else {
    header("Location: ujian_guru.php");
}
exit();
?>