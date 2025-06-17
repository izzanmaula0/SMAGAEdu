<?php
include 'includes/session_config.php';
require_once 'groq_config_ujian.php';

header('Content-Type: application/json');

if (!validateGroqConfig()) {
    http_response_code(500);
    echo json_encode(['error' => 'Groq API belum dikonfigurasi']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak diperbolehkan']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$exam_context = $input['exam_context'] ?? null;

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Pesan tidak boleh kosong']);
    exit();
}

// Siapkan context dari ujian
// Siapkan context dari ujian
$context = "Kamu adalah asisten AI yang membantu siswa belajar. ";

if ($exam_context) {
    // Informasi dasar ujian
    $context .= "Siswa baru saja mengerjakan ujian '{$exam_context['ujian']['judul']}' tentang {$exam_context['ujian']['mata_pelajaran']}. ";

    // Informasi hasil ujian (tanpa menyebutkan angka nilai ke siswa)
    $total_soal = $exam_context['hasil']['total_soal'];
    $benar = $exam_context['hasil']['benar'];
    $salah = $exam_context['hasil']['salah'];

    $context .= "Dari $total_soal soal: $benar dijawab benar, $salah dijawab salah. ";

    // Detail topik yang sudah dikuasai dan perlu diperbaiki
    if (!empty($exam_context['jawaban_detail'])) {
        $topik_dikuasai = [];
        $topik_perlu_perbaikan = [];

        foreach ($exam_context['jawaban_detail'] as $jawaban) {
            $topik = substr($jawaban['pertanyaan'], 0, 60) . "...";

            // Bandingkan jawaban dengan case insensitive
            if (strtolower(trim($jawaban['jawaban_siswa'])) === strtolower(trim($jawaban['jawaban_benar']))) {
                $topik_dikuasai[] = $topik;
            } else {
                $topik_perlu_perbaikan[] = $topik . " (dijawab: {$jawaban['jawaban_siswa']})";
            }
        }

        if (!empty($topik_dikuasai)) {
            $context .= "Topik yang sudah dikuasai: " . implode(", ", $topik_dikuasai) . ". ";
        }

        if (!empty($topik_perlu_perbaikan)) {
            $context .= "Topik yang perlu dipelajari lagi: " . implode(", ", $topik_perlu_perbaikan) . ". ";
        }
    }

    $context .= "ATURAN PENTING: Jika ditanya nilai angka, katakan 'Aku tidak membahas nilai dalam bentuk angka, tapi bisa bantu jelaskan materi yang perlu dipelajari'. ";
}

$context .= "Jawab pertanyaan siswa dengan ramah menggunakan 'aku' dan 'kamu', fokus pada pembelajaran dan motivasi.";

try {
    $data = [
        'model' => GROQ_MODEL,
        'messages' => [
            [
                'role' => 'system',
                'content' => $context
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 800
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, GROQ_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Error dari Groq API: ' . $response);
    }

    $result = json_decode($response, true);

    if (!$result || !isset($result['choices'][0]['message']['content'])) {
        throw new Exception('Response AI tidak valid');
    }

    echo json_encode([
        'success' => true,
        'response' => $result['choices'][0]['message']['content']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memproses chat: ' . $e->getMessage()]);
}
