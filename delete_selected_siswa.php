<?php
session_start();
require "koneksi.php";

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == 'delete_selected' && isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $delete_ids = $_POST['delete_ids'];
    $success_count = 0;
    $error_count = 0;
    
    // Mulai transaksi database
    mysqli_begin_transaction($koneksi);
    
    try {
        foreach ($delete_ids as $id) {
            $id = mysqli_real_escape_string($koneksi, $id);
            
            // Hapus data kelas_siswa terlebih dahulu
            mysqli_query($koneksi, "DELETE FROM kelas_siswa WHERE siswa_id = '$id'");
            
            // Hapus data pg jika ada
            mysqli_query($koneksi, "DELETE FROM pg WHERE siswa_id = '$id'");
            
            // Hapus data pengumpulan_tugas jika ada
            mysqli_query($koneksi, "DELETE FROM pengumpulan_tugas WHERE siswa_id = '$id'");
            
            // Hapus data jawaban_ujian jika ada
            mysqli_query($koneksi, "DELETE FROM jawaban_ujian WHERE siswa_id = '$id'");
            
            // Hapus siswa
            $result = mysqli_query($koneksi, "DELETE FROM siswa WHERE id = '$id'");
            
            if ($result) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        // Commit transaksi jika semua berhasil
        mysqli_commit($koneksi);
        
        if ($success_count > 0) {
            $_SESSION['success'] = "Berhasil menghapus $success_count siswa" . ($error_count > 0 ? ", $error_count siswa gagal dihapus" : "");
        } else {
            $_SESSION['error'] = "Tidak ada siswa yang berhasil dihapus";
        }
        
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($koneksi);
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
    
    header("Location: siswa_admin.php");
    exit();
} else {
    $_SESSION['error'] = "Permintaan tidak valid";
    header("Location: siswa_admin.php");
    exit();
}
?>