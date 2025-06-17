<?php
require "koneksi.php";
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$projectId = $_POST['project_id'];
$projectName = $_POST['project_name'];
$description = $_POST['description'];
$userId = $_SESSION['userid'];

// Update project utama
$stmt = $koneksi->prepare("UPDATE projects SET project_name = ?, description = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("ssis", $projectName, $description, $projectId, $userId);
$stmt->execute();

// Hapus konten lama
$koneksi->query("DELETE FROM project_contents WHERE project_id = $projectId");

// Simpan konten baru
foreach ($_POST['knowledge'] as $index => $knowledge) {
    $contentType = $knowledge['type'];
    
    if ($contentType === 'text') {
        $content = $knowledge['content'];
        $stmt = $koneksi->prepare("INSERT INTO project_contents (project_id, content_type, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $projectId, $contentType, $content);
        $stmt->execute();
    } elseif ($contentType === 'file' && isset($_FILES['knowledge']['tmp_name'][$index]['file'])) {
        $file = $_FILES['knowledge']['tmp_name'][$index]['file'];
        $fileName = $_FILES['knowledge']['name'][$index]['file'];
        $uploadDir = 'uploads/project_files/';
        $filePath = $uploadDir . uniqid() . '_' . $fileName;
        
        if (move_uploaded_file($file, $filePath)) {
            $stmt = $koneksi->prepare("INSERT INTO project_contents (project_id, content_type, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $projectId, $contentType, $filePath);
            $stmt->execute();
        }
    }
}

echo json_encode(['success' => true]);
?>