<?php
session_start();
require "koneksi.php";

// Validasi session dan level user
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    die(json_encode(['success' => false, 'error' => 'Unauthorized access']));
}

// Validasi input
$data = json_decode(file_get_contents('php://input'), true);
$projectId = $data['project_id'] ?? null;

if (!$projectId) {
    die(json_encode(['success' => false, 'error' => 'Invalid project ID']));
}

// Mulai transaction
mysqli_begin_transaction($koneksi);

try {
    // Hapus knowledge terlebih dahulu
    $queryDeleteKnowledge = "DELETE FROM project_knowledge WHERE project_id = ?";
    $stmt = mysqli_prepare($koneksi, $queryDeleteKnowledge);
    mysqli_stmt_bind_param($stmt, "i", $projectId);
    mysqli_stmt_execute($stmt);
    
    // Hapus project
    $queryDeleteProject = "DELETE FROM projects WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($koneksi, $queryDeleteProject);
    mysqli_stmt_bind_param($stmt, "is", $projectId, $_SESSION['userid']);
    mysqli_stmt_execute($stmt);
    
    // Commit transaction
    mysqli_commit($koneksi);
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction jika error
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}