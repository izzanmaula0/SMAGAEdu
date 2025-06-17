<?php
header('Content-Type: application/json');

try {
    $host = 'localhost';
    $dbname = 'smagaedu';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_POST['siswa_id']) || !isset($_POST['photo_type'])) {
        throw new Exception('Missing required parameters');
    }

    $siswa_id = $_POST['siswa_id'];
    $photo_type = $_POST['photo_type'];
    $upload_dir = 'uploads/profil/';

    // Ambil data foto lama
    $query = "SELECT photo_type, photo_url, foto_profil FROM siswa WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$siswa_id]);
    $old_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($photo_type === 'upload') {
        if (!isset($_FILES['photo'])) {
            throw new Exception('No photo uploaded');
        }

        $file = $_FILES['photo'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed with error code: ' . $file['error']);
        }

        $filename = uniqid('profile_') . '.jpg';
        $filepath = $upload_dir . $filename;

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }

        // Hapus foto lama jika ada
        if ($old_data['photo_type'] === 'upload' && !empty($old_data['foto_profil'])) {
            $old_file = $upload_dir . $old_data['foto_profil'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Update database - hapus avatar url dan set foto baru
        $query = "UPDATE siswa SET 
                  photo_type = 'upload', 
                  photo_url = NULL, 
                  foto_profil = ? 
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$filename, $siswa_id]);

    } else if ($photo_type === 'avatar') {
        if (!isset($_POST['avatar_url'])) {
            throw new Exception('No avatar URL provided');
        }

        $avatar_url = $_POST['avatar_url'];

        if (!str_starts_with($avatar_url, 'https://api.dicebear.com/')) {
            throw new Exception('Invalid avatar URL');
        }

        // Hapus foto upload lama jika ada
        if ($old_data['photo_type'] === 'upload' && !empty($old_data['foto_profil'])) {
            $old_file = $upload_dir . $old_data['foto_profil'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }

        // Update database - hapus foto profil dan set avatar baru
        $query = "UPDATE siswa SET 
                  photo_type = 'avatar', 
                  photo_url = ?, 
                  foto_profil = NULL 
                  WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$avatar_url, $siswa_id]);

    } else {
        throw new Exception('Invalid photo type');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profile photo updated successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}