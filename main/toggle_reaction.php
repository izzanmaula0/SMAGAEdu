<?php
session_start();
require 'koneksi.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log incoming request data
error_log("POST data received: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

// Check for user login
if(!isset($_SESSION['userid'])) {
    error_log("Error: User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Validate postingan_id
if(!isset($_POST['postingan_id']) || empty($_POST['postingan_id'])) {
    error_log("Error: Missing or empty postingan_id. POST data: " . print_r($_POST, true));
    echo json_encode(['success' => false, 'message' => 'Missing postingan_id']);
    exit;
}

// Get and validate input data
$postingan_id = filter_var($_POST['postingan_id'], FILTER_VALIDATE_INT);
$user_id = $_SESSION['userid'];
$emoji = isset($_POST['emoji']) ? $_POST['emoji'] : '👍';

// Validate postingan_id after filtering
if($postingan_id === false) {
    error_log("Error: Invalid postingan_id format: " . $_POST['postingan_id']);
    echo json_encode(['success' => false, 'message' => 'Invalid postingan_id format']);
    exit;
}

error_log("Processing reaction - Post ID: $postingan_id, User ID: $user_id, Emoji: $emoji");

mysqli_begin_transaction($koneksi);

try {
    // Check if user already reacted
    $check_query = "SELECT * FROM emoji_reactions WHERE postingan_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($stmt, "is", $postingan_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0) {
        // Update existing reaction
        $update_query = "UPDATE emoji_reactions SET emoji = ? WHERE postingan_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($koneksi, $update_query);
        mysqli_stmt_bind_param($stmt, "sis", $emoji, $postingan_id, $user_id);
    } else {
        // Insert new reaction
        $insert_query = "INSERT INTO emoji_reactions (postingan_id, user_id, emoji) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $insert_query);
        mysqli_stmt_bind_param($stmt, "iss", $postingan_id, $user_id, $emoji);
    }

    $execute_result = mysqli_stmt_execute($stmt);
    
    if(!$execute_result) {
        throw new Exception("Failed to save reaction: " . mysqli_error($koneksi));
    }

    // Get updated reaction counts
    $count_query = "SELECT emoji, COUNT(*) as count 
                    FROM emoji_reactions 
                    WHERE postingan_id = ? 
                    GROUP BY emoji 
                    ORDER BY count DESC";
    $stmt = mysqli_prepare($koneksi, $count_query);
    mysqli_stmt_bind_param($stmt, "i", $postingan_id);
    mysqli_stmt_execute($stmt);
    $reactions_result = mysqli_stmt_get_result($stmt);
    
    $reactions = [];
    while($row = mysqli_fetch_assoc($reactions_result)) {
        $reactions[$row['emoji']] = intval($row['count']);
    }

    mysqli_commit($koneksi);
    
    error_log("Success - Returning reactions: " . print_r($reactions, true));
    
    echo json_encode([
        'success' => true,
        'reactions' => $reactions,
        'currentEmoji' => $emoji
    ]);

} catch (Exception $e) {
    error_log("Error occurred: " . $e->getMessage());
    mysqli_rollback($koneksi);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($koneksi);
?>