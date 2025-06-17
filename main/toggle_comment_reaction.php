<?php
session_start();
require 'koneksi.php';

if(!isset($_SESSION['userid']) || !isset($_POST['comment_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$comment_id = $_POST['comment_id'];
$user_id = $_SESSION['userid'];
$emoji = isset($_POST['emoji']) ? $_POST['emoji'] : '👍';

try {
    // Handle reaction toggle
    $check_query = "SELECT * FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($stmt, "is", $comment_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE comment_reactions SET emoji = ? WHERE comment_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($stmt, "sis", $emoji, $comment_id, $user_id);
    } else {
        $insert_query = "INSERT INTO comment_reactions (comment_id, user_id, emoji) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $insert_query);
        mysqli_stmt_bind_param($stmt, "iss", $comment_id, $user_id, $emoji);
    }
    mysqli_stmt_execute($stmt);

    // Get updated reaction counts
    $count_query = "SELECT emoji, COUNT(*) as count FROM comment_reactions WHERE comment_id = ? GROUP BY emoji";
    $stmt = mysqli_prepare($koneksi, $count_query);
    mysqli_stmt_bind_param($stmt, "i", $comment_id);
    mysqli_stmt_execute($stmt);
    $reactions = mysqli_stmt_get_result($stmt);

    $reaction_counts = [];
    while($row = mysqli_fetch_assoc($reactions)) {
        $reaction_counts[$row['emoji']] = $row['count'];
    }

    echo json_encode([
        'success' => true,
        'reactions' => $reaction_counts
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>