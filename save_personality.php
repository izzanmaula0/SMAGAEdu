<?php
session_start();
require_once 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Ambil data JSON dari request
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Sanitasi input
$userId = $_SESSION['userid'];
$currentJob = isset($input['currentJob']) ? mysqli_real_escape_string($koneksi, $input['currentJob']) : '';
$personality = isset($input['personality']) ? mysqli_real_escape_string($koneksi, $input['personality']) : '';
$additionalInfo = isset($input['additionalInfo']) ? mysqli_real_escape_string($koneksi, $input['additionalInfo']) : '';

// Cek apakah sudah ada personality untuk user ini
$checkQuery = "SELECT * FROM saga_personality WHERE user_id = '$userId'";
$checkResult = mysqli_query($koneksi, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    // Update existing record
    $query = "UPDATE saga_personality SET 
              current_job = '$currentJob',
              traits = '$personality',
              additional_info = '$additionalInfo',
              updated_at = NOW()
              WHERE user_id = '$userId'";
} else {
    // Insert new record
    $query = "INSERT INTO saga_personality 
              (user_id, current_job, traits, additional_info, created_at, updated_at)
              VALUES 
              ('$userId', '$currentJob', '$personality', '$additionalInfo', NOW(), NOW())";
}

$result = mysqli_query($koneksi, $query);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($koneksi)]);
}
?>

