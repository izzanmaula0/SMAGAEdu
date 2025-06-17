<?php
session_start();
require "koneksi.php";

// Tangkap data dari request
$data = json_decode(file_get_contents('php://input'), true);

// Pastikan user sudah login dan data lengkap
if (!isset($_SESSION['userid']) || !isset($data['pesan']) || !isset($data['respons'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$user_id = $_SESSION['userid'];
$pesan = $data['pesan'];
$respons = $data['respons'];
// Ambil nilai uses_canvas jika ada, defaultnya 0
$uses_canvas = isset($data['uses_canvas']) ? (int)$data['uses_canvas'] : 0;

// Cek apakah ada session chat aktif
if (!isset($_SESSION['active_chat_session'])) {
    // Jika tidak ada, buat session baru
    $query = "INSERT INTO ai_chat_sessions (user_id, title, created_at) 
              VALUES (?, ?, NOW())";
    
    $title = substr($pesan, 0, 30) . (strlen($pesan) > 30 ? '...' : '');
    
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $user_id, $title);
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        echo json_encode(['success' => false, 'error' => 'Failed to create session']);
        exit;
    }
    
    $_SESSION['active_chat_session'] = mysqli_insert_id($koneksi);
} else {
    // Verifikasi bahwa session masih valid di database
    $session_id = $_SESSION['active_chat_session'];
    $check_query = "SELECT id FROM ai_chat_sessions WHERE id = ?";
    $check_stmt = mysqli_prepare($koneksi, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'i', $session_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        // Session tidak valid, buat baru
        $query = "INSERT INTO ai_chat_sessions (user_id, title, created_at) 
                  VALUES (?, ?, NOW())";
        
        $title = substr($pesan, 0, 30) . (strlen($pesan) > 30 ? '...' : '');
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $user_id, $title);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            echo json_encode(['success' => false, 'error' => 'Failed to create new session']);
            exit;
        }
        
        $_SESSION['active_chat_session'] = mysqli_insert_id($koneksi);
    }
}

// Simpan pesan dengan session ID yang benar
$session_id = $_SESSION['active_chat_session'];

// Tambahkan uses_canvas ke query
$query = "INSERT INTO ai_chat_messages (ai_chat_sessions_id, pesan, respons, created_at, uses_canvas) 
          VALUES (?, ?, ?, NOW(), ?)";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'issi', $session_id, $pesan, $respons, $uses_canvas);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    echo json_encode([
        'success' => true, 
        'session_id' => $session_id
    ]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($koneksi)]);
}
?>