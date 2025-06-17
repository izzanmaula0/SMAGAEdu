<?php
session_start();
require "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $komentar_id = mysqli_real_escape_string($koneksi, $_POST['komentar_id']);
    $user_id = $_SESSION['userid'];
    $konten = mysqli_real_escape_string($koneksi, $_POST['konten']);
    
    $query = "INSERT INTO komentar_replies (komentar_id, user_id, konten) 
              VALUES ('$komentar_id', '$user_id', '$konten')";
    
    if (mysqli_query($koneksi, $query)) {
        $response = [
            'status' => 'success',
            'message' => 'Balasan berhasil ditambahkan'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Gagal menambahkan balasan'
        ];
    }
    
    echo json_encode($response);
}
?>