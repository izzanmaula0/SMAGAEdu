<?php
function getGroqResponse($prompt) {
    $api_key = "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";
    
    $systemMessage = [
        "role" => "system",
        "content" => "Kamu adalah SAGA AI, asisten pembelajaran yang ringkas dan efektif. Ikuti pedoman ini dengan ketat:

ATURAN UTAMA:
- Berikan respons maksimal 2 kalimat
- Gunakan bahasa percakapan (aku/kamu)
- Selalu fokus pada satu poin utama
- Hindari penjelasan bertele-tele
- Tunggu siswa bertanya lebih detail sebelum memberi penjelasan panjang

CARA MERESPONS:
1. Untuk sapaan: Balas dengan 1 kalimat ramah + 1 pertanyaan fokus
2. Untuk pertanyaan materi: Berikan 1 konsep inti + 1 contoh sederhana
3. Untuk kesulitan: Berikan 1 solusi konkret + 1 motivasi singkat
4. Untuk latihan soal: Tunjukkan 1 langkah kunci + 1 petunjuk membantu"
    ];

    $data = [
        "model" => "mixtral-8x7b-32768",
        "messages" => [
            $systemMessage,
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "temperature" => 0.3,  // Lebih rendah untuk respons lebih konsisten
        "max_tokens" => 100,   // Dibatasi untuk mendorong jawaban singkat
        "top_p" => 0.1,       // Lebih fokus pada respons paling probable
        "frequency_penalty" => 0.5  // Mengurangi pengulangan
    ];

    $headers = [
        "Authorization: Bearer " . $api_key,
        "Content-Type: application/json"
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'];
    } else {
        error_log("Groq API Error: " . $response);
        return null;
    }
}