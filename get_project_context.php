<?php
require "koneksi.php";
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

if (!isset($_GET['project_id'])) {
    die(json_encode(['success' => false, 'error' => 'Project ID required']));
}

$projectId = $_GET['project_id'];
$userId = $_SESSION['userid'];

// Ambil detail project
$query = "SELECT * FROM projects WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "is", $projectId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die(json_encode(['success' => false, 'error' => 'Project not found']));
}

$project = mysqli_fetch_assoc($result);

// Ambil konten pengetahuan
$contents = []; // Inisialisasi array kosong
$queryContent = "SELECT * FROM project_knowledge WHERE project_id = ?";
$stmtContent = mysqli_prepare($koneksi, $queryContent);
mysqli_stmt_bind_param($stmtContent, "i", $projectId);
mysqli_stmt_execute($stmtContent);
$resultContent = mysqli_stmt_get_result($stmtContent);

while ($row = mysqli_fetch_assoc($resultContent)) {
    // Gunakan nama field sesuai database
    $contents[] = [
        'content_type' => $row['content_type'],
        'content' => $row['content'],
        'file_path' => $row['file_path']
    ];
}

echo json_encode([
    'success' => true,
    'project_name' => $project['project_name'],
    'contents' => $contents
]);
?>