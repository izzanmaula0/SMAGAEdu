<?php
session_start();
require "koneksi.php";


if(!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

// Ambil userid dari session
$userid = $_SESSION['userid'];


// Ambil data guru
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Fungsi untuk menyimpan chat ke database
function saveChat($userId, $message, $response) {
    global $koneksi;
    
    $userId = mysqli_real_escape_string($koneksi, $userId);
    $message = mysqli_real_escape_string($koneksi, $message);
    $response = mysqli_real_escape_string($koneksi, $response);
    
    $query = "INSERT INTO ai_chat_history (user_id, pesan, respons) 
              VALUES ('$userId', '$message', '$response')";
              
    return mysqli_query($koneksi, $query);
}

// Fungsi untuk mengambil riwayat chat
function getChatHistory($userId) {
    global $koneksi;
    
    $userId = mysqli_real_escape_string($koneksi, $userId);
    $query = "SELECT * FROM ai_chat_history 
              WHERE user_id = '$userId' 
              ORDER BY created_at DESC 
              LIMIT 10";
              
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}



// Ambil data siswa
$userid = $_SESSION['userid'];
$query = "SELECT * FROM siswa WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
$query_kelas = "SELECT k.*, g.namaLengkap as nama_guru 
                FROM kelas k 
                JOIN kelas_siswa ks ON k.id = ks.kelas_id 
                JOIN guru g ON k.guru_id = g.username
                JOIN siswa s ON ks.siswa_id = s.id
                WHERE s.username = ? AND ks.is_archived = 0";


$stmt_kelas = mysqli_prepare($koneksi, $query_kelas);
mysqli_stmt_bind_param($stmt_kelas, "s", $userid);
mysqli_stmt_execute($stmt_kelas);
$result_kelas = mysqli_stmt_get_result($stmt_kelas);



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <title>Beranda - SMAGAEdu</title>
</head>
<style>
        body{ 
            font-family: merriweather;
        }
        .color-web {
            background-color: rgb(218, 119, 86);
        }

</style>
<body>
    

<?php include 'includes/styles.php'; ?>

<div class="container-fluid">
        <div class="row">
            <!-- Sidebar for desktop -->
            <?php include 'includes/sidebar_siswa.php'; ?>

            <!-- Mobile navigation -->
            <?php include 'includes/mobile_nav siswa.php'; ?>

            <!-- Settings Modal -->
            <?php include 'includes/settings_modal.php'; ?>

            
        </div>
    </div> 
    <style>
        .col-utama {
            padding-left: 14rem !important;
        }
        @media screen and (max-width: 992px) {
            .col-utama {
                padding-left: 0 !important;
            }
            
        }
    </style>   

<!-- Main Content Container -->
<div class="col-utama container-fluid p-0">
    <div class="row g-0 flex-column flex-lg-row">
        <!-- Main Chat Section -->
        <div class="col-12 col-lg-8 d-flex flex-column min-vh-100">
            <!-- Chat Header -->
            <div class="p-2 p-sm-3 border-bottom bg-white">
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                        <i class="bi bi-stars text-primary fs-6"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">SAGA AI</h6>
                        <p class="text-muted small mb-0 d-none d-sm-block">Mode: Matematika - Integral</p>
                    </div>
                    <div class="ms-auto">
                        <select class="form-select form-select-sm border-0 bg-light" style="max-width: 200px;">
                            <option>Pilih Mapel</option>
                            <option>Matematika</option>
                            <option>Fisika</option>
                            <option>Kimia</option>
                            <option>Biologi</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Voice Interaction Area -->
            <div class="flex-grow-1 d-flex flex-column">
                <div class="p-3 p-sm-4 text-center" style="background-color: #f8f9fa;">
                    <div class="voice-indicator mb-3">
                        <div class="audio-waves mb-3">
                            <div class="wave-circle bg-primary bg-opacity-10 mx-auto" 
                                style="width: 150px; height: 150px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <button id="voiceButton" class="btn btn-primary rounded-circle p-3" 
                                        style="width: 80px; height: 80px;">
                                    <i class="bi bi-mic-fill"></i>
                                </button>
                            </div>
                        </div>
                        <div id="statusText" class="status-text">
                            <h6 class="mb-2">Tekan untuk Berbicara</h6>
                            <p class="text-muted small mb-0">SAGA AI siap mendengarkan</p>
                        </div>
                    </div>

                    <!-- Quick Prompts -->
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button class="btn btn-light rounded-pill btn-sm">
                            <i class="bi bi-lightbulb me-1"></i>Jelaskan integral
                        </button>
                        <button class="btn btn-light rounded-pill btn-sm">
                            <i class="bi bi-question-circle me-1"></i>Contoh soal
                        </button>
                        <button class="btn btn-light rounded-pill btn-sm">
                            <i class="bi bi-book me-1"></i>Latihan
                        </button>
                    </div>
                </div>

                <!-- Conversation Transcript -->
                <div class="flex-grow-1 overflow-auto p-3 border-top">
                    <h6 class="mb-3 text-muted">Transkrip Percakapan</h6>
                    
                    <!-- AI Message -->
                    <div class="d-flex gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 align-self-start" style="width: 32px; height: 32px;">
                            <i class="bi bi-robot text-primary small"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="card border-0 bg-light rounded-3 p-2">
                                <p class="small mb-0">Hai! Saya siap membantumu belajar. Silakan bertanya tentang materi yang ingin kamu pelajari.</p>
                            </div>
                            <small class="text-muted ms-2">11:30</small>
                        </div>
                    </div>

                    <!-- User Message -->
                    <div class="d-flex gap-3 mb-3">
                        <!-- hasil  -->
                    </div>
                </div>

                
            </div>
        </div>

        <!-- Right Panel -->
        <div class="col-12 col-lg-4 border-start bg-white">
            <div class="p-3">
                <!-- Whiteboard Section -->
                <div class="mb-4">
                    <h6 class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-pencil-square"></i>
                        Papan Tulis Digital
                    </h6>
                    <div class="border rounded-3 p-2" style="min-height: 200px;">
                        <canvas id="whiteboard" class="w-100 h-100 border rounded"></canvas>
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light"><i class="bi bi-eraser"></i></button>
                            <button class="btn btn-sm btn-light"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Materials Section -->
                <div class="mb-4">
                    <h6 class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-book"></i>
                        Materi Pembelajaran
                    </h6>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-file-pdf text-danger"></i>
                            Integral Dasar
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-file-pdf text-danger"></i>
                            Integral Substitusi
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-file-pdf text-danger"></i>
                            Integral Parsial
                        </a>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="mb-4">
                    <h6 class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-journal-text"></i>
                        Ringkasan
                    </h6>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Terakhir diupdate: 5 menit lalu</small>
                                <button class="btn btn-sm btn-light"><i class="bi bi-download"></i></button>
                            </div>
                            <p class="card-text small">
                                Rangkuman otomatis akan muncul di sini berdasarkan percakapan dengan AI.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const voiceButton = document.getElementById('voiceButton');
    const statusText = document.getElementById('statusText');
    let isRecording = false;
    
    // Check if browser supports speech recognition
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        statusText.innerHTML = `
            <h6 class="mb-2 text-danger">Browser Tidak Mendukung</h6>
            <p class="text-muted small mb-0">Silakan gunakan browser modern</p>
        `;
        voiceButton.disabled = true;
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'id-ID';
    recognition.interimResults = false;

    voiceButton.addEventListener('click', function() {
        if (!isRecording) {
            startRecording();
        } else {
            stopRecording();
        }
    });

    function startRecording() {
        isRecording = true;
        voiceButton.classList.add('btn-danger');
        voiceButton.classList.remove('btn-primary');
        statusText.innerHTML = `
            <h6 class="mb-2">Mendengarkan...</h6>
            <p class="text-muted small mb-0">Silakan bicara sekarang</p>
        `;
        recognition.start();
    }

    function stopRecording() {
        isRecording = false;
        voiceButton.classList.remove('btn-danger');
        voiceButton.classList.add('btn-primary');
        statusText.innerHTML = `
            <h6 class="mb-2">Tekan untuk Berbicara</h6>
            <p class="text-muted small mb-0">SAGA AI siap mendengarkan</p>
        `;
        recognition.stop();
    }

    recognition.onresult = function(event) {
        const message = event.results[0][0].transcript;
        sendMessage(message);
        stopRecording();
    };

    recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
        statusText.innerHTML = `
            <h6 class="mb-2 text-danger">Error</h6>
            <p class="text-muted small mb-0">${event.error}</p>
        `;
        stopRecording();
    };

    function sendMessage(message) {
    fetch('handle_chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `message=${encodeURIComponent(message)}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            addMessageToChat(message, data.response, data.timestamp);
        } else {
            console.error('Error:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage('Terjadi kesalahan saat mengirim pesan');
    });
}

