<?php
session_start();
require "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['project_id'];
    $upload_dir = 'uploads/projects/';
    
    // Allowed file types
    $allowed_types = ['pdf', 'doc', 'docx', 'txt'];
    $max_file_size = 10 * 1024 * 1024; // 10MB
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $files = [];
    $errors = [];
    
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['files']['name'][$key];
        $file_size = $_FILES['files']['size'][$key];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validate file type and size
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type for $file_name";
            continue;
        }
        
        if ($file_size > $max_file_size) {
            $errors[] = "File $file_name exceeds 10MB limit";
            continue;
        }
        
        $unique_filename = uniqid() . '_' . $file_name;
        $file_path = $upload_dir . $unique_filename;
        
        if (move_uploaded_file($tmp_name, $file_path)) {
            $query = "INSERT INTO project_files (project_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("isss", $project_id, $file_name, $file_path, $file_type);
            $stmt->execute();
            
            $files[] = [
                'name' => $file_name,
                'path' => $file_path
            ];
        } else {
            $errors[] = "Failed to move uploaded file: $file_name";
        }
    }
    
    echo json_encode([
        'success' => empty($errors),
        'files' => $files,
        'errors' => $errors
    ]);
}