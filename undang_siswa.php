<?php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if(isset($_POST['kelas_id']) && isset($_POST['siswa_ids'])) {
    $kelas_id = mysqli_real_escape_string($koneksi, $_POST['kelas_id']);
    $siswa_ids = $_POST['siswa_ids']; // Array dari siswa yang dipilih
    
    // Validasi kelas milik guru yang sedang login
    $query_check = "SELECT id FROM kelas WHERE id = ? AND guru_id = ?";
    $stmt_check = mysqli_prepare($koneksi, $query_check);
    mysqli_stmt_bind_param($stmt_check, "is", $kelas_id, $_SESSION['userid']);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if(mysqli_num_rows($result_check) > 0) {
        // Prepare statement untuk insert
        $query_insert = "INSERT INTO kelas_siswa (kelas_id, siswa_id) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($koneksi, $query_insert);
        
        foreach($siswa_ids as $siswa_id) {
            mysqli_stmt_bind_param($stmt_insert, "ii", $kelas_id, $siswa_id);
            mysqli_stmt_execute($stmt_insert);
        }
        
        header("Location: kelas_guru.php?id=" . $kelas_id . "&pesan=siswa_berhasil_ditambahkan");
    } else {
        header("Location: beranda_guru.php?pesan=akses_ditolak");
    }
} else {
    header("Location: beranda_guru.php?pesan=data_tidak_lengkap");
}
?>