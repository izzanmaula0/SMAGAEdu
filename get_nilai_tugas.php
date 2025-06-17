<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $pengumpulan_id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $query = "SELECT nilai, komentar_guru FROM pengumpulan_tugas WHERE id = '$pengumpulan_id'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        echo json_encode([
            'success' => true,
            'nilai' => $data['nilai'],
            'komentar' => $data['komentar_guru']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
}
?>