<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userid = $_SESSION['userid'];

$query = "SELECT n.*, p.konten as post_content, 
          CASE WHEN k.nama_kelas IS NOT NULL AND k.nama_kelas != '' THEN k.nama_kelas ELSE k.mata_pelajaran END as nama_kelas,
          CASE WHEN g.namaLengkap IS NOT NULL THEN g.namaLengkap ELSE s.nama END as pelaku_nama,
          CASE 
            WHEN s.photo_type = 'avatar' AND s.photo_url IS NOT NULL THEN s.photo_url
            WHEN s.photo_type = 'upload' AND s.foto_profil IS NOT NULL THEN CONCAT('uploads/profil/', s.foto_profil)
            WHEN g.foto_profil IS NOT NULL THEN CONCAT('uploads/profil/', g.foto_profil)
            ELSE 'assets/pp.png' 
          END as foto_profil
          FROM notifikasi n
          JOIN postingan_kelas p ON n.postingan_id = p.id
          JOIN kelas k ON n.kelas_id = k.id
          LEFT JOIN guru g ON n.pelaku_id = g.username
          LEFT JOIN siswa s ON n.pelaku_id = s.username
          WHERE n.penerima_id = ? AND n.sudah_dibaca = 0
          ORDER BY n.waktu DESC
          LIMIT 10";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Format waktu yang lebih user-friendly
    $timestamp = strtotime($row['waktu']);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        $time_ago = "Baru saja";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        $time_ago = "$minutes menit yang lalu";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        $time_ago = "$hours jam yang lalu";
    } else {
        $days = floor($diff / 86400);
        $time_ago = "$days hari yang lalu";
    }
    
    $row['waktu_formatted'] = $time_ago;
    
    // Format konten post (potong jika terlalu panjang)
    $row['post_content_short'] = (strlen($row['post_content']) > 50) ? 
                                substr($row['post_content'], 0, 50) . '...' : 
                                $row['post_content'];
    
    // Nama kelas
    $row['nama_kelas'] = !empty($row['nama_kelas']) ? $row['nama_kelas'] : $row['mata_pelajaran'];
    
    $notifications[] = $row;
}

// Hitung total notifikasi yang belum dibaca
$count_query = "SELECT COUNT(*) as total FROM notifikasi WHERE penerima_id = ? AND sudah_dibaca = 0";
$count_stmt = mysqli_prepare($koneksi, $count_query);
mysqli_stmt_bind_param($count_stmt, "s", $userid);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);

$response = [
    'notifications' => $notifications,
    'total_unread' => (int)$count_row['total']
];

echo json_encode($response);
?>