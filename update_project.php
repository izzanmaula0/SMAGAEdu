<?php
header('Content-Type: application/json');
require "koneksi.php";

$project_id = $_POST['project_id'] ?? '';
$project_name = $_POST['project_name'] ?? '';
$description = $_POST['description'] ?? '';

if (!$project_id || !$project_name) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // Update project details
    $query = "UPDATE projects SET project_name = ?, description = ? WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'ssi', $project_name, $description, $project_id);
    mysqli_stmt_execute($stmt);

    // Delete old contents
    mysqli_query($koneksi, "DELETE FROM project_knowledge WHERE project_id = $project_id");

    // Add new contents
    if (isset($_POST['knowledge'])) {
        foreach ($_POST['knowledge'] as $knowledge) {
            if ($knowledge['type'] === 'text') {
                $query = "INSERT INTO project_knowledge (project_id, content_type, content) VALUES (?, 'text', ?)";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, 'is', $project_id, $knowledge['content']);
                mysqli_stmt_execute($stmt);
            } else if ($knowledge['type'] === 'file' && isset($_FILES['knowledge']['name'][$i])) {
                $file = $_FILES['knowledge']['tmp_name'][$i];
                $filename = $_FILES['knowledge']['name'][$i];
                $filepath = 'uploads/projects/' . time() . '_' . $filename;
                
                move_uploaded_file($file, $filepath);
                
                $query = "INSERT INTO project_knowledge (project_id, content_type, file_path) VALUES (?, 'file', ?)";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, 'is', $project_id, $filepath);
                mysqli_stmt_execute($stmt);
            }
        }
    }

    mysqli_commit($koneksi);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}