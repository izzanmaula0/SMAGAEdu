<?php
session_start();
require "koneksi.php";

// Cek session dan level user
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    die(json_encode(['error' => 'Akses ditolak']));
}

// Proses file upload
$uploadDir = 'uploads/projects/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Simpan project utama terlebih dahulu
$projectName = $_POST['project_name'] ?? '';
$description = $_POST['description'] ?? '';
$userId = $_SESSION['userid'];

// Validasi input
if(empty($projectName)) {
    die(json_encode(['error' => 'Nama project wajib diisi']));
}


// Insert project ke database
$queryProject = "INSERT INTO projects (user_id, project_name, description) 
                VALUES (?, ?, ?)";
$stmtProject = mysqli_prepare($koneksi, $queryProject);
mysqli_stmt_bind_param($stmtProject, "sss", $userId, $projectName, $description);

if (!mysqli_stmt_execute($stmtProject)) {
    die(json_encode(['error' => 'Gagal menyimpan project']));
}

// Dapatkan ID project yang baru dibuat
$projectId = mysqli_insert_id($koneksi);

// Proses knowledge jika ada
if (isset($_POST['knowledge']) && is_array($_POST['knowledge'])) {
    foreach ($_POST['knowledge'] as $index => $knowledge) {
        $type = $knowledge['type'] ?? 'text';
        
        if ($type === 'text') {
            // Handle text content
            $content = $knowledge['content'] ?? '';
            
            $query = "INSERT INTO project_knowledge 
                     (project_id, content_type, content)
                     VALUES (?, 'text', ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "is", $projectId, $content);
            mysqli_stmt_execute($stmt);
        } else {
            // Handle file upload
            if (isset($_FILES['knowledge']['tmp_name'][$index]['file'])) {
                $fileTmp = $_FILES['knowledge']['tmp_name'][$index]['file'];
                $fileName = uniqid() . '_' . $_FILES['knowledge']['name'][$index]['file'];
                $fileType = $_FILES['knowledge']['type'][$index]['file'];
                
                if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
                    $query = "INSERT INTO project_knowledge 
                             (project_id, content_type, file_path, file_type)
                             VALUES (?, 'file', ?, ?)";
                    $stmt = mysqli_prepare($koneksi, $query);
                    mysqli_stmt_bind_param($stmt, "iss", $projectId, $uploadDir . $fileName, $fileType);
                    mysqli_stmt_execute($stmt);
                }
            }
        }
    }
}

echo json_encode(['success' => true]);