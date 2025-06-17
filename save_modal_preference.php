<?php
session_start();
require "koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Ambil userid dari session
$userid = $_SESSION['userid'];
$key = isset($_POST['key']) ? $_POST['key'] : '';

if (empty($key)) {
    echo json_encode(['success' => false, 'message' => 'Missing key parameter']);
    exit();
}

// Periksa apakah sudah ada preferensi untuk user ini
$query = "SELECT * FROM user_preferences WHERE username = ? AND preference_key = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("ss", $userid, $key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update preferensi yang sudah ada
    $query = "UPDATE user_preferences SET preference_value = 'true', updated_at = NOW() 
              WHERE username = ? AND preference_key = ?";
} else {
    // Buat preferensi baru
    $query = "INSERT INTO user_preferences (username, preference_key, preference_value, created_at) 
              VALUES (?, ?, 'true', NOW())";
}

$stmt = $koneksi->prepare($query);
$stmt->bind_param("ss", $userid, $key);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>