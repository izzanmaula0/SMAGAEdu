<?php
session_start();
require 'koneksi.php';

// Check if user is logged in
if(!isset($_SESSION['userid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Validate POST data
if(!isset($_POST['postingan_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID is required'
    ]);
    exit;
}

$postingan_id = $_POST['postingan_id'];
$user_id = $_SESSION['userid'];

// Begin transaction
mysqli_begin_transaction($koneksi);

try {
    // Check if already liked
    $query_cek = "SELECT * FROM likes_postingan WHERE postingan_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $query_cek);
    mysqli_stmt_bind_param($stmt, "is", $postingan_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0) {
        // Already liked, remove like
        $query = "DELETE FROM likes_postingan WHERE postingan_id = ? AND user_id = ?";
    } else {
        // Not liked yet, add like
        $query = "INSERT INTO likes_postingan (postingan_id, user_id) VALUES (?, ?)";
    }

    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "is", $postingan_id, $user_id);
    $success = mysqli_stmt_execute($stmt);

    // Get updated like count
    $query_count = "SELECT COUNT(*) as like_count FROM likes_postingan WHERE postingan_id = ?";
    $stmt_count = mysqli_prepare($koneksi, $query_count);
    mysqli_stmt_bind_param($stmt_count, "i", $postingan_id);
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $row = mysqli_fetch_assoc($result_count);
    $like_count = $row['like_count'];

    // Commit transaction
    mysqli_commit($koneksi);

    echo json_encode([
        'success' => true,
        'like_count' => $like_count,
        'message' => 'Operation successful'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($koneksi);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Close connection
mysqli_close($koneksi);