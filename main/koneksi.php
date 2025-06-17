<?php
// Koneksi ke database smagaedu
$koneksi = mysqli_connect("localhost", "smpp3485_admin", "kemambuan", "smpp3485_smagaedu");

// Cek koneksi
if (mysqli_connect_errno()){
    echo "Koneksi database gagal : " . mysqli_connect_error();
    exit();
}

// Set karakter encoding ke utf8mb4
mysqli_set_charset($koneksi, 'utf8mb4');


