<?php
session_start();
require "../koneksi.php";

// Pastikan user adalah guru
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Pastikan kelas_id diberikan
if (!isset($_GET['kelas_id']) || empty($_GET['kelas_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing kelas_id parameter'
    ]);
    exit;
}

$kelas_id = $_GET['kelas_id'];

// Cek status presentasi di kelas
$query = "SELECT * FROM presentasi_aktif WHERE kelas_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $kelas_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $presentasi = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'kelas_id' => $kelas_id,
        'active' => (bool)$presentasi['active'],
        'updated_at' => $presentasi['updated_at'],
        // Bisa ditambahkan info lain sesuai kebutuhan
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No presentation data found for this class'
    ]);
}
?>