// Inisialisasi speech synthesis
const synth = window.speechSynthesis;

// Fungsi untuk mengubah teks menjadi suara
function speakResponse(text) {
    synth.cancel();
    
    // Preprocessing untuk pengucapan yang lebih natural
    text = improveIndonesianPronunciation(text);
    const sentences = text.match(/[^.!?]+[.!?]+/g) || [text];
    let currentIndex = 0;

    function speakNextSentence() {
        if (currentIndex < sentences.length) {
            const utterance = new SpeechSynthesisUtterance(sentences[currentIndex]);
            
            // Pengaturan dasar yang lebih natural
            utterance.lang = 'id-ID';
            utterance.volume = 0.9; // Sedikit lebih lembut
            utterance.rate = 1.1;   // Kecepatan sedang
            
            // Cari suara Indonesia yang tersedia
            const voices = synth.getVoices();
            const preferredVoice = voices.find(voice => 
                voice.lang.includes('id-ID') && voice.localService === false
            ) || voices.find(voice => 
                voice.lang.includes('id')
            );
            
            if (preferredVoice) {
                utterance.voice = preferredVoice;
            }

            // Dinamis sesuaikan suara berdasarkan konteks
            const sentence = sentences[currentIndex].toLowerCase();
            
            // Pengaturan suara yang lebih natural berdasarkan konteks
            if (sentence.includes('?')) {
                utterance.pitch = 1.15;  // Nada naik untuk pertanyaan
                utterance.rate = 1.05;   // Sedikit lebih lambat untuk pertanyaan
            } else if (sentence.includes('!')) {
                utterance.pitch = 1.2;   // Lebih tinggi untuk ekspresi
                utterance.rate = 1.15;   // Sedikit lebih cepat untuk antusiasme
            } else if (/^(oh|ah|wah|hmm)/.test(sentence)) {
                utterance.pitch = 1.1;   // Sedikit lebih tinggi untuk ekspresi
                utterance.rate = 0.95;   // Lebih lambat untuk penekanan
            } else {
                // Variasi kecil untuk kalimat normal
                utterance.pitch = 1 + (Math.random() * 0.1 - 0.05); // Variasi ±0.05
                utterance.rate = 1.1 + (Math.random() * 0.1 - 0.05); // Variasi kecepatan
            }

            // Tambah jeda untuk kata-kata tertentu
            utterance.onboundary = function(event) {
                if (event.name === 'word') {
                    const word = event.target.text.substr(event.charIndex).split(/\s+/)[0];
                    // Jeda pada kata penghubung
                    if (['dan', 'atau', 'tetapi', 'namun', 'karena', 'sehingga'].includes(word.toLowerCase())) {
                        setTimeout(() => {}, 100);
                    }
                }
            };

            // Jeda antar kalimat yang lebih natural
            utterance.onend = () => {
                setTimeout(() => {
                    currentIndex++;
                    speakNextSentence();
                }, sentence.includes('.') ? 200 : 100); // Jeda lebih lama untuk titik
            };

            synth.speak(utterance);
        }
    }

    speakNextSentence();
}


