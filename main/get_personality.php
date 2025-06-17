<?php
// get_personality.php
session_start();
require_once 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['userid'];

// Query untuk mengambil personality
$query = "SELECT * FROM saga_personality WHERE user_id = '$userId'";
$result = mysqli_query($koneksi, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $personality = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'personality' => $personality
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No personality found'
    ]);
}
?>