<?php
session_start();
require "koneksi.php";

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get post_id from parameter
$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($post_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

// Pastikan header content-type diset dengan benar
header('Content-Type: application/json');

try {
    // Query to get users who reacted to the post
    // We need to join with both guru and siswa tables to get user information
    $query = "SELECT er.emoji, er.user_id,
              g.namaLengkap as guru_name, g.foto_profil as guru_foto,
              s.nama as siswa_name, s.foto_profil as siswa_foto, s.photo_url, s.photo_type,
              CASE WHEN g.username IS NOT NULL THEN 'guru' ELSE 'siswa' END as user_type
              FROM emoji_reactions er
              LEFT JOIN guru g ON er.user_id = g.username
              LEFT JOIN siswa s ON er.user_id = s.username AND g.username IS NULL
              WHERE er.postingan_id = ?
              ORDER BY COALESCE(g.namaLengkap, s.nama) ASC";

    $stmt = mysqli_prepare($koneksi, $query);

    if (!$stmt) {
        throw new Exception('Query preparation failed: ' . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $user_type = $row['user_type'];
        
        // Determine the user's name and photo based on type
        if ($user_type == 'guru') {
            $nama = $row['guru_name'];
            $foto_profil = !empty($row['guru_foto']) ? 'uploads/profil/' . $row['guru_foto'] : 'assets/pp.png';
        } else {
            $nama = $row['siswa_name'];
            
            // Handle different photo types for students
            if (!empty($row['photo_url']) && $row['photo_type'] === 'avatar') {
                $foto_profil = $row['photo_url'];
            } elseif (!empty($row['siswa_foto']) && $row['photo_type'] === 'upload') {
                $foto_profil = 'uploads/profil/' . $row['siswa_foto'];
            } else {
                $foto_profil = 'assets/pp.png';
            }
        }
        
        // Add user to results
        $users[] = [
            'emoji' => $row['emoji'],
            'user_id' => $row['user_id'],
            'nama' => $nama,
            'foto_profil' => $foto_profil,
            'user_type' => $user_type
        ];
    }

    echo json_encode($users);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>