<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userid = $_SESSION['userid'];

// Mark a single notification as read
if (isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    
    $query = "UPDATE notifikasi SET sudah_dibaca = 1 
              WHERE id = ? AND penerima_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "is", $notification_id, $userid);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
    }
}
// Mark all notifications as read
else if (isset($_POST['mark_all'])) {
    $query = "UPDATE notifikasi SET sudah_dibaca = 1 
              WHERE penerima_id = ? AND sudah_dibaca = 0";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $userid);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark all notifications as read']);
    }
}
else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
?>