// Fungsi untuk memperbaiki pengucapan
function improveIndonesianPronunciation(text) {
    const pronunciationMap = {
        'yang': 'yang',
        'dengan': 'dengan',
        'adalah': 'adalah',
        'bagaimana': 'bagaimana',
        'mengapa': 'mengapa',
        'sedang': 'sedang',
        'akan': 'akan',
        'sudah': 'sudah',
        'bisa': 'bisa',
        'tidak': 'tidak',
        'ini': 'ini',
        'itu': 'itu',
        'saya': 'saya',
        'kamu': 'kamu',
        'dia': 'dia',
        'mereka': 'mereka',
        'kita': 'kita',
        'kami': 'kami'
    };
    
    // Tambah sedikit jeda untuk tanda baca
    text = text.replace(/([,.!?])/g, '$1 ');
    
    // Ganti pengucapan kata-kata tertentu
    return text.replace(/\b\w+\b/g, word => 
        pronunciationMap[word.toLowerCase()] || word
    );
}

// Fungsi untuk menyesuaikan properti suara berdasarkan konteks
function adjustVoiceProperties(utterance, sentence) {
    // Base settings
    utterance.rate = 1.2;  // kecepatan speech ai
    utterance.pitch = 1.0; // pitch suara ai
    
    // Sesuaikan berdasarkan tipe kalimat
    if (sentence.includes('?')) {
        // Nada naik untuk pertanyaan
        utterance.pitch = 1.2;
        utterance.rate = 0.85;
    } else if (sentence.includes('!')) {
        // Lebih ekspresif untuk seruan
        utterance.volume = 1.2;
        utterance.pitch = 1.15;
        utterance.rate = 1.1;
    } else if (sentence.length > 100) {
        // Kalimat panjang dibaca lebih cepat
        utterance.rate = 1.0;
    }
    
    // Tambahkan variasi acak kecil untuk kesan lebih natural
    utterance.pitch += (Math.random() * 0.1) - 0.05;
    utterance.rate += (Math.random() * 0.1) - 0.05;
}

