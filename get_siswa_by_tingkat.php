<?php
session_start();
require "koneksi.php";

// Check if the logged-in user is an admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['tingkat'])) {
    $tingkat = mysqli_real_escape_string($koneksi, $_GET['tingkat']);
    
    // Get students by grade level
    $query = "SELECT * FROM siswa 
              WHERE tingkat = '$tingkat' 
              ORDER BY nama ASC";
    
    $result = mysqli_query($koneksi, $query);
    
    $siswa = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $siswa[] = [
            'id' => $row['id'],
            'nama' => $row['nama'],
            'username' => $row['username'],
            'tingkat' => $row['tingkat']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($siswa);
    exit();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing tingkat parameter']);
    exit();
}
?>