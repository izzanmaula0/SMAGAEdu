<?php
session_start();
require "koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userid = $_SESSION['userid'];
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';

if (empty($message)) {
    echo json_encode(['error' => 'Message required']);
    exit();
}

// Check usage limit
$stmt = mysqli_prepare($koneksi, "SELECT ai_usage_count, ai_usage_date FROM siswa WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usage = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$today = date('Y-m-d');
if ($usage['ai_usage_date'] === NULL || $usage['ai_usage_date'] !== $today) {
    $reset_stmt = mysqli_prepare($koneksi, "UPDATE siswa SET ai_usage_count = 0, ai_usage_date = ? WHERE username = ?");
    mysqli_stmt_bind_param($reset_stmt, "ss", $today, $userid);
    mysqli_stmt_execute($reset_stmt);
    mysqli_stmt_close($reset_stmt);
    $usage['ai_usage_count'] = 0;
}

if ($usage['ai_usage_count'] >= 20) {
    echo json_encode(['error' => 'Batas penggunaan AI hari ini sudah tercapai (20/20)']);
    exit();
}

// Grog AI API configuration
$api_key = 'YOUR_API_KEY'; // Ganti dengan API key Groq Anda
$api_url = 'https://api.groq.com/openai/v1/chat/completions';

$data = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => 'namamu adalah SMAGA AI dan kamu adalah seorang guru AI
        yang senang membantu siswa, jika mereka meminta jawaban ujian maka jangan di kasih, tetap profesional,
        kamu saat ini bekerja di SMP Muhammadiyah 2 Gatak dan SMA Muhammadiyah 5 Gatak, jawablah sesederhana dan sesimpel mungkin'],
        ['role' => 'user', 'content' => $message]
    ],
    'temperature' => 0.7
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

function formatAIResponse($text)
{
    // Pastikan ada spasi setelah tanda baca
    $text = preg_replace('/([.!?])(\w)/', '$1 $2', $text);

    // Pastikan ada spasi setelah koma
    $text = preg_replace('/,(\w)/', ', $1', $text);

    // Ganti multiple newlines dengan maksimal 2 newlines
    $text = preg_replace('/\n{3,}/', "\n\n", $text);

    // Konversi newlines ke <br> untuk HTML
    $text = nl2br($text);

    return $text;
}

if ($http_code === 200) {
    $result = json_decode($response, true);
    $ai_response = $result['choices'][0]['message']['content'];

    // Format respons AI
    $formatted_response = formatAIResponse($ai_response);

    // Update usage count and save chat
    $update_stmt = mysqli_prepare($koneksi, "UPDATE siswa SET ai_usage_count = ai_usage_count + 1 WHERE username = ?");
    mysqli_stmt_bind_param($update_stmt, "s", $userid);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);

    $chat_stmt = mysqli_prepare($koneksi, "INSERT INTO ai_chat_history (user_id, pesan, respons) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($chat_stmt, "sss", $userid, $message, $formatted_response);
    mysqli_stmt_execute($chat_stmt);
    mysqli_stmt_close($chat_stmt);

    echo json_encode(['response' => $formatted_response]);
} else {
    echo json_encode(['error' => 'AI service error', 'details' => $response]);
}
