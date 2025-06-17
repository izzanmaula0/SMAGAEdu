<?php
include 'includes/session_config.php';
require_once 'groq_config_ujian.php';

header('Content-Type: application/json');

// Validasi session terlebih dahulu
if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Session tidak ditemukan', 'redirect' => 'index.php']);
    exit();
}

if (!validateGroqConfig()) {
    http_response_code(500);
    echo json_encode(['error' => 'Groq API belum dikonfigurasi']);
    exit();
}

// Debug request method
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Content Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
error_log('Raw Input: ' . file_get_contents('php://input'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method tidak diperbolehkan', 
        'received_method' => $_SERVER['REQUEST_METHOD'],
        'expected' => 'POST'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Debug input data
error_log('Parsed Input: ' . print_r($input, true));

$exam_data = $input['exam_data'] ?? null;

if (!$exam_data) {
    http_response_code(400);
    echo json_encode(['error' => 'Data ujian tidak ditemukan']);
    exit();
}


// Siapkan prompt untuk AI
$prompt = "Kamu adalah asisten AI yang membantu menganalisis hasil ujian siswa, JANGAN PERNAH SEBUTKAN NILAI SISWA BERUPA ANGKA, JANGAN PERNAH SEBUTKAN NILAI SISWA APAPUN YANG TERJADI. 

Data Ujian:
- Mata Pelajaran: {$exam_data['ujian']['mata_pelajaran']}
- Judul Ujian: {$exam_data['ujian']['judul']}
- Nama Siswa: {$exam_data['siswa']['nama']}";

$total_soal = $exam_data['hasil']['total_soal'];
$benar = $exam_data['hasil']['benar'];
$persentase = ($benar / $total_soal) * 100;

// Kategorikan performa
if ($persentase >= 80) {
    $kategori = "sangat baik";
} elseif ($persentase >= 70) {
    $kategori = "baik";
} elseif ($persentase >= 60) {
    $kategori = "cukup";
} else {
    $kategori = "perlu perbaikan";
}

$prompt = "Kamu adalah asisten motivasi belajar. ATURAN KETAT: 
1. DILARANG menyebutkan angka, nilai, skor, persentase apapun
2. DILARANG menghitung atau menebak nilai
3. Fokus hanya pada motivasi dan saran belajar
4. Jika ditanya nilai, jawab: 'Saya tidak membahas angka nilai'


Data Ujian:
- Mata Pelajaran: {$exam_data['ujian']['mata_pelajaran']}
- Judul Ujian: {$exam_data['ujian']['judul']}
- Nama Siswa: {$exam_data['siswa']['nama']}
- Performa: $kategori
Analisis jawaban:
";

// // Tambahkan detail jawaban dengan format ringkas untuk menghemat token
if (!empty($exam_data['jawaban_detail'])) {
    $soal_benar = [];
    $soal_salah = [];

    foreach ($exam_data['jawaban_detail'] as $index => $jawaban) {
        $soal_singkat = substr($jawaban['pertanyaan'], 0, 50) . "...";
        $jawaban_siswa = $jawaban['jawaban_siswa'] ?? 'tidak dijawab';
        $jawaban_benar = $jawaban['jawaban_benar'];

        // Bandingkan dengan case insensitive
        if (strtolower(trim($jawaban_siswa)) === strtolower(trim($jawaban_benar))) {
            $soal_benar[] = $soal_singkat;
        } else {
            $soal_salah[] = $soal_singkat . " (dijawab: $jawaban_siswa)";
        }
    }

    // Acak urutan soal agar AI tidak bisa tebak pola
    $semua_soal = [];
    foreach ($soal_benar as $soal) {
        $semua_soal[] = ['soal' => $soal, 'status' => 'dikuasai'];
    }
    foreach ($soal_salah as $soal) {
        $semua_soal[] = ['soal' => $soal, 'status' => 'perlu_dipelajari'];
    }

    // Acak urutan
    shuffle($semua_soal);

    $prompt .= "Analisis per topik (urutan acak):\n";
    foreach ($semua_soal as $item) {
        if ($item['status'] === 'dikuasai') {
            $prompt .= "✓ Topik: " . $item['soal'] . " - sudah dikuasai\n";
        } else {
            $prompt .= "• Topik: " . $item['soal'] . " - perlu dipelajari lagi\n";
        }
    }
} else {
    $prompt .= "Detail jawaban tidak tersedia, analisis berdasarkan statistik umum.\n";
}

$prompt .= "
Berikan analisis yang ramah dan mendukung untuk siswa ini. Fokus pada:

1. KELEBIHAN: Jelaskan apa yang sudah dikuasai siswa dengan baik berdasarkan jawaban benar
2. AREA PENGEMBANGAN: Jelaskan materi apa yang perlu dipelajari lagi berdasarkan jawaban salah (tanpa menyebutkan jawaban yang benar)
3. SARAN BELAJAR: Berikan tips konkret untuk meningkatkan pemahaman
4. PERHATIAN : Jangan sebutkan nilai dalam bentuk angka atau persentase, fokus pada proses belajar dan motivasi siswa. JANGAN PERNAH SEBUTKAN NILAI SISWA BERUPA ANGKA, JANGAN PERNAH SEBUTKAN NILAI SISWA APAPUN YANG TERJADI.\
5. KOMUNIKASI : Gunakan Aku dan Kamu untuk membuat analisis terasa personal dan dekat.
Gunakan bahasa yang positif, motivasi, dan mudah dipahami siswa. Panjang respon sekitar 200-300 kata.";

try {
    $data = [
        'model' => GROQ_MODEL,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Kamu adalah guru AI yang membantu siswa menganalisis hasil ujian mereka dengan cara yang positif dan memotivasi.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.7,
        'max_tokens' => 1000
    ];

    error_log('=== DEBUG PROMPT ===');
    error_log($prompt);
    error_log('=== END DEBUG ===');

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
        'analysis' => $result['choices'][0]['message']['content']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menganalisis: ' . $e->getMessage()]);
}
