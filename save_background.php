<?php
session_start();
require "koneksi.php";

// Debugging
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// Cek jika user sudah login dan merupakan guru
if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Cek jika ada file yang diupload dan kelas_id
if(!isset($_FILES['image']) || !isset($_POST['kelas_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$kelas_id = mysqli_real_escape_string($koneksi, $_POST['kelas_id']);

// Ambil dan simpan informasi photographer dari Unsplash
$photographer = isset($_POST['photographer']) ? mysqli_real_escape_string($koneksi, $_POST['photographer']) : '';
$profile_url = isset($_POST['profile_url']) ? mysqli_real_escape_string($koneksi, $_POST['profile_url']) : '';

// Verifikasi kepemilikan kelas
$query_check = "SELECT * FROM kelas WHERE id = '$kelas_id' AND guru_id = '{$_SESSION['userid']}'";
$result_check = mysqli_query($koneksi, $query_check);

if(mysqli_num_rows($result_check) == 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access to class']);
    exit();
}

// Konfigurasi upload
$upload_dir = 'uploads/background/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate nama file unik
$file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$file_name = uniqid('bg_') . '_' . time() . '.' . $file_extension;
$file_path = $upload_dir . $file_name;

// Cek tipe file
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
if (!in_array($_FILES['image']['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

// Cek ukuran file (max 5MB)
if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large']);
    exit();
}

// Upload file
if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
    // Hapus background lama jika ada
    $query_old = "SELECT background_image FROM kelas WHERE id = '$kelas_id'";
    $result_old = mysqli_query($koneksi, $query_old);
    $row = mysqli_fetch_assoc($result_old);
    
    if (!empty($row['background_image'])) {
        $old_file = $row['background_image'];
        if (file_exists($old_file)) {
            unlink($old_file);
        }
    }

    // Update database dengan path file dan informasi photographer
    $file_path_db = mysqli_real_escape_string($koneksi, $file_path);
    
    // Tambahkan kolom photographer_name dan photographer_url ke query update
    $query_update = "UPDATE kelas SET 
                    background_image = '$file_path_db',
                    photographer_name = '$photographer',
                    photographer_url = '$profile_url'
                    WHERE id = '$kelas_id'";
    
    if (mysqli_query($koneksi, $query_update)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Background updated successfully',
            'path' => $file_path,
            'photographer' => $photographer,
            'profile_url' => $profile_url
        ]);
    } else {
        unlink($file_path); // Hapus file jika update database gagal
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Database update failed: ' . mysqli_error($koneksi)
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}
?>