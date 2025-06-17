<?php
session_start();
require "koneksi.php";
require "groq_helper.php";

// Debug logs
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    error_log("Authorization failed: userid=" . isset($_SESSION['userid']) . ", level=" . ($_SESSION['level'] ?? 'not set'));
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

if(isset($_POST['message'])) {
    error_log("Message received: " . $_POST['message']);
    
    $userId = $_SESSION['userid'];
    $message = $_POST['message'];
    $mapel = isset($_POST['mapel']) ? $_POST['mapel'] : 'umum';

    // Format prompt untuk Groq
    $prompt = "Saya adalah siswa yang ingin belajar $mapel. $message";
    error_log("Prompt: " . $prompt);
    
    try {
        // Dapatkan respons dari Groq
        $response = getGroqResponse($prompt);
        error_log("Groq response: " . ($response ?? 'null'));
        
        // Jika tidak dapat respons dari Groq, gunakan respons default
        if (!$response) {
            $response = "Maaf, saya sedang mengalami kendala teknis. Bisa diulangi pertanyaannya?";
        }

        // Simpan ke database
        $query = "INSERT INTO ai_chat_history (user_id, pesan, respons, character_traits) VALUES (?, ?, ?, ?)";
        $character_traits = json_encode([
            "collaborative" => 0,
            "analytical" => 0,
            "detail_oriented" => 0
        ]);
        
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $userId, $message, $response, $character_traits);
        
        if(mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'response' => $response,
                'timestamp' => date('H:i')
            ]);
        } else {
            error_log("Database error: " . mysqli_error($koneksi));
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan chat'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("No message in POST data");
    echo json_encode([
        'success' => false,
        'message' => 'Tidak ada pesan'
    ]);
}