<?php
// archive_kelas.php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if(isset($_GET['id'])) {
    $kelas_id = $_GET['id'];
    $userid = $_SESSION['userid'];
    
    // Verify that the class belongs to the current teacher
    $query = "UPDATE kelas SET is_archived = 1 
              WHERE id = ? AND guru_id = ?";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "is", $kelas_id, $userid);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Kelas berhasil diarsipkan.";
    } else {
        $_SESSION['error'] = "Gagal mengarsipkan kelas.";
    }
    
    mysqli_stmt_close($stmt);
}

header("Location: beranda_guru.php");
exit();
?>

<?php
// unarchive_kelas.php
session_start();
require "koneksi.php";

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if(isset($_GET['id'])) {
    $kelas_id = $_GET['id'];
    $userid = $_SESSION['userid'];
    
    // Verify that the class belongs to the current teacher
    $query = "UPDATE kelas SET is_archived = 0 
              WHERE id = ? AND guru_id = ?";
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "is", $kelas_id, $userid);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Kelas berhasil dikeluarkan dari arsip.";
    } else {
        $_SESSION['error'] = "Gagal mengeluarkan kelas dari arsip.";
    }
    
    mysqli_stmt_close($stmt);
}

header("Location: beranda_guru.php");
exit();
?>