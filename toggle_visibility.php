<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    
    // Pastikan id dan status valid
    if (!is_numeric($id) || !in_array($status, [0, 1])) {
        $_SESSION['pesan'] = "invalid_data";
        header("Location: ujian_guru.php");
        exit();
    }
    
    // Update status visibilitas ujian
    $query = "UPDATE ujian SET is_hidden = $status WHERE id = $id AND guru_id = '{$_SESSION['userid']}'";
    $result = mysqli_query($koneksi, $query);
    
    if ($result) {
        $_SESSION['pesan'] = ($status == 1) ? "hide_success" : "show_success";
    } else {
        $_SESSION['pesan'] = "update_error";
    }
}

header("Location: ujian_guru.php");
exit();
?>