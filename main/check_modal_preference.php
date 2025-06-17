<?php
session_start();
require "koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    echo json_encode(['hide' => false]);
    exit();
}

// Ambil userid dari session
$userid = $_SESSION['userid'];
$key = isset($_POST['key']) ? $_POST['key'] : '';

if (empty($key)) {
    echo json_encode(['hide' => false]);
    exit();
}

// Periksa apakah preferensi tersimpan di database
$query = "SELECT preference_value FROM user_preferences 
          WHERE username = ? AND preference_key = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("ss", $userid, $key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hide = ($row['preference_value'] === 'true');
} else {
    $hide = false;
}

echo json_encode(['hide' => $hide]);
?>