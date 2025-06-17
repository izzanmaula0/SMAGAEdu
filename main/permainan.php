<?php
session_start();
require "koneksi.php";


if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

// Ambil userid dari session
$userid = $_SESSION['userid'];


// Ambil data guru
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);



// Ambil data siswa
$userid = $_SESSION['userid'];
$query = "SELECT * FROM siswa WHERE username = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
$query_kelas = "SELECT k.*, g.namaLengkap as nama_guru, g.foto_profil as guru_foto 
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
    <title>Permainan - SMAGAEdu</title>
</head>
<style>
    .custom-card {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .menu-samping {
            display: none;
        }

        body {
            padding-top: 60px;
        }

        .custom-card {
            max-width: 100%;
        }
    }

    .custom-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }

    .custom-card .profile-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 3px solid white;
        margin-top: -40px;
    }

    .custom-card .card-body {
        text-align: left;
    }

    body {
        font-family: merriweather;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }
</style>

<body>

    </head>
    <style>
        body {
            font-family: merriweather;
        }

        .color-web {
            background-color: rgb(218, 119, 86);
        }
    </style>
    <style>
        .col-utama {
            margin-left: 13rem;
            animation: fadeInUp 0.5s;
            opacity: 1;

        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .menu-samping {
                display: none;
            }

            .col-utama {
                margin-left: 0;
            }
        }
    </style>

    <body>

        <?php include 'includes/styles.php'; ?>

        <div class="container-fluid">
            <div class="row">
                <!-- sidebar buat view dekstopp -->
                <?php include 'includes/sidebar.php'; ?>

                <!-- Mobile navigation -->
                <?php include 'includes/mobile_nav siswa.php'; ?>

                <!-- Settings Modal -->
                <?php include 'includes/settings_modal.php'; ?>


            </div>
        </div>

        <style>
            .game-card {
                cursor: pointer;
                transition: transform 0.3s;
                width: 80%;
                border-radius: 15px;
            }

            .game-card:hover {
                transform: translateY(-5px);
            }

            .game-container {
                display: none;
                min-height: 400px;
            }

            .game-active {
                display: block;
            }

            .card-img-top {
                height: 180px;
                object-fit: cover;
            }

            .back-btn {
                cursor: pointer;
                padding: 5px 15px;
            }

            .math-question-card {
                height: 100%;
            }

            .memory-card {
                width: 100px;
                height: 100px;
                margin: 5px;
                position: relative;
                transform-style: preserve-3d;
                transition: transform 0.5s;
                cursor: pointer;
            }

            .memory-card.flip {
                transform: rotateY(180deg);
            }

            .memory-card-front,
            .memory-card-back {
                width: 100%;
                height: 100%;
                padding: 10px;
                position: absolute;
                border-radius: 5px;
                backface-visibility: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .memory-card-front {
                background-color: rgb(175, 63, 26);
                transform: rotateY(180deg);
                font-size: 50px;
            }

            .memory-card-back {
                background-color: rgb(218, 119, 86);
                color: white;
                font-size: 50px;
            }

            .typing-input {
                font-size: 24px;
                padding: 10px;
                text-align: center;
                margin-bottom: 20px;
            }

            .typing-text {
                font-size: 20px;
                line-height: 1.6;
                margin-bottom: 20px;
            }

            .typing-text span {
                position: relative;
            }

            .typing-text span.correct {
                color: green;
            }

            .typing-text span.incorrect {
                color: red;
                text-decoration: underline;
            }

            .typing-text span.current {
                background-color: rgba(255, 255, 0, 0.3);
            }

            .puzzle-piece {
                width: 100px;
                height: 100px;
                margin: 2px;
                cursor: pointer;
                font-size: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            }

            #quiz-options div {
                cursor: pointer;
                transition: all 0.2s;
            }

            #quiz-options div:hover {
                background-color: #e9ecef;
            }

            .card-text {
                font-size: 12px;
            }

            .card-title {
                font-weight: bold;
            }
        </style>

        <!-- ini isi kontennya -->
        <!-- Isi konten -->
        <!-- Area Menu Game -->
        <div class="col p-2 col-utama mt-1 mt-md-0">
            <div class="p-4 row row-cols-1 row-cols-md-3 g-1">
                <!-- Game 1: Puzzle Matematika -->
                <div class="col">
                    <div class="card h-75 game-card shadow-sm" onclick="openGame('math-game')">
                        <img src="assets/matematika.png" class="card-img-top" alt="Puzzle Matematika">
                        <div class="card-body">
                            <h5 class="card-title">Puzzle Matematika</h5>
                            <p class="card-text">Latih kemampuan matematika dasar dengan menjawab soal-soal yang diberikan.</p>
                        </div>
                    </div>
                </div>

                <!-- Game 2: Permainan Memori -->
                <div class="col">
                    <div class="card h-75 game-card shadow-sm" onclick="openGame('memory-game')">
                        <img src="assets/memori.png" class="card-img-top" alt="Permainan Memori">
                        <div class="card-body">
                            <h5 class="card-title">Permainan Memori</h5>
                            <p class="card-text">Asah ingatanmu dengan mencari pasangan kartu yang sama.</p>
                        </div>
                    </div>
                </div>

                <!-- Game 3: Latihan Mengetik -->
                <div class="col">
                    <div class="card h-75 game-card shadow-sm" onclick="openGame('typing-game')">
                        <img src="assets/mengetik.png" class="card-img-top" alt="Latihan Mengetik">
                        <div class="card-body">
                            <h5 class="card-title">Latihan Mengetik</h5>
                            <p class="card-text">Tingkatkan kecepatan mengetikmu dengan permainan seru ini.</p>
                        </div>
                    </div>
                </div>

                <!-- Game 4: Puzzle Geser -->
                <div class="col">
                    <div class="card h-75 game-card shadow-sm" onclick="openGame('sliding-puzzle')">
                        <img src="assets/puzzle.png" class="card-img-top" alt="Puzzle Geser">
                        <div class="card-body">
                            <h5 class="card-title">Puzzle Geser</h5>
                            <p class="card-text">Susun ulang potongan puzzle untuk membentuk angka 1-15 secara berurutan.</p>
                        </div>
                    </div>
                </div>

                <!-- Game 5: Kuis Pengetahuan Umum -->
                <div class="col">
                    <div class="card h-75 game-card shadow-sm" onclick="openGame('quiz-game')">
                        <img src="assets/quiz.png" class="card-img-top" alt="Kuis Pengetahuan Umum">
                        <div class="card-body">
                            <h5 class="card-title">Kuis Pengetahuan Umum</h5>
                            <p class="card-text">Uji pengetahuanmu dengan berbagai pertanyaan menarik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- AREA PERMAINANNYA -->
        <!-- Area Permainan -->
        <div class="container col-utama mt-4">
            <!-- Game 1: Puzzle Matematika -->
            <div id="math-game" class="game-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Puzzle Matematika</h2>
                    <span class="back-btn btn btn-sm btn-outline-secondary" onclick="closeGame()">Kembali</span>
                </div>
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card border-0 shadow-none math-question-card">
                            <div class="card-body  text-center">
                                <p>Pertanyaan</p>
                                <h3 id="math-question" class="display-4 mb-4">5 + 3 = ?</h3>
                                <input type="number" id="math-answer" class="form-control form-control-lg mb-4 text-center" placeholder="Tulis jawabanmu di sini">
                                <button id="math-submit" class="btn btn-lg me-2 text-white" style="background-color: rgb(218, 119, 86);"><i class="bi bi-check-circle-fill me-1"></i> Periksa</button>
                                <button id="math-next" class="btn border rounded btn-lg"><i class="bi bi-arrow-right-circle-fill me-1"></i> Soal Berikutnya</button>
                                <div id="math-result" class="mt-4 h4"></div>
                                <div class="mt-3">
                                    <span class="h5">Skor: <span id="math-score">0</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game 2: Permainan Memori -->
            <div id="memory-game" class="game-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Permainan Memori</h2>
                    <span class="back-btn btn btn-sm btn-outline-secondary" onclick="closeGame()">Kembali</span>
                </div>
                <div class="text-center mb-3">
                    <p>Temukan pasangan gambar yang sama!</p>
                    <span class="ms-3 h5">Langkah: <span id="memory-moves">0</span></span>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div id="memory-board" class="d-flex flex-wrap justify-content-center">
                            <!-- Kartu memori akan ditambahkan oleh JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <button id="memory-restart" class="btn me-2 btn-sm border" style=" border-radius:15px;">Mulai Ulang?</button>
                </div>

            </div>

            <!-- Game 3: Latihan Mengetik -->
            <div id="typing-game" class="game-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Latihan Mengetik</h2>
                    <span class="back-btn btn btn-sm btn-outline-secondary" onclick="closeGame()">Kembali</span>
                </div>
                <div class="row">
                    <div class="col-md-10 mx-auto">
                        <div class="card border-0 shadow-none">
                            <div class="card-body">
                                <div id="typing-text" class="typing-text"></div>
                                <input type="text" id="typing-input" class="form-control typing-input" placeholder="Mulai mengetik di sini...">
                                <div class="d-flex justify-content-between">
                                    <button id="typing-restart" class="btn btn-primary">Mulai Ulang</button>
                                    <div>
                                        <span class="me-3">Waktu: <span id="typing-time">60</span> detik</span>
                                        <span>WPM: <span id="typing-wpm">0</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game 4: Puzzle Geser -->
            <div id="sliding-puzzle" class="game-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Puzzle Geser</h2>
                    <span class="back-btn btn btn-sm btn-outline-secondary" onclick="closeGame()">Kembali</span>
                </div>
                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <div class="card shadow">
                            <div class="card-body text-center">
                                <p>Klik pada balok untuk menggeser dan susun angka 1-15:</p>
                                <div id="puzzle-board" class="d-flex flex-wrap justify-content-center" style="max-width: 420px; margin: 0 auto;"></div>
                                <button id="puzzle-restart" class="btn btn-primary mt-3">Acak Ulang</button>
                                <div class="mt-3">
                                    <span class="h5">Gerakan: <span id="puzzle-moves">0</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Game 5: Kuis Pengetahuan Umum -->
            <div id="quiz-game" class="game-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Kuis Pengetahuan Umum</h2>
                    <span class="back-btn btn btn-sm btn-outline-secondary" onclick="closeGame()">Kembali</span>
                </div>
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card shadow">
                            <div class="card-body">
                                <h3 id="quiz-question" class="text-center mb-4">Pertanyaan akan muncul di sini</h3>
                                <div id="quiz-options" class="d-grid gap-2"></div>
                                <div id="quiz-feedback" class="alert mt-3" style="display: none;"></div>
                                <div class="d-flex justify-content-between mt-4">
                                    <button id="quiz-next" class="btn btn-primary">Pertanyaan Berikutnya</button>
                                    <div>
                                        <span class="h5">Skor: <span id="quiz-score">0</span>/<span id="quiz-total">0</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Fungsi untuk mengelola tampilan game
            function openGame(gameId) {
                document.querySelector('.col-utama').style.display = 'none';
                document.getElementById(gameId).classList.add('game-active');

                // Initialize the specific game
                if (gameId === 'math-game') initMathGame();
                if (gameId === 'memory-game') initMemoryGame();
                if (gameId === 'typing-game') initTypingGame();
                if (gameId === 'sliding-puzzle') initSlidingPuzzle();
                if (gameId === 'quiz-game') initQuizGame();
            }

            function closeGame() {
                document.querySelector('.col-utama').style.display = 'block';
                document.querySelectorAll('.game-container').forEach(container => {
                    container.classList.remove('game-active');
                });
            }

            // Game 1: Puzzle Matematika
            let mathScore = 0;
            let currentMathQuestion = {};

            function initMathGame() {
                mathScore = 0;
                document.getElementById('math-score').textContent = '0';
                generateMathQuestion();

                document.getElementById('math-submit').addEventListener('click', checkMathAnswer);
                document.getElementById('math-next').addEventListener('click', generateMathQuestion);
                document.getElementById('math-answer').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') checkMathAnswer();
                });
            }

            function generateMathQuestion() {
                const operations = ['+', '-', '*', '/'];
                const operation = operations[Math.floor(Math.random() * 3)]; // Exclude division for simplicity
                let num1, num2;

                if (operation === '+') {
                    num1 = Math.floor(Math.random() * 100);
                    num2 = Math.floor(Math.random() * 100);
                    currentMathQuestion = {
                        question: `${num1} + ${num2} = ?`,
                        answer: num1 + num2
                    };
                } else if (operation === '-') {
                    num1 = Math.floor(Math.random() * 100) + 20;
                    num2 = Math.floor(Math.random() * num1);
                    currentMathQuestion = {
                        question: `${num1} - ${num2} = ?`,
                        answer: num1 - num2
                    };
                } else if (operation === '*') {
                    num1 = Math.floor(Math.random() * 12) + 1;
                    num2 = Math.floor(Math.random() * 12) + 1;
                    currentMathQuestion = {
                        question: `${num1} × ${num2} = ?`,
                        answer: num1 * num2
                    };
                }

                document.getElementById('math-question').textContent = currentMathQuestion.question;
                document.getElementById('math-answer').value = '';
                document.getElementById('math-result').textContent = '';
                document.getElementById('math-answer').focus();
            }

            function checkMathAnswer() {
                const userAnswer = parseInt(document.getElementById('math-answer').value);
                const resultElement = document.getElementById('math-result');

                if (userAnswer === currentMathQuestion.answer) {
                    resultElement.textContent = 'Benar! 👍';
                    resultElement.className = 'mt-4 h4 text-success';
                    mathScore++;
                    document.getElementById('math-score').textContent = mathScore;
                } else {
                    resultElement.textContent = `Salah. Jawaban yang benar adalah ${currentMathQuestion.answer}`;
                    resultElement.className = 'mt-4 h4 text-danger';
                }
            }

            // Game 2: Permainan Memori
            let hasFlippedCard = false;
            let lockBoard = false;
            let firstCard, secondCard;
            let memoryMoves = 0;
            const memoryPairs = ['🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓'];

            function initMemoryGame() {
                memoryMoves = 0;
                document.getElementById('memory-moves').textContent = '0';
                createMemoryBoard();

                document.getElementById('memory-restart').addEventListener('click', restartMemoryGame);
            }

            function createMemoryBoard() {
                const memoryBoard = document.getElementById('memory-board');
                memoryBoard.innerHTML = '';

                // Duplicate the pairs and shuffle
                let cards = [...memoryPairs, ...memoryPairs];
                cards = shuffleArray(cards);

                cards.forEach((card, index) => {
                    const cardElement = document.createElement('div');
                    cardElement.classList.add('memory-card');
                    cardElement.dataset.value = card;

                    const frontFace = document.createElement('div');
                    frontFace.classList.add('memory-card-front');
                    frontFace.textContent = card;

                    const backFace = document.createElement('div');
                    backFace.classList.add('memory-card-back');
                    backFace.textContent = '?';

                    cardElement.appendChild(frontFace);
                    cardElement.appendChild(backFace);
                    cardElement.addEventListener('click', flipCard);

                    memoryBoard.appendChild(cardElement);
                });
            }

            function flipCard() {
                if (lockBoard) return;
                if (this === firstCard) return;

                this.classList.add('flip');

                if (!hasFlippedCard) {
                    // First click
                    hasFlippedCard = true;
                    firstCard = this;
                    return;
                }

                // Second click
                secondCard = this;

                // Increment moves counter
                memoryMoves++;
                document.getElementById('memory-moves').textContent = memoryMoves;

                checkForMatch();
            }

            function checkForMatch() {
                let isMatch = firstCard.dataset.value === secondCard.dataset.value;

                isMatch ? disableCards() : unflipCards();

                // Check if all cards are matched
                const allCards = document.querySelectorAll('.memory-card');
                const flippedCards = document.querySelectorAll('.memory-card.flip');

                if (flippedCards.length === allCards.length) {
                    setTimeout(() => {
                        // Create success modal dynamically if it doesn't exist
                        if (!document.getElementById('memorySuccessModal')) {
                            const modalHTML = `
                                <div class="modal fade" id="memorySuccessModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">Selamat!</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <h3 class="mb-4">🎉 Permainan Selesai! 🎉</h3>
                                                <p class="lead">Anda berhasil menyelesaikan permainan dalam <strong id="memoryMovesResult">${memoryMoves}</strong> gerakan.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" onclick="restartMemoryGame()">Main Lagi</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            document.body.insertAdjacentHTML('beforeend', modalHTML);
                        } else {
                            document.getElementById('memoryMovesResult').textContent = memoryMoves;
                        }

                        // Show the modal
                        const successModal = new bootstrap.Modal(document.getElementById('memorySuccessModal'));
                        successModal.show();
                    }, 500);
                }
            }

            function disableCards() {
                firstCard.removeEventListener('click', flipCard);
                secondCard.removeEventListener('click', flipCard);

                resetBoard();
            }

            function unflipCards() {
                lockBoard = true;

                setTimeout(() => {
                    firstCard.classList.remove('flip');
                    secondCard.classList.remove('flip');

                    resetBoard();
                }, 1000);
            }

            function resetBoard() {
                [hasFlippedCard, lockBoard] = [false, false];
                [firstCard, secondCard] = [null, null];
            }

            function restartMemoryGame() {
                resetBoard();
                memoryMoves = 0;
                document.getElementById('memory-moves').textContent = '0';

                setTimeout(() => {
                    createMemoryBoard();
                }, 300);
            }

            // Game 3: Latihan Mengetik
            let typingTexts = [
                "Teknologi informasi telah mengubah cara kita hidup dan bekerja. Komputer, internet, dan telepon pintar telah menjadi bagian penting dalam kehidupan sehari-hari.",
                "Belajar adalah proses yang berkelanjutan sepanjang hidup. Kita tidak pernah terlalu tua untuk memperoleh pengetahuan dan keterampilan baru.",
                "Indonesia adalah negara kepulauan terbesar di dunia dengan lebih dari tujuh belas ribu pulau yang tersebar dari Sabang sampai Merauke."
            ];
            let currentTypingText = '';
            let typingTimer;
            let typingTimeLeft = 60;
            let typingCharIndex = 0;
            let typingStartTime;
            let typingWpm = 0;

            function initTypingGame() {
                typingTimeLeft = 60;
                typingCharIndex = 0;
                typingWpm = 0;
                document.getElementById('typing-time').textContent = typingTimeLeft;
                document.getElementById('typing-wpm').textContent = '0';

                // Select random text
                currentTypingText = typingTexts[Math.floor(Math.random() * typingTexts.length)];

                // Format text with spans for styling
                const textDisplay = document.getElementById('typing-text');
                textDisplay.innerHTML = '';
                currentTypingText.split('').forEach(char => {
                    const charSpan = document.createElement('span');
                    charSpan.innerText = char;
                    textDisplay.appendChild(charSpan);
                });

                // Set the first character as current
                textDisplay.querySelector('span').classList.add('current');

                // Clear existing timers
                if (typingTimer) clearInterval(typingTimer);

                const inputField = document.getElementById('typing-input');
                inputField.value = '';
                inputField.focus();

                inputField.addEventListener('input', processTyping);
                document.getElementById('typing-restart').addEventListener('click', initTypingGame);
            }

            function processTyping() {
                const inputField = document.getElementById('typing-input');
                const textDisplay = document.getElementById('typing-text');
                const spans = textDisplay.querySelectorAll('span');
                const inputValue = inputField.value;

                // Start timer on first input
                if (!typingStartTime && inputValue.length === 1) {
                    typingStartTime = Date.now();
                    typingTimer = setInterval(updateTypingTimer, 1000);
                }

                // Compare input with text
                if (typingCharIndex < spans.length) {
                    spans.forEach(span => span.classList.remove('current'));

                    if (inputValue.length > 0) {
                        // Check current character
                        if (inputValue === currentTypingText.substring(0, inputValue.length)) {
                            // All correct so far
                            for (let i = 0; i < inputValue.length; i++) {
                                spans[i].classList.add('correct');
                            }
                        } else {
                            // Error in input
                            let correctChars = 0;
                            for (let i = 0; i < inputValue.length; i++) {
                                if (inputValue[i] === currentTypingText[i]) {
                                    spans[i].classList.remove('incorrect');
                                    spans[i].classList.add('correct');
                                    correctChars++;
                                } else {
                                    spans[i].classList.remove('correct');
                                    spans[i].classList.add('incorrect');
                                }
                            }
                        }

                        // Add current class to next character
                        if (inputValue.length < spans.length) {
                            spans[inputValue.length].classList.add('current');
                        }

                        // Calculate WPM
                        const elapsedTime = (Date.now() - typingStartTime) / 60000; // in minutes
                        const wordsTyped = inputValue.length / 5; // assuming 5 characters per word
                        typingWpm = Math.round(wordsTyped / elapsedTime);
                        document.getElementById('typing-wpm').textContent = isNaN(typingWpm) ? '0' : typingWpm;

                        // Check if completed
                        if (inputValue === currentTypingText) {
                            clearInterval(typingTimer);
                            inputField.disabled = true;
                            alert(`Selamat! Anda menyelesaikan latihan mengetik dengan kecepatan ${typingWpm} WPM.`);
                        }
                    } else {
                        // Reset all to normal
                        spans.forEach(span => {
                            span.classList.remove('correct', 'incorrect');
                        });
                        spans[0].classList.add('current');
                    }

                    typingCharIndex = inputValue.length;
                }
            }

            function updateTypingTimer() {
                typingTimeLeft--;
                document.getElementById('typing-time').textContent = typingTimeLeft;

                if (typingTimeLeft <= 0) {
                    clearInterval(typingTimer);
                    document.getElementById('typing-input').disabled = true;
                    alert('Waktu habis! Silakan coba lagi.');
                }
            }

            // Game 4: Puzzle Geser
            let puzzleGrid = [];
            let emptyCell = {
                row: 3,
                col: 3
            };
            let puzzleMoves = 0;

            function initSlidingPuzzle() {
                puzzleMoves = 0;
                document.getElementById('puzzle-moves').textContent = '0';

                // Initialize grid
                puzzleGrid = [];
                for (let i = 0; i < 16; i++) {
                    if (i === 15) {
                        puzzleGrid.push('');
                    } else {
                        puzzleGrid.push(i + 1);
                    }
                }

                // Shuffle
                shufflePuzzle();

                // Render
                renderPuzzle();

                document.getElementById('puzzle-restart').addEventListener('click', initSlidingPuzzle);
            }

            function shufflePuzzle() {
                // Make random moves to shuffle
                for (let i = 0; i < 100; i++) {
                    const possibleMoves = getPossibleMoves();
                    const move = possibleMoves[Math.floor(Math.random() * possibleMoves.length)];
                    makeMove(move.row, move.col, false);
                }

                // Reset moves counter
                puzzleMoves = 0;
                emptyCell = {
                    row: 3,
                    col: 3
                }; // Reset empty cell position

                // Get grid index from row, col
                function getIndex(row, col) {
                    return row * 4 + col;
                }

                // Check if the puzzle is solvable
                let inversionCount = 0;
                for (let i = 0; i < 15; i++) {
                    for (let j = i + 1; j < 16; j++) {
                        if (puzzleGrid[i] && puzzleGrid[j] && puzzleGrid[i] > puzzleGrid[j]) {
                            inversionCount++;
                        }
                    }
                }

                // If not solvable, swap two pieces to make it solvable
                if (inversionCount % 2 !== 0) {
                    // Swap first two pieces
                    [puzzleGrid[0], puzzleGrid[1]] = [puzzleGrid[1], puzzleGrid[0]];
                }
            }

            function renderPuzzle() {
                const board = document.getElementById('puzzle-board');
                board.innerHTML = '';

                for (let row = 0; row < 4; row++) {
                    for (let col = 0; col < 4; col++) {
                        const index = row * 4 + col;
                        const piece = document.createElement('div');
                        piece.classList.add('puzzle-piece');

                        if (puzzleGrid[index] !== '') {
                            piece.textContent = puzzleGrid[index];
                            piece.style.backgroundColor = '#007bff';
                            piece.style.color = 'white';
                            piece.addEventListener('click', () => handlePuzzleClick(row, col));
                        } else {
                            piece.style.backgroundColor = '#e9ecef';
                        }

                        board.appendChild(piece);
                    }
                }
            }

            function handlePuzzleClick(row, col) {
                if (canMove(row, col)) {
                    makeMove(row, col, true);
                    renderPuzzle();

                    // Check for win
                    if (isPuzzleSolved()) {
                        setTimeout(() => {
                            alert(`Selamat! Anda menyelesaikan puzzle dalam ${puzzleMoves} gerakan.`);
                        }, 300);
                    }
                }
            }

            function makeMove(row, col, countMove) {
                const clickedIndex = row * 4 + col;
                const emptyIndex = emptyCell.row * 4 + emptyCell.col;

                // Swap
                [puzzleGrid[clickedIndex], puzzleGrid[emptyIndex]] = [puzzleGrid[emptyIndex], puzzleGrid[clickedIndex]];

                // Update empty cell position
                emptyCell = {
                    row,
                    col
                };

                // Increment moves if counting
                if (countMove) {
                    puzzleMoves++;
                    document.getElementById('puzzle-moves').textContent = puzzleMoves;
                }
            }

            function canMove(row, col) {
                // Check if adjacent to empty cell
                return (
                    (Math.abs(row - emptyCell.row) === 1 && col === emptyCell.col) ||
                    (Math.abs(col - emptyCell.col) === 1 && row === emptyCell.row)
                );
            }

            function getPossibleMoves() {
                const moves = [];

                // Check all four directions
                const directions = [{
                        row: -1,
                        col: 0
                    }, // Up
                    {
                        row: 1,
                        col: 0
                    }, // Down
                    {
                        row: 0,
                        col: -1
                    }, // Left
                    {
                        row: 0,
                        col: 1
                    } // Right
                ];

                for (const dir of directions) {
                    const newRow = emptyCell.row + dir.row;
                    const newCol = emptyCell.col + dir.col;

                    // Check if in bounds
                    if (newRow >= 0 && newRow < 4 && newCol >= 0 && newCol < 4) {
                        moves.push({
                            row: newRow,
                            col: newCol
                        });
                    }
                }

                return moves;
            }

            function isPuzzleSolved() {
                for (let i = 0; i < 15; i++) {
                    if (puzzleGrid[i] !== i + 1) {
                        return false;
                    }
                }
                return puzzleGrid[15] === '';
            }

            // Game 5: Kuis Pengetahuan Umum
            let quizQuestions = [{
                    question: "Planet apa yang terdekat dengan matahari?",
                    options: ["Venus", "Merkurius", "Bumi", "Mars"],
                    answer: 1
                },
                {
                    question: "Siapakah penemu bola lampu?",
                    options: ["Albert Einstein", "Isaac Newton", "Thomas Edison", "Nikola Tesla"],
                    answer: 2
                },
                {
                    question: "Berapa jumlah provinsi di Indonesia?",
                    options: ["33", "34", "35", "36"],
                    answer: 1
                },
                {
                    question: "Apa nama ibukota Jepang?",
                    options: ["Seoul", "Beijing", "Tokyo", "Bangkok"],
                    answer: 2
                },
                {
                    question: "Gas apa yang paling banyak terdapat di atmosfer Bumi?",
                    options: ["Oksigen", "Karbon Dioksida", "Hidrogen", "Nitrogen"],
                    answer: 3
                },
                {
                    question: "Berapa sisi yang dimiliki sebuah segitiga?",
                    options: ["3", "4", "5", "6"],
                    answer: 0
                },
                {
                    question: "Benua terbesar di dunia adalah?",
                    options: ["Afrika", "Amerika", "Eropa", "Asia"],
                    answer: 3
                },
                {
                    question: "Hewan apa yang merupakan mamalia terbesar di dunia?",
                    options: ["Gajah", "Paus Biru", "Badak", "Jerapah"],
                    answer: 1
                },
                {
                    question: "Berapa jumlah planet di tata surya kita?",
                    options: ["7", "8", "9", "10"],
                    answer: 1
                },
                {
                    question: "Sungai terpanjang di dunia adalah?",
                    options: ["Sungai Nil", "Sungai Amazon", "Sungai Mississippi", "Sungai Yangtze"],
                    answer: 0
                }
            ];

            let currentQuizQuestion = 0;
            let quizScore = 0;

            function initQuizGame() {
                currentQuizQuestion = 0;
                quizScore = 0;
                document.getElementById('quiz-score').textContent = '0';
                document.getElementById('quiz-total').textContent = '0';

                // Shuffle questions
                quizQuestions = shuffleArray(quizQuestions);

                displayQuizQuestion();

                document.getElementById('quiz-next').addEventListener('click', nextQuizQuestion);
            }

            function displayQuizQuestion() {
                const questionData = quizQuestions[currentQuizQuestion];
                document.getElementById('quiz-question').textContent = questionData.question;

                const optionsContainer = document.getElementById('quiz-options');
                optionsContainer.innerHTML = '';

                questionData.options.forEach((option, index) => {
                    const button = document.createElement('button');
                    button.classList.add('btn', 'btn-outline-primary', 'text-start', 'p-3');
                    button.textContent = option;
                    button.addEventListener('click', () => checkQuizAnswer(index));
                    optionsContainer.appendChild(button);
                });

                document.getElementById('quiz-feedback').style.display = 'none';
                document.getElementById('quiz-total').textContent = currentQuizQuestion + 1;
            }

            function checkQuizAnswer(selectedIndex) {
                const questionData = quizQuestions[currentQuizQuestion];
                const feedbackElement = document.getElementById('quiz-feedback');
                const optionButtons = document.querySelectorAll('#quiz-options button');

                // Disable all buttons
                optionButtons.forEach(button => {
                    button.disabled = true;
                });

                // Highlight correct and incorrect answers
                optionButtons[questionData.answer].classList.remove('btn-outline-primary');
                optionButtons[questionData.answer].classList.add('btn-success');

                if (selectedIndex === questionData.answer) {
                    // Correct answer
                    feedbackElement.textContent = 'Benar! 👍';
                    feedbackElement.className = 'alert alert-success mt-3';
                    quizScore++;
                    document.getElementById('quiz-score').textContent = quizScore;
                } else {
                    // Incorrect answer
                    feedbackElement.textContent = 'Salah. Jawaban yang benar adalah: ' +
                        quizQuestions[currentQuizQuestion].options[questionData.answer];
                    feedbackElement.className = 'alert alert-danger mt-3';
                    optionButtons[selectedIndex].classList.remove('btn-outline-primary');
                    optionButtons[selectedIndex].classList.add('btn-danger');
                }

                feedbackElement.style.display = 'block';
            }

            function nextQuizQuestion() {
                currentQuizQuestion++;

                if (currentQuizQuestion < quizQuestions.length) {
                    displayQuizQuestion();
                } else {
                    // End of quiz
                    document.getElementById('quiz-question').textContent = 'Kuis selesai!';
                    document.getElementById('quiz-options').innerHTML = '';
                    document.getElementById('quiz-feedback').textContent =
                        `Skor akhir Anda: ${quizScore} dari ${quizQuestions.length}.`;
                    document.getElementById('quiz-feedback').className = 'alert alert-info mt-3';
                    document.getElementById('quiz-feedback').style.display = 'block';
                    document.getElementById('quiz-next').textContent = 'Mulai Ulang';
                    document.getElementById('quiz-next').removeEventListener('click', nextQuizQuestion);
                    document.getElementById('quiz-next').addEventListener('click', initQuizGame);
                }
            }

            // Utility function for shuffling arrays
            function shuffleArray(array) {
                const newArray = [...array];
                for (let i = newArray.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
                }
                return newArray;
            }

            // Initialize the page when loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Add event listeners for initializing games here if needed
            });
        </script>

    </body>

</html>