// Fungsi untuk menentukan apakah perlu jeda setelah kata tertentu
function shouldPauseAfterWord(word) {
    const pauseWords = [
        'tetapi', 'namun', 'sedangkan', 'karena', 'sehingga',
        'ketika', 'setelah', 'sebelum', 'jika', 'apabila'
    ];
    return pauseWords.includes(word.toLowerCase());
}

// Fungsi untuk menghitung durasi jeda antar kalimat
function calculatePauseDuration(sentence) {
    // Jeda lebih lama setelah kalimat panjang
    const baseDelay = 250;
    const lengthFactor = Math.min(sentence.length / 50, 2);
    
    // Tambahan jeda untuk tanda baca tertentu
    if (sentence.endsWith('...')) return baseDelay * 2;
    if (sentence.endsWith('!')) return baseDelay * 1.5;
    if (sentence.endsWith('?')) return baseDelay * 1.3;
    
    return baseDelay * lengthFactor;
}


// Update fungsi addMessageToChat untuk memasukkan fitur suara
function addMessageToChat(message, response, timestamp) {
    const chatContainer = document.querySelector('.overflow-auto');
    
    // Tambah pesan user
    chatContainer.innerHTML += `
        <div class="d-flex gap-3 mb-3">
            <div class="rounded-circle bg-primary p-2 align-self-start" style="width: 32px; height: 32px;">
                <i class="bi bi-person text-white small"></i>
            </div>
            <div class="flex-grow-1">
                <div class="card border-0 bg-primary bg-opacity-10 rounded-3 p-2">
                    <p class="small mb-0">${escapeHtml(message)}</p>
                </div>
                <small class="text-muted ms-2">${timestamp}</small>
            </div>
        </div>
    `;

    // Tambah respons AI dengan tombol suara
    chatContainer.innerHTML += `
        <div class="d-flex gap-3 mb-3">
            <div class="rounded-circle bg-primary bg-opacity-10 p-2 align-self-start" style="width: 32px; height: 32px;">
                <i class="bi bi-robot text-primary small"></i>
            </div>
            <div class="flex-grow-1">
                <div class="card border-0 bg-light rounded-3 p-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <p class="small mb-0">${escapeHtml(response)}</p>
                        <button class="btn btn-sm btn-link text-primary" onclick="speakResponse('${escapeHtml(response)}')">
                            <i class="bi bi-volume-up"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted ms-2">${timestamp}</small>
            </div>
        </div>
    `;

    // Scroll ke bawah
    chatContainer.scrollTop = chatContainer.scrollHeight;

    // Otomatis bacakan respons
    speakResponse(response);
}

// Helper function untuk escape HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Function untuk menampilkan pesan error
function showErrorMessage(message) {
    const chatContainer = document.querySelector('.overflow-auto');
    chatContainer.innerHTML += `
        <div class="alert alert-danger" role="alert">
            ${escapeHtml(message)}
        </div>
    `;
    chatContainer.scrollTop = chatContainer.scrollHeight;
}});
</script>

</body>
</html>