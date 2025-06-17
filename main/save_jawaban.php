<?php
include 'includes/session_config.php'; // Ganti session_start() dengan ini untuk konsistensi
require "koneksi.php";

// Validasi session terlebih dahulu
if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session not found']);
    exit();
}

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Ambil data dari POST
$ujian_id = $_POST['ujian_id'] ?? null;
$soal_index = $_POST['soal_index'] ?? null;
$jawaban = isset($_POST['jawaban']) ? $_POST['jawaban'] : null; // Dapat null jika user clear jawaban

// Handle session ping - jangan simpan ke database
// Handle session ping - jangan simpan ke database
if ($soal_index == -999 && $jawaban === 'session_ping') {
    $session_info = getSessionInfo();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Session kept alive',
        'time' => date('Y-m-d H:i:s'),
        'userid' => $_SESSION['userid'],
        'session_info' => [
            'age_hours' => round($session_info['session_age'] / 3600, 1),
            'last_activity_minutes' => round($session_info['last_activity'] / 60, 1),
            'status' => 'active_rolling',
            'note' => 'Session akan diperpanjang selama user aktif'
        ]
    ]);
    exit();
}

// Validasi data untuk operasi normal
if ($ujian_id === null || $soal_index === null) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit();
}

// Ambil ID siswa dari session username
$query_siswa = "SELECT id FROM siswa WHERE username = ?";
$stmt_siswa = $koneksi->prepare($query_siswa);

if (!$stmt_siswa) {
    echo json_encode(['success' => false, 'error' => 'Database prepare error: ' . $koneksi->error]);
    exit();
}

$stmt_siswa->bind_param("s", $_SESSION['userid']);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();
$siswa = $result_siswa->fetch_assoc();

if (!$siswa) {
    echo json_encode(['success' => false, 'error' => 'Siswa tidak ditemukan']);
    exit();
}

$siswa_id = $siswa['id'];

// Ambil soal ID berdasarkan index (urutan) dari session
if (!isset($_SESSION['soal_order_' . $ujian_id][$soal_index])) {
    echo json_encode(['success' => false, 'error' => 'Soal tidak ditemukan']);
    exit();
}

$soal_id = $_SESSION['soal_order_' . $ujian_id][$soal_index];

try {
    // Cek apakah jawaban sudah ada untuk soal ini
    $query_check = "SELECT id FROM jawaban_sementara WHERE ujian_id = ? AND siswa_id = ? AND soal_id = ?";
    $stmt_check = $koneksi->prepare($query_check);
    
    if (!$stmt_check) {
        throw new Exception('Database prepare error: ' . $koneksi->error);
    }
    
    $stmt_check->bind_param("iii", $ujian_id, $siswa_id, $soal_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Jawaban sudah ada, update
        if ($jawaban === null) {
            // Jika jawaban null (dihapus/clear), hapus dari database
            $query_delete = "DELETE FROM jawaban_sementara WHERE ujian_id = ? AND siswa_id = ? AND soal_id = ?";
            $stmt_delete = $koneksi->prepare($query_delete);
            
            if (!$stmt_delete) {
                throw new Exception('Database prepare error: ' . $koneksi->error);
            }
            
            $stmt_delete->bind_param("iii", $ujian_id, $siswa_id, $soal_id);
            $stmt_delete->execute();
        } else {
            // Update jawaban
            $query_update = "UPDATE jawaban_sementara SET jawaban = ?, timestamp = NOW() WHERE ujian_id = ? AND siswa_id = ? AND soal_id = ?";
            $stmt_update = $koneksi->prepare($query_update);
            
            if (!$stmt_update) {
                throw new Exception('Database prepare error: ' . $koneksi->error);
            }
            
            $stmt_update->bind_param("siii", $jawaban, $ujian_id, $siswa_id, $soal_id);
            $stmt_update->execute();
        }
    } else if ($jawaban !== null) {
        // Jawaban belum ada dan bukan null, insert baru
        $query_insert = "INSERT INTO jawaban_sementara (ujian_id, siswa_id, soal_id, jawaban, timestamp) 
                        VALUES (?, ?, ?, ?, NOW())";
        $stmt_insert = $koneksi->prepare($query_insert);
        
        if (!$stmt_insert) {
            throw new Exception('Database prepare error: ' . $koneksi->error);
        }
        
        $stmt_insert->bind_param("iiis", $ujian_id, $siswa_id, $soal_id, $jawaban);
        $stmt_insert->execute();
    }
    
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'session_remaining' => getSessionRemainingTime()
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>