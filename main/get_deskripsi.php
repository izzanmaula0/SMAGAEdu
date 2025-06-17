<?php
session_start();
require "koneksi.php";

// Cek akses
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Cek request parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid description ID']);
    exit();
}

$description_id = $_GET['id'];

try {
    // Ambil data deskripsi
    $query = "SELECT * FROM soal_descriptions WHERE id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $description_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Deskripsi tidak ditemukan']);
        exit();
    }
    
    $deskripsi = $result->fetch_assoc();
    
    // Ambil soal yang terkait
    $query = "SELECT id FROM bank_soal WHERE description_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("i", $description_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $soal_terkait = [];
    while ($row = $result->fetch_assoc()) {
        $soal_terkait[] = (int)$row['id'];
    }
    
    echo json_encode([
        'status' => 'success', 
        'deskripsi' => $deskripsi,
        'soal_terkait' => $soal_terkait
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>