<?php
session_start();
require "koneksi.php";
header('Content-Type: application/json');

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah_soal = intval($_POST['jumlah_soal']);
    $tipe_soal = $_POST['tipe_soal'];
    $ujian_id = intval($_POST['ujian_id']);
    $mata_pelajaran = $_POST['mata_pelajaran'];
    $tingkat = $_POST['tingkat'];

    // Ambil data materi dari database
    $query = "SELECT materi FROM ujian WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $ujian_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ujian = mysqli_fetch_assoc($result);

    $materi_list = [];
    if (!empty($ujian['materi'])) {
        $materi_list = json_decode($ujian['materi'], true);
    }

    $kesulitan = $_POST['kesulitan'];

    // Update prompt generation
    $prompt = "Buatkan {$jumlah_soal} soal " .
        ($tipe_soal == 'pilihan_ganda' ? "pilihan ganda" : "uraian") .
        " untuk mata pelajaran {$mata_pelajaran} kelas {$tingkat} dengan tingkat kesulitan " .
        strtoupper($kesulitan) . ".\n\n";

    // Add difficulty guidelines based on level
    // Ganti match dengan switch
    switch ($kesulitan) {
        case 'mudah':
            $difficulty_guidelines = "- Gunakan konsep dasar dan pengetahuan faktual
                    - Soal bersifat ingatan dan pemahaman sederhana
                    - Jawaban mudah ditemukan dalam materi dasar";
            break;
        case 'sedang':
            $difficulty_guidelines = "- Gabungkan beberapa konsep dasar
                    - Butuh analisis sederhana
                    - Memerlukan pemahaman konseptual";
            break;
        case 'sulit':
            $difficulty_guidelines = "- Gunakan analisis kompleks
                    - Kombinasikan multiple konsep
                    - Membutuhkan pemecahan masalah
                    - Aplikasi konsep dalam konteks baru";
            break;
        case 'sangat_sulit':
            $difficulty_guidelines = "- Membutuhkan analisis sangat mendalam
                        - Evaluasi dan sintesis konsep
                        - Penalaran tingkat tinggi
                        - Pemecahan masalah kompleks";
            break;
        default:
            $difficulty_guidelines = "";
            break;
    }

    $prompt .= "\nPanduan tingkat kesulitan:\n" . $difficulty_guidelines . "\n\n";

    //  error log untuk promt
    error_log("Prompt buat jumlah soal, tipe soal, dan mata pelajaran: " . $prompt);

    // Tambahkan daftar materi ke prompt
    if (!empty($materi_list)) {
        $prompt .= "Materi yang harus dicakup:\n";
        foreach ($materi_list as $materi) {
            $prompt .= "- " . $materi . "\n";
        }
        $prompt .= "\n";
    }

    if (!empty($_POST['custom_prompt'])) {
        $prompt = $_POST['custom_prompt'];
    } else {
        if ($tipe_soal == 'pilihan_ganda') {
            $prompt .= "format yang diinginkan:
                1. setiap soal harus memiliki 4 pilihan jawaban (A, B, C, D)
                2. sertakan kunci jawaban untuk setiap soal
                3. jawaban harus bervariasi (tidak selalu A atau B)
                4. tingkat kesulitan soal harus bervariasi
                5. soal harus sesuai dengan tingkat pemahaman siswa kelas {$tingkat}
                
                WAJIB IKUTI FORMAT INI DENGAN SANGAT TEPAT:
                
                Soal: [pertanyaan tanpa nomor]
                A. [jawaban]
                B. [jawaban] 
                C. [jawaban]
                D. [jawaban]
                Jawaban: [A/B/C/D]
                ---
                
                Contoh:
                Soal: Berapakah hasil dari 5 x 5?
                A. 15
                B. 20
                C. 25
                D. 30
                Jawaban: C
                ---
                
                ATURAN PENTING:
                - JANGAN GUNAKAN PENOMORAN PADA SOAL (1., 2., dst)
                - DILARANG menggunakan format lain (tidak boleh ada '**', 'Soal 1:', dll)
                - WAJIB gunakan separator '---' di antara soal
                - WAJIB mulai tiap soal dengan kata 'Soal:' (tanpa nomor)
                - DILARANG memberikan komentar atau teks tambahan";
        } else {
            $prompt .= "Format yang diinginkan:
                1. Soal harus dalam bentuk uraian yang menguji pemahaman
                2. Tingkat kesulitan soal harus bervariasi
                3. Soal harus sesuai dengan tingkat pemahaman siswa kelas {$tingkat}
                
                Berikan output dengan format TEPAT seperti ini untuk setiap soal:
                1. [tuliskan pertanyaan pertama]
                2. [tuliskan pertanyaan kedua]
                (dan seterusnya)
                
                Contoh format output yang diharapkan:
                1. Jelaskan proses fotosintesis pada tumbuhan dan sebutkan faktor-faktor yang mempengaruhinya!
                2. Mengapa mata pelajaran matematika penting dalam kehidupan sehari-hari? Berikan contoh penerapannya!";
        }
    }
    try {
        // Setup API Groq
        $apiKey = 'YOUR_API_KEY';  // Ganti dengan API key Anda
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $soal_generated = [];

        // Lakukan request ke API
        $data = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah asisten AI yang ahli dalam membuat soal ujian.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7
        ];

        $options = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];

        // Inisialisasi cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Hati-hati dengan opsi ini di production
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        // Eksekusi request
        $result = curl_exec($ch);

        // Cek error cURL
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        // Tutup koneksi cURL
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Cek status HTTP
        if ($httpCode != 200) {
            error_log("HTTP Error: " . $httpCode);
            error_log("Response: " . $result);
            throw new Exception("HTTP request failed with status $httpCode");
        }

        // Parsing JSON response
        $response = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        // Pastikan struktur response Groq
        if (!isset($response['choices'][0]['message']['content'])) {
            error_log("Full API Response: " . print_r($response, true));
            throw new Exception('Unexpected API response format from Groq');
        }

        $generated_text = $response['choices'][0]['message']['content'];
        error_log("Generated Text: " . $generated_text);

        if ($tipe_soal == 'pilihan_ganda') {
            // Bersihkan format yang tidak perlu
            $generated_text = preg_replace('/\*\*.*?\*\*\n?/', '', $generated_text);
            $generated_text = trim($generated_text);

            // Split berdasarkan "Soal:"
            $soal_array = preg_split('/(?=Soal:\s*[^0-9])/', $generated_text, -1, PREG_SPLIT_NO_EMPTY);
            $soal_array = array_map('trim', $soal_array);

            $soal_array = array_slice($soal_array, 0, $jumlah_soal);

            // Debug info
            error_log("=== Debug Split Soal ===");
            error_log("Text asli: " . $generated_text);
            error_log("Jumlah soal setelah split: " . count($soal_array));

            foreach ($soal_array as $index => $soal) {
                error_log("=== Soal " . ($index + 1) . " ===");
                error_log($soal);
            }

            foreach ($soal_array as $index => $soal_text) {
                // Parse tiap komponen soal
                if (
                    preg_match('/Soal:\s*(.*?)(?=A\.|$)/s', $soal_text, $pertanyaan) &&
                    preg_match('/A\.\s*(.*?)(?=B\.|$)/s', $soal_text, $jawaban_a) &&
                    preg_match('/B\.\s*(.*?)(?=C\.|$)/s', $soal_text, $jawaban_b) &&
                    preg_match('/C\.\s*(.*?)(?=D\.|$)/s', $soal_text, $jawaban_c) &&
                    preg_match('/D\.\s*(.*?)(?=Jawaban:|$)/s', $soal_text, $jawaban_d) &&
                    preg_match('/Jawaban:\s*([ABCD])/i', $soal_text, $jawaban_benar)
                )

                    // Debug info
                    error_log("=== Debug Parsing Soal " . ($index + 1) . " ===");
                error_log("Pertanyaan: " . ($pertanyaan[1] ?? 'tidak ditemukan'));
                error_log("Jawaban A: " . ($jawaban_a[1] ?? 'tidak ditemukan'));
                error_log("Jawaban B: " . ($jawaban_b[1] ?? 'tidak ditemukan'));
                error_log("Jawaban C: " . ($jawaban_c[1] ?? 'tidak ditemukan'));
                error_log("Jawaban D: " . ($jawaban_d[1] ?? 'tidak ditemukan'));
                error_log("Jawaban Benar: " . ($jawaban_benar[1] ?? 'tidak ditemukan'));

                if (
                    empty($pertanyaan[1]) || empty($jawaban_a[1]) || empty($jawaban_b[1]) ||
                    empty($jawaban_c[1]) || empty($jawaban_d[1]) || empty($jawaban_benar[1])
                ) {
                    error_log("Soal tidak lengkap, skip...");
                    continue;
                }

                error_log("Processing Soal " . ($index + 1));
                error_log("Soal Text: " . $soal_text); // Tambahkan ini

                if (!empty($pertanyaan[1])) {
                    // Prepare query dan bind parameters
                    $query = "INSERT INTO bank_soal (ujian_id, jenis_soal, pertanyaan, jawaban_a, jawaban_b, jawaban_c, jawaban_d, jawaban_benar) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($koneksi, $query);

                    $pertanyaan_clean = trim($pertanyaan[1]);
                    $jawaban_a_clean = trim($jawaban_a[1] ?? '');
                    $jawaban_b_clean = trim($jawaban_b[1] ?? '');
                    $jawaban_c_clean = trim($jawaban_c[1] ?? '');
                    $jawaban_d_clean = preg_replace('/\s*Jawaban:\s*[ABCD].*$/i', '', trim($jawaban_d[1] ?? ''));
                    $jawaban_benar_clean = trim($jawaban_benar[1] ?? 'A');

                    mysqli_stmt_bind_param(
                        $stmt,
                        "isssssss",
                        $ujian_id,
                        $tipe_soal,
                        $pertanyaan_clean,
                        $jawaban_a_clean,
                        $jawaban_b_clean,
                        $jawaban_c_clean,
                        $jawaban_d_clean,
                        $jawaban_benar_clean
                    );

                    mysqli_stmt_execute($stmt);

                    // Tambahkan ke array untuk response
                    $soal_generated[] = [
                        'pertanyaan' => $pertanyaan_clean,
                        'jawaban_a' => $jawaban_a_clean,
                        'jawaban_b' => $jawaban_b_clean,
                        'jawaban_c' => $jawaban_c_clean,
                        'jawaban_d' => $jawaban_d_clean,
                        'jawaban_benar' => $jawaban_benar_clean
                    ];
                }
            }
        } else {
            // Untuk soal uraian
            // Split teks menjadi array soal (asumsikan setiap soal dipisahkan dengan nomor)
            preg_match_all('/\d+\.\s+(.*?)(?=\d+\.|$)/s', $generated_text, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $soal_text) {
                    $pertanyaan = trim($soal_text);

                    if (!empty($pertanyaan)) {
                        // Prepare query dan bind parameters
                        $query = "INSERT INTO bank_soal (ujian_id, jenis_soal, pertanyaan) VALUES (?, ?, ?)";
                        $stmt = mysqli_prepare($koneksi, $query);

                        mysqli_stmt_bind_param(
                            $stmt,
                            "iss",
                            $ujian_id,
                            $tipe_soal,
                            $pertanyaan
                        );

                        mysqli_stmt_execute($stmt);

                        // Tambahkan ke array untuk response
                        $soal_generated[] = [
                            'pertanyaan' => $pertanyaan
                        ];
                    }
                }
            }
        }

        error_log("Total Soal Generated: " . count($soal_generated));
        echo json_encode([
            'status' => 'success',
            'message' => 'Berhasil generate ' . count($soal_generated) . ' soal',
            'data' => $soal_generated
        ]);
    } catch (Exception $e) {
        error_log("Error: in generate_multiple_soal.php:" . $e->getMessage());
        error_log("Full Response: " . print_r($result, true)); // Tambahkan ini
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'debug' => $result // Tambahkan ini untuk debugging
        ]);
        exit();
    }
}
