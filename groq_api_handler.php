<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$prompt = $data['prompt'];
$api_key = $data['api_key'];
$parse_json = $data['parse_json'] ?? false;

// Groq API endpoint
$url = 'https://api.groq.com/openai/v1/chat/completions';

$postData = [
    'model' => 'llama-3.3-70b-versatile', // atau model Groq lainnya
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Anda adalah asisten AI yang membantu guru dalam menyempurnakan soal ujian.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 1000
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memanggil Groq API']);
    exit();
}

$result = json_decode($response, true);
$content = $result['choices'][0]['message']['content'] ?? '';

if ($parse_json) {
    // Coba parse JSON dari response
    preg_match('/\{.*\}/s', $content, $matches);
    if (!empty($matches[0])) {
        $content = json_decode($matches[0], true);
    }
}

echo json_encode(['status' => 'success', 'content' => $content]);
?>