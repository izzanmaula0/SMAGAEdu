<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if(!isset($_POST['postingan_id']) || !isset($_POST['konten']) || empty($_POST['konten'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$postingan_id = $_POST['postingan_id'];
$user_id = $_SESSION['userid'];
$konten = $_POST['konten'];

// Cek apakah postingan ada
$query_cek = "SELECT * FROM postingan_kelas WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query_cek);
mysqli_stmt_bind_param($stmt, "i", $postingan_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

// Tambah komentar
$query = "INSERT INTO komentar_postingan (postingan_id, user_id, konten) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "iss", $postingan_id, $user_id, $konten);
$success = mysqli_stmt_execute($stmt);

if($success) {
    // Ambil data komentar yang baru ditambahkan
    $komentar_id = mysqli_insert_id($koneksi);
    $query_komentar = "SELECT kp.*, 
                       s.nama as nama_siswa, s.foto_profil as foto_siswa,
                       g.namaLengkap as nama_guru, g.foto_profil as foto_guru,
                       IF(s.id IS NOT NULL, 'siswa', 'guru') as user_type
                       FROM komentar_postingan kp
                       LEFT JOIN siswa s ON kp.user_id = s.username
                       LEFT JOIN guru g ON kp.user_id = g.username
                       WHERE kp.id = ?";
    
    $stmt = mysqli_prepare($koneksi, $query_komentar);
    mysqli_stmt_bind_param($stmt, "i", $komentar_id);
    mysqli_stmt_execute($stmt);
    $komentar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    echo json_encode([
        'success' => true,
        'komentar' => $komentar
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add comment'
    ]);
}