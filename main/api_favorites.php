<?php
include 'includes/session_config.php';
require "koneksi.php";

// Set header JSON
header('Content-Type: application/json');

// Cek apakah user login sebagai siswa
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userid = $_SESSION['userid'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_favorites':
        getFavorites($koneksi, $userid);
        break;
    
    case 'toggle_favorite':
        toggleFavorite($koneksi, $userid);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getFavorites($koneksi, $userid) {
    $query = "SELECT kelas_id FROM siswa_favorites WHERE siswa_username = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $favorites = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $favorites[] = $row['kelas_id'];
    }
    
    echo json_encode(['favorites' => $favorites]);
}

function toggleFavorite($koneksi, $userid) {
    $kelas_id = $_POST['kelas_id'] ?? '';
    
    if (empty($kelas_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Kelas ID required']);
        return;
    }
    
    // Cek apakah sudah ada di favorit
    $check_query = "SELECT id FROM siswa_favorites WHERE siswa_username = ? AND kelas_id = ?";
    $check_stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($check_stmt, "si", $userid, $kelas_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Hapus dari favorit
        $delete_query = "DELETE FROM siswa_favorites WHERE siswa_username = ? AND kelas_id = ?";
        $delete_stmt = mysqli_prepare($koneksi, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "si", $userid, $kelas_id);
        $success = mysqli_stmt_execute($delete_stmt);
        
        echo json_encode([
            'success' => $success,
            'action' => 'removed',
            'message' => 'Kelas dihapus dari favorit'
        ]);
    } else {
        // Tambah ke favorit
        $insert_query = "INSERT INTO siswa_favorites (siswa_username, kelas_id) VALUES (?, ?)";
        $insert_stmt = mysqli_prepare($koneksi, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "si", $userid, $kelas_id);
        $success = mysqli_stmt_execute($insert_stmt);
        
        echo json_encode([
            'success' => $success,
            'action' => 'added',
            'message' => 'Kelas ditambahkan ke favorit'
        ]);
    }
}
?>