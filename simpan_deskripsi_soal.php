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

// Ambil data dari form
$ujian_id = $_POST['ujian_id'];
$title = $_POST['title'];
$content = $_POST['content'];
$selected_soal = isset($_POST['selected_soal']) ? $_POST['selected_soal'] : [];
$description_id = isset($_POST['description_id']) && !empty($_POST['description_id']) ? $_POST['description_id'] : null;

// Validasi data
if (empty($title) || empty($content) || empty($selected_soal)) {
    echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi']);
    exit();
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    if ($description_id) {
        // Mode edit: Update deskripsi yang sudah ada
        $query = "UPDATE soal_descriptions SET title = ?, content = ? WHERE id = ? AND ujian_id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("ssis", $title, $content, $description_id, $ujian_id);
        $stmt->execute();
        
        // Reset semua tautan terlebih dahulu
        $query = "UPDATE bank_soal SET description_id = NULL WHERE description_id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $description_id);
        $stmt->execute();
    } else {
        // Mode tambah: Simpan deskripsi baru
        $query = "INSERT INTO soal_descriptions (ujian_id, title, content) VALUES (?, ?, ?)";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("iss", $ujian_id, $title, $content);
        $stmt->execute();
        
        // Ambil ID deskripsi yang baru saja disimpan
        $description_id = $stmt->insert_id;
    }
    
    // Update soal-soal yang dipilih
    foreach ($selected_soal as $soal_id) {
        $query = "UPDATE bank_soal SET description_id = ? WHERE id = ? AND ujian_id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("iii", $description_id, $soal_id, $ujian_id);
        $stmt->execute();
    }
    
    // Commit transaksi
    mysqli_commit($koneksi);
    
    echo json_encode(['status' => 'success', 'message' => 'Deskripsi soal berhasil disimpan']);
} catch (Exception $e) {
    // Rollback jika terjadi error
    mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>