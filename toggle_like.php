<?php
session_start();
require 'koneksi.php';
require 'create_notification.php';
header('Content-Type: application/json; charset=utf8mb4');

// Ensure proper character encoding
mysqli_set_charset($koneksi, "utf8mb4");

// Basic validation
if(!isset($_SESSION['userid'])) {
    error_log("Error: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if(!isset($_POST['postingan_id'])) {
    error_log("Error: Missing postingan_id");
    echo json_encode(['success' => false, 'message' => 'Missing postingan_id']);
    exit;
}

$postingan_id = $_POST['postingan_id'];
$user_id = $_SESSION['userid'];
$emoji = isset($_POST['emoji']) ? $_POST['emoji'] : '👍';

mysqli_begin_transaction($koneksi);

try {
    // Check if user already reacted
    $check_query = "SELECT emoji FROM emoji_reactions WHERE postingan_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($stmt, "is", $postingan_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $existing_reaction = mysqli_fetch_assoc($result);

    $isNewReaction = false; // Flag to track if this is a new reaction

    if($existing_reaction) {
        if($existing_reaction['emoji'] === $emoji) {
            // If clicking the same emoji, remove the reaction (unlike)
            $delete_query = "DELETE FROM emoji_reactions WHERE postingan_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($koneksi, $delete_query);
            mysqli_stmt_bind_param($stmt, "is", $postingan_id, $user_id);
            mysqli_stmt_execute($stmt);
        } else {
            // If clicking different emoji, update the reaction
            $update_query = "UPDATE emoji_reactions SET emoji = ? WHERE postingan_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($koneksi, $update_query);
            mysqli_stmt_bind_param($stmt, "sis", $emoji, $postingan_id, $user_id);
            mysqli_stmt_execute($stmt);
            $isNewReaction = true; // Consider emoji change as a new reaction for notification
        }
    } else {
        // If no existing reaction, add new one
        $insert_query = "INSERT INTO emoji_reactions (postingan_id, user_id, emoji) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $insert_query);
        mysqli_stmt_bind_param($stmt, "iss", $postingan_id, $user_id, $emoji);
        mysqli_stmt_execute($stmt);
        $isNewReaction = true; // This is a new reaction
    }

    // Get updated reaction counts
    $get_reactions = "SELECT emoji, COUNT(*) as count 
                     FROM emoji_reactions 
                     WHERE postingan_id = ? 
                     GROUP BY emoji";
    $stmt = mysqli_prepare($koneksi, $get_reactions);
    mysqli_stmt_bind_param($stmt, "i", $postingan_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $reactions = [];
    $total = 0;
    $emoji_stack = [];

    while($row = mysqli_fetch_assoc($result)) {
        $reactions[$row['emoji']] = (int)$row['count'];
        $total += $row['count'];
        $emoji_stack[] = $row['emoji'];
    }

    // Get user's current reaction after update
    $current_reaction_query = "SELECT emoji FROM emoji_reactions 
                             WHERE postingan_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $current_reaction_query);
    mysqli_stmt_bind_param($stmt, "is", $postingan_id, $user_id);
    mysqli_stmt_execute($stmt);
    $current_reaction = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Add notification logic here if this is a new reaction
    if ($isNewReaction) {
        // Get post owner (the teacher)
        $query_post_owner = "SELECT p.user_id, p.kelas_id FROM postingan_kelas p WHERE p.id = ?";
        $stmt = mysqli_prepare($koneksi, $query_post_owner);
        mysqli_stmt_bind_param($stmt, "i", $postingan_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $post_owner = $row['user_id'];
            $kelas_id = $row['kelas_id'];
            
            // Only create notification if the liker is not the post owner
            if ($user_id != $post_owner) {
                // Create notification
                createNotification($koneksi, $post_owner, 'like', $postingan_id, $user_id, $kelas_id);
            }
        }
    }

    mysqli_commit($koneksi);

    echo json_encode([
        'success' => true,
        'total' => $total,
        'emoji_stack' => array_slice($emoji_stack, 0, 3),
        'reactions' => $reactions,
        'current_emoji' => $current_reaction ? $current_reaction['emoji'] : null,
        'is_liked' => $current_reaction ? true : false
    ]);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    error_log("Error in toggle_like: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your reaction'
    ]);
}

mysqli_close($koneksi);
?>