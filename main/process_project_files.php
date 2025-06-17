<?php
session_start();
require "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $upload_dir = 'uploads/projects/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $processedFiles = [];
    
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['files']['name'][$key];
        $unique_name = uniqid() . '_' . $file_name;
        $file_path = $upload_dir . $unique_name;
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
        
        if (move_uploaded_file($tmp_name, $file_path)) {
            // Insert file info into project_files
            $query = "INSERT INTO project_files (project_id, file_name, file_path, file_type) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("isss", $project_id, $file_name, $file_path, $file_type);
            
            if ($stmt->execute()) {
                // Extract content based on file type and update project context
                $content = "";
                
                if ($file_type === 'pdf') {
                    // Add PDF content extraction logic
                    $content = "PDF content from " . $file_name;
                } elseif (in_array($file_type, ['doc', 'docx'])) {
                    // Add Word document content extraction logic
                    $content = "DOCX content from " . $file_name;
                }
                
                // Update project context with extracted content
                $updateQuery = "UPDATE project_context 
                              SET context = CONCAT(IFNULL(context,''), ?) 
                              WHERE id = ?";
                $updateStmt = $koneksi->prepare($updateQuery);
                $updateStmt->bind_param("si", $content, $project_id);
                $updateStmt->execute();
                
                $processedFiles[] = [
                    'name' => $file_name,
                    'path' => $file_path,
                    'type' => $file_type
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => !empty($processedFiles),
        'files' => $processedFiles
    ]);
}
?>