<?php
session_start();
require_once "../koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil data JSON dari request
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Validasi input
$kelas_id = isset($input['kelas_id']) ? intval($input['kelas_id']) : 0;
$username = isset($input['username']) ? $input['username'] : '';
$isFullscreen = isset($input['isFullscreen']) ? (bool)$input['isFullscreen'] : false;
$browser = isset($input['browser']) ? $input['browser'] : '';
$timestamp = isset($input['timestamp']) ? $input['timestamp'] : date('Y-m-d H:i:s');

// Cari data siswa
$query_siswa = "SELECT id, nama FROM siswa WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result_siswa = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result_siswa) == 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

$siswa = mysqli_fetch_assoc($result_siswa);
$siswa_id = $siswa['id'];
$siswa_nama = $siswa['nama'];

// Cek apakah sudah ada data status sebelumnya
$query_check = "SELECT * FROM presentasi_fullscreen_status WHERE kelas_id = ? AND siswa_id = ?";
$stmt = mysqli_prepare($koneksi, $query_check);
mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $siswa_id);
mysqli_stmt_execute($stmt);
$result_check = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result_check) > 0) {
    // Update data yang sudah ada
    $query = "UPDATE presentasi_fullscreen_status SET 
              is_fullscreen = ?, 
              browser = ?, 
              updated_at = NOW() 
              WHERE kelas_id = ? AND siswa_id = ?";
    
    $stmt = mysqli_prepare($koneksi, $query);
    $is_fullscreen_int = $isFullscreen ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "isii", $is_fullscreen_int, $browser, $kelas_id, $siswa_id);
} else {
    // Insert data baru
    $query = "INSERT INTO presentasi_fullscreen_status 
              (kelas_id, siswa_id, siswa_nama, is_fullscreen, browser, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = mysqli_prepare($koneksi, $query);
    $is_fullscreen_int = $isFullscreen ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "iissi", $kelas_id, $siswa_id, $siswa_nama, $is_fullscreen_int, $browser);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Fullscreen status updated',
        'student' => $siswa_nama,
        'isFullscreen' => $isFullscreen
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update fullscreen status: ' . mysqli_error($koneksi)
    ]);
}
?>