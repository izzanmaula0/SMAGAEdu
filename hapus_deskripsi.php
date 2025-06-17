<?php
session_start();
require "koneksi.php";

// Cek akses
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Cek request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Ambil data dari request
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['description_id']) || !is_numeric($data['description_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid description ID']);
    exit();
}

$description_id = $data['description_id'];
// Tambahkan parameter baru untuk menentukan apakah soal terkait akan dihapus
$delete_linked_soal = isset($data['delete_linked_soal']) && $data['delete_linked_soal'] === true;

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    if ($delete_linked_soal) {
        // Hapus soal-soal yang terkait dengan deskripsi
        $query = "DELETE FROM bank_soal WHERE description_id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $description_id);
        $stmt->execute();
    } else {
        // Hapus tautan dari soal (tetap pertahankan soalnya)
        $query = "UPDATE bank_soal SET description_id = NULL WHERE description_id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $description_id);
        $stmt->execute();
    }
    
    // Hapus deskripsi
    $query = "DELETE FROM soal_descriptions WHERE id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $description_id);
    $stmt->execute();
    
    // Commit transaksi
    mysqli_commit($koneksi);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Deskripsi berhasil dihapus', 
        'soal_deleted' => $delete_linked_soal
    ]);
} catch (Exception $e) {
    // Rollback jika terjadi error
    mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>