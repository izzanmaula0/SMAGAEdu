<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Pastikan request method adalah POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Hanya menerima request POST");
    }

    // Baca data JSON dari request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validasi input
    if (empty($data['project_name']) || empty($data['content'])) {
        throw new Exception("Nama project dan konten wajib diisi");
    }

    // Sanitasi input
    $projectName = trim($data['project_name']);
    $content = trim($data['content']);

    // Simpan ke database
    $query = "INSERT INTO simple_projects 
             (user_id, project_name, content)
             VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param(
        $stmt, 
        "sss", 
        $_SESSION['userid'],
        $projectName,
        $content
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menyimpan: " . mysqli_error($koneksi));
    }

    // Response sukses
    echo json_encode([
        'success' => true,
        'id' => mysqli_insert_id($koneksi)
    ]);

} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}