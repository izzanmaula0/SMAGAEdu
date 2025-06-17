<?php
require "koneksi.php";
session_start();

if(!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die("Akses ditolak");
}

if(isset($_POST['action'])) {
    $table = mysqli_real_escape_string($koneksi, $_POST['table']);
    
    if($_POST['action'] == 'add') {
        $columns = [];
        $values = [];
        
        foreach($_POST as $key => $value) {
            if($key != 'action' && $key != 'table' && $key != 'id') {
                $columns[] = mysqli_real_escape_string($koneksi, $key);
                $values[] = "'" . mysqli_real_escape_string($koneksi, $value) . "'";
            }
        }
        
        $query = "INSERT INTO $table (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")";
        mysqli_query($koneksi, $query);
        
    } elseif($_POST['action'] == 'edit') {
        $id = mysqli_real_escape_string($koneksi, $_POST['id']);
        $updates = [];
        
        foreach($_POST as $key => $value) {
            if($key != 'action' && $key != 'table' && $key != 'id') {
                $updates[] = "$key = '" . mysqli_real_escape_string($koneksi, $value) . "'";
            }
        }
        
        $query = "UPDATE $table SET " . implode(",", $updates) . " WHERE id = $id";
        mysqli_query($koneksi, $query);
    }
    
} elseif(isset($_GET['action']) && $_GET['action'] == 'delete') {
    $table = mysqli_real_escape_string($koneksi, $_GET['table']);
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    if ($table == 'siswa') {
        // Delete related records in jawaban_ujian first
        $query = "DELETE FROM jawaban_ujian WHERE siswa_id = $id";
        mysqli_query($koneksi, $query);
        
        // Delete related records in kelas_siswa
        $query = "DELETE FROM kelas_siswa WHERE siswa_id = $id";
        mysqli_query($koneksi, $query);
        
        // Finally delete the student
        $query = "DELETE FROM siswa WHERE id = $id";
        mysqli_query($koneksi, $query);
    } else {
        $query = "DELETE FROM $table WHERE id = $id";
        mysqli_query($koneksi, $query);
    }
}

header("Location: admin_dashboard.php?table=" . ($table ?? ''));
exit();
?>