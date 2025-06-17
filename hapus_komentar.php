<?php
require "koneksi.php";
session_start();

if (!isset($_POST['komentar_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$komentar_id = mysqli_real_escape_string($koneksi, $_POST['komentar_id']);
$post_id = mysqli_real_escape_string($koneksi, $_POST['post_id']);

// Verify if user is a teacher
if ($_SESSION['level'] !== 'guru') {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin']);
    exit;
}

// Delete the comment
$query_delete = "DELETE FROM komentar_postingan WHERE id = '$komentar_id'";
$result_delete = mysqli_query($koneksi, $query_delete);

if ($result_delete) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus komentar']);
}
?>