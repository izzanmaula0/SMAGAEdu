<?php
header('Content-Type: application/json');
require "koneksi.php";

$project_id = $_GET['id'] ?? '';

if (!$project_id) {
    echo json_encode(['success' => false, 'error' => 'Project ID required']);
    exit;
}

$query = "SELECT p.*, pk.content_type, pk.content, pk.file_path 
          FROM projects p 
          LEFT JOIN project_knowledge pk ON p.id = pk.project_id 
          WHERE p.id = ?";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'i', $project_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$project = null;
$contents = [];

while ($row = mysqli_fetch_assoc($result)) {
    if (!$project) {
        $project = [
            'id' => $row['id'],
            'project_name' => $row['project_name'],
            'description' => $row['description']
        ];
    }
    if ($row['content_type']) {
        $contents[] = [
            'type' => $row['content_type'],
            'content' => $row['content'],
            'file_path' => $row['file_path']
        ];
    }
}

echo json_encode([
    'success' => true,
    'project' => $project,
    'contents' => $contents
]);