<?php
header('Content-Type: application/json');
require "koneksi.php";

// API key Gemini
$apiKey = 'AIzaSyAm6yuSvkKYnjmlqor8HjciqFiFAwahUgM';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

// Terima parameter dari request
$jenis_soal = $_POST['jenis_soal'] ?? 'tidak ada';
$ujian_id = $_POST['ujian_id'] ?? 'tidak ada';
$mata_pelajaran = $_POST['mata_pelajaran'] ?? 'tidak ada';
$tingkat = $_POST['tingkat'] ?? 'tidak ada';

// Debug: Print masing-masing parameter
error_log("jenis_soal: " . $jenis_soal);
error_log("ujian_id: " . $ujian_id);
error_log("mata_pelajaran: " . $mata_pelajaran);
error_log("tingkat: " . $tingkat);

// Validasi parameter dengan pesan lebih spesifik
if(empty($jenis_soal) || $jenis_soal == 'tidak ada') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter jenis_soal tidak ada'
    ]);
    exit;
}
if(empty($ujian_id) || $ujian_id == 'tidak ada') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter ujian_id tidak ada'
    ]);
    exit;
}
if(empty($mata_pelajaran) || $mata_pelajaran == 'tidak ada') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter mata_pelajaran tidak ada'
    ]);
    exit;
}
if(empty($tingkat) || $tingkat == 'tidak ada') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Parameter tingkat tidak ada'
    ]);
    exit;
}

// After your existing parameter validations, add:
$query = "SELECT materi FROM ujian WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $ujian_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ujian = mysqli_fetch_assoc($result);

$materi_list = [];
if(!empty($ujian['materi'])) {
    $materi_list = json_decode($ujian['materi'], true);
    $materi_text = "Materi yang harus dicakup:\n" . implode("\n", array_map(fn($m) => "- $m", $materi_list)) . "\n\n";
} else {
    $materi_text = "";
}

// Buat prompt berdasarkan jenis soal
if($jenis_soal == 'pilihan_ganda') {
    $prompt = "Kamu adalah seorang guru profesional untuk mata pelajaran $mata_pelajaran.
    Buatkan 1 soal pilihan ganda dengan:
    - Mata pelajaran: $mata_pelajaran  
    - Tingkat kelas: $tingkat
    $materi_text
    - Jawaban benar harus dipilih secara acak (A/B/C/D)
    - Setiap pilihan jawaban harus logis
    - Hindari opsi 'semua benar/salah'

    Output dalam format JSON tanpa ada teks tambahan:
    {
        \"pertanyaan\": \"Contoh pertanyaan?\",
        \"jawaban_a\": \"Pilihan A\",
        \"jawaban_b\": \"Pilihan B\", 
        \"jawaban_c\": \"Pilihan C\",
        \"jawaban_d\": \"Pilihan D\",
        \"jawaban_benar\": \"[A-D]\"
    }";
} else {
    $prompt = "Kamu adalah seorang guru profesional untuk mata pelajaran $mata_pelajaran.
    Buatkan 1 soal uraian yang sesuai dengan:
    - mata pelajaran: $mata_pelajaran
    - tingkat kelas: $tingkat
    - pastikan berbasis kurikulum merdeka
    - memiliki tingkat kesulitan yang sesuai
    - pertanyaan harus mendorong siswa untuk berpikir analitis
    - hindari pertanyaan yang bisa dijawab hanya dengan 'ya' atau 'tidak'
    - jangan menghalu, fokus pada tujuan di atas
    
    berikan output dalam format JSON persis seperti contoh ini, tanpa komentar atau teks tambahan:
    {
        \"pertanyaan\": \"Contoh pertanyaan uraian?\"
    }";
}

// Data untuk dikirim ke API
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

// Inisialisasi cURL
$ch = curl_init($url . '?key=' . $apiKey);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Eksekusi request
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['status' => 'error', 'message' => $err]);
    exit;
}

// Parse response dari Gemini
$result = json_decode($response, true);

try {
    // Ambil teks dari response
    $generated_text = $result['candidates'][0]['content']['parts'][0]['text'];
    
    // Bersihkan string dari karakter yang tidak diinginkan
    $generated_text = preg_replace('/```json\s*|\s*```/', '', trim($generated_text));
    
    $soal = json_decode($generated_text, true);
    
    // Convert jawaban_benar ke uppercase jika ada
    if(isset($soal['jawaban_benar'])) {
        $soal['jawaban_benar'] = strtoupper($soal['jawaban_benar']);
    }
    
    if(json_last_error() === JSON_ERROR_NONE) {
        echo json_encode(['status' => 'success', 'data' => $soal]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Failed to parse generated content',
            'json_error' => json_last_error_msg(),
            'raw_text' => $generated_text
        ]);
    }
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>