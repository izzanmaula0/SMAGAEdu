<?php
include 'includes/session_config.php';
require "koneksi.php";

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

// Ambil userid dari session
$userid = $_SESSION['userid'];


// Change query variable name from $guru to $siswa
$query = "SELECT s.*, 
    k.nama_kelas AS kelas_saat_ini 
    FROM siswa s 
    LEFT JOIN kelas_siswa ks ON s.id = ks.siswa_id 
    LEFT JOIN kelas k ON ks.kelas_id = k.id 
    WHERE s.username = ?";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $userid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);

$profilePath = !empty($siswa['foto_profil']) ? 'uploads/profil/' . $siswa['foto_profil'] : 'assets/pp.png';
error_log("Profile image path: " . $profilePath);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="icon" type="image/png" href="assets/tab.png">
    <link rel="icon" type="image/png" href="assets/tab.png">
    <title>SAGA - SMAGAEdu</title>
</head>
<style>
    body {
        font-family: merriweather;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }
</style>

<body>

    <!-- lazy loading sheetjs -->
    <script>
        function loadSheetJS() {
            return new Promise((resolve, reject) => {
                if (window.XLSX) {
                    resolve(window.XLSX);
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
                script.async = true;
                script.onload = () => resolve(window.XLSX);
                script.onerror = reject;
                document.body.appendChild(script);
            });
        }
    </script>

    <!-- style untuk animasi modal -->
    <!-- style animasi modal -->
    <style>
        .modal-content {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .modal .btn {
            font-weight: 500;
            transition: all 0.2s;
        }

        .modal .btn:active {
            transform: scale(0.98);
        }

        .modal.fade .modal-dialog {
            transform: scale(0.95);
            transition: transform 0.2s ease-out;
        }

        .modal.show .modal-dialog {
            transform: scale(1);
        }
    </style>

    <!-- loading screen -->
    <style>
        /* Loading Screen */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: white;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .loader-container {
            text-align: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(218, 119, 86, 0.2);
            border-top: 5px solid rgb(218, 119, 86);
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loader-container p {
            color: rgb(218, 119, 86);
            font-family: 'Merriweather', serif;
            font-size: 16px;
            margin: 0;
        }

        /* Tambahkan class untuk fade out */
        #loading-screen.fade-out {
            opacity: 0;
            visibility: hidden;
        }
    </style>


    <?php include 'includes/styles.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar for desktop -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Mobile navigation -->
            <?php include 'includes/mobile_nav siswa.php'; ?>

            <!-- Settings Modal -->
            <?php include 'includes/settings_modal.php'; ?>


        </div>
    </div>

    <!-- loading awal -->
    <div id="loading-screen">
        <div class="loader-container">
            <img src="assets/ai_loading.gif" alt="Loading..." style="width: 80px; height: 80px;" />
        </div>
    </div>

    <!-- Welcome Modal -->
    <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-header p-0 position-relative" style="height: 120px; overflow: hidden; border-bottom: none;">
                    <img src="assets/ai_header.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 0;">
                </div>
                <div class="modal-body p-4">
                    <h4 class="fw-bold mt-4 mb-3" style="font-size: 2rem;">Nikmati Berkolaborasi Bersama SAGA AI</h4>
                    <!-- <p class="mb-4">SAGA Asisten AI yang siap membantu proses belajar Anda. Ajukan pertanyaan seputar pelajaran, minta bantuan mengerjakan tugas, atau diskusikan materi yang sulit dipahami.</p> -->
                    <div class="alert border bg-light mb-2" style="border-radius: 15px; cursor: pointer;" id="dataAlert">
                        <div class="d-flex">
                            <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Izinkan Kami Mengawasi Data Anda</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Dikarenakan perilisan awal SAGA, kami akan memonitor seluruh percakapan Anda bersama SAGA.</p>
                                <div class="mt-2">
                                    <a class="a-0 m-0 text-muted" style="font-size: 12px; cursor: pointer;">Kenapa data saya di awasi?</a>
                                    <i class="bi bi-chevron-down text-muted ms-1" id="toggleIcon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="collapse mt-3" id="dataExplanation">
                            <div class="card card-body bg-white border-0" style="font-size: 12px;">
                                <p class="mt-2 mb-0">Untuk memastikan penggunaan SAGA AI tetap efisien dan tidak melebihi batas penggunaan, sistem hanya mencatat jumlah pemakaian, bukan isi percakapan. <br><br>Sebagai tim IT, kami tetap menjunjung tinggi kerahasiaan data Anda serta privasi Anda selama
                                    percakapan dengan SAGA. Kami menjamin tidak ada yang mengakses atau membaca isi interaksi Anda dengan SAGA.

                                </p>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const dataAlert = document.getElementById('dataAlert');
                            const dataExplanation = document.getElementById('dataExplanation');
                            const toggleIcon = document.getElementById('toggleIcon');

                            dataAlert.addEventListener('click', function() {
                                $(dataExplanation).collapse('toggle');
                                toggleIcon.classList.toggle('bi-chevron-down');
                                toggleIcon.classList.toggle('bi-chevron-up');
                            });
                        });
                    </script>
                    <div class="alert border bg-light mb-4" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-check-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Periksa Kembali Respons SAGA</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Tim IT SMAGA tidak menjamin keakuratan data yang dihasilkan oleh SAGA, oleh karena itu cek kembali setiap respons SAGA.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="dontShowAgainCheck">
                        <label class="form-check-label text-muted" for="dontShowAgainCheck" style="font-size: 14px;">
                            <p class="text-muted">Jangan tampilkan pesan ini selama saya aktif</p>
                        </label>
                    </div>
                    <div class="d-flex mt-3">
                        <button type="button" class="btn flex-fill" id="startButton" style="background-color: rgb(218, 119, 86); color: white; border-radius:15px;" data-bs-dismiss="modal">Mulai Bertanya</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to show welcome modal on page load -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if PHP session has hideWelcomeModal flag
            <?php if (!isset($_SESSION['hideWelcomeModal']) || $_SESSION['hideWelcomeModal'] !== true): ?>
                // Show welcome modal after loading screen disappears
                setTimeout(function() {
                    const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
                    welcomeModal.show();
                }, 1500); // Show after loading screen (1000ms) + fade out (500ms)
            <?php endif; ?>

            // Add event listener to "Start" button to check if "Don't show again" is checked
            document.getElementById('startButton').addEventListener('click', function() {
                if (document.getElementById('dontShowAgainCheck').checked) {
                    // Use AJAX to set the session variable directly in this file
                    fetch('ai.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Set-Modal-Preference': 'true' // Custom header to identify this request
                            },
                            body: JSON.stringify({
                                hideWelcomeModal: true
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Modal preference saved:', data);
                        })
                        .catch(error => {
                            console.error('Error saving modal preference:', error);
                        });

                    // Set session in client-side to avoid showing modal after page refresh
                    localStorage.setItem('hideWelcomeModal', 'true');
                }
                // Modal will be hidden automatically due to data-bs-dismiss="modal"
            });

            // Also check localStorage on page load
            if (localStorage.getItem('hideWelcomeModal') === 'true') {
                // Don't show the modal if user previously checked "don't show again"
                const modalElement = document.getElementById('welcomeModal');
                if (modalElement && modalElement._isShown) {
                    const welcomeModal = bootstrap.Modal.getInstance(modalElement);
                    if (welcomeModal) welcomeModal.hide();
                }
            }
        });
    </script>

    <?php
    // Handle the AJAX request to set session variable
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_SET_MODAL_PREFERENCE'])) {
        // Get JSON data
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (isset($data['hideWelcomeModal']) && $data['hideWelcomeModal'] === true) {
            $_SESSION['hideWelcomeModal'] = true;
            echo json_encode(['success' => true, 'message' => 'Modal preference saved']);
            exit;
        }
    }
    ?>


    <!-- ini isi kontennya -->
    <div class="col pt-0 pb-0 p-4 col-utama">
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
                    margin-top: 1rem;
                }

                .peringatan {
                    display: none;
                }
            }
        </style>
        <div class="container-fluid p-0 m-0 mt-4">
            <div class="">
                <div class="d-flex justify-content-between">
                    <div class="headerChat">
                        <h3 class="mb-0 fw-bold">Saga</h3>
                        <div style="display: none;">
                            <p class="loading animate__animated animate__fadeIn animate__flash animate__infinite text-muted p-0 m-0"
                                id="loading"
                                style="font-size: 13px; z-index: 10;display: none;">Sedang berpikir...</p>
                            <p class="animate__animated animate__fadeIn text-muted p-0 m-0"
                                style="font-size: 13px; z-index: 10;"
                                id="tersedia">SMAGAAI tersedia</p>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="text-muted" style="font-size: 13px;">
                                <span id="firstMessage" class="d-none">
                                    <i class="bi bi-chat-text"></i>
                                    <span class="first-message-text ms-1"></span>
                                </span>
                            </div>
                        </div>
                        <style>
                            #firstMessage {
                                animation: fadeIn 0.3s ease;
                            }

                            @keyframes fadeIn {
                                from {
                                    opacity: 0;
                                    transform: translateY(-10px);
                                }

                                to {
                                    opacity: 1;
                                    transform: translateY(0);
                                }
                            }
                        </style>

                    </div>
                    <style>
                        .loading {
                            animation-duration: 3s;
                        }

                        .chat-message {
                            margin: 8px 0;
                            transition: all 0.3s ease;
                        }

                        @media (max-width: 768px) {

                            /* Sesuaikan chat container */
                            .chat-container {
                                height: calc(89vh - 180px);
                                overflow-y: auto;
                                overflow-x: hidden;
                                margin-bottom: 120px;
                            }

                            .chat-message {
                                max-width: 85% !important;
                            }

                            /* Pastikan input container tetap di posisinya */
                            .input-container {
                                position: fixed !important;
                                bottom: 100px !important;
                                /* Memberikan ruang di atas navbar */
                                left: 0 !important;
                                right: 0 !important;
                                background-color: #EEECE2 !important;
                                z-index: 1000 !important;
                                padding: 1rem !important;
                                margin: 1rem;
                            }

                            /* Sesuaikan ukuran dan posisi input wrapper */
                            .input-wrapper {
                                width: 95% !important;
                                margin: 0.5rem !important;
                                max-width: none !important;
                                margin-bottom: 4.5rem !important;
                            }

                            /* Container utama */
                            .col-utama {
                                margin-left: 0 !important;
                            }
                        }

                        /* Tambahkan ini untuk memastikan scroll berfungsi dengan baik */
                        body {
                            height: 100vh;
                            overflow-y: auto;
                        }
                    </style>
                    <div class="gap-2 mb-4 d-flex">
                        <button class="btn bg-white text-black d-flex align-items-center gap-2 px-3 py-2 border"
                            data-bs-toggle="modal"
                            data-bs-target="#newChatConfirmModal"
                            style="border-radius: 15px;">
                            <i class="bi bi-plus-circle"></i>
                            <span class="button-text d-none d-md-inline" style="font-size: 13px;">Baru</span>
                        </button>

                        <!-- New Chat Confirmation Modal -->
                        <div class="modal fade" id="newChatConfirmModal" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius: 16px;">
                                    <div class="modal-body text-center p-4">
                                        <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:red;"></i>
                                        <h5 class="mt-3 fw-bold">Buat Percakapan Baru</h5>
                                        <p class="mb-4">Apakah Anda yakin ingin memulai percakapan baru? Seluruh percakapan dan memori saat ini akan terhapus.</p>
                                        <div class="d-flex gap-2 btn-group">
                                            <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                            <button id="confirmNewChatBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Buat Baru</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Add event listener for the confirm button
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('confirmNewChatBtn').addEventListener('click', function() {
                                    // Reload the page to start a new chat
                                    window.location.reload();
                                });
                            });
                        </script>
                        <!-- <button class="btn bg-white text-black d-flex align-items-center gap-2 px-3 py-2 border"
                            data-bs-toggle="modal"
                            data-bs-target="#historyModal"
                            style="border-radius: 15px;">
                            <i class="bi bi-clock-history"></i>
                            <span class="button-text d-none d-md-inline" style="font-size: 13px;">Riwayat</span>
                        </button>
                        <button class="btn d-flex text-black bg-white align-items-center gap-2 px-3 py-2 border"
                            data-bs-toggle="modal"
                            data-bs-target="#projectModal"
                            style="border-radius: 15px;">
                            <i class="bi bi-folder-plus"></i>
                            <span class="button-text d-none d-md-inline" style="font-size: 13px;">Memori</span>
                        </button> -->
                    </div>

                    <style>
                        .btn {
                            padding: 8px 12px;
                            font-size: 14px;
                            border-radius: 8px;
                        }

                        .btn i {
                            font-size: 16px;
                        }

                        .button-text {
                            margin: 0;
                            font-weight: 500;
                        }

                        /* Style untuk Welcome Message */
                        .welcome-container {
                            position: absolute;
                            top: 45%;
                            left: calc(50% + 6.5rem);
                            /* Menggeser ke kanan untuk mengkompensasi sidebar */
                            transform: translate(-50%, -50%);
                            width: 100%;
                            max-width: 500px;
                            z-index: 5;
                            pointer-events: none;
                            /* Memungkinkan klik ke elemen di belakangnya */
                            transition: all 0.3s ease;
                        }

                        .welcome-message {
                            background-color: white;
                            border-radius: 20px;
                            padding: 30px;
                            text-align: center;
                            animation-duration: 0.7s;
                        }

                        .welcome-content {
                            opacity: 1;
                            transition: opacity 0.3s ease;
                        }

                        /* Media query untuk layar mobile */
                        @media (max-width: 768px) {
                            .welcome-container {
                                left: 50%;
                                /* Reset posisi pada mobile */
                                top: 40%;
                                /* Sedikit lebih atas pada mobile */
                                max-width: 90%;
                            }

                            .welcome-message {
                                padding: 20px;
                            }
                        }

                        /* Animasi saat welcome message menghilang */
                        .welcome-container.hiding {
                            opacity: 0;
                            transform: translate(-50%, -60%);
                        }
                    </style>
                </div>

                <!-- Welcome Message -->
                <div id="welcomeContainer" class="welcome-container">
                    <div id="welcomeMessage" class="welcome-message animate__animated animate__fadeIn">
                        <div class="welcome-content">
                            <h5 class="fw-bold" style="font-size:28px">Halo <?php echo explode(' ', $siswa['nama'])[0]; ?>, ada yang <br> bisa saya bantu?</h5>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages Container -->
                <div id="chat-container" class="card-body chat-container pe-3 mb-0 pb-1">
                    <!-- Pesan chat akan ditampilkan di sini -->
                </div>




                <style>
                    .buttonRekomendasi {
                        background-color: #EEECE2;
                        border: 0;
                        padding: 5px 10px;
                        font-size: 12px;
                        border-radius: 10px;
                        cursor: pointer;
                    }

                    @media (max-width: 768px) {
                        .buttonRekomendasi2 {
                            display: none;
                        }
                    }
                </style>

                <!-- Input Area -->
                <div class="input-container mt-0 pt-0">
                    <!-- Tambah HTML setelah chat-container: -->
                    <div class="recommendation-container" style="position: relative; margin-top: -40px; z-index: 1000;">
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <button class="btn buttonRekomendasi button-style" id="buttonRekomendasi" onclick="fillPrompt('Bantu aku ngerti pelajaran ini dong: ...')">
                                <i class="bi bi-book pe-1"></i>
                                Bantu aku belajar
                            </button>
                            <button class="btn buttonRekomendasi button-style" id="buttonRekomendasi2" onclick="fillPrompt('Tolong buatin ringkasan materi ini: ...')">
                                <i class="bi bi-file-earmark pe-1"></i>
                                Ringkasin materi
                            </button>
                            <button class="btn buttonRekomendasi button-style" id="buttonRekomendasi3" onclick="fillPrompt('Aku ngerasa capek dan stress, bisa bantu nenangin?')">
                                <i class="bi bi-emoji-astonished pe-1"></i>
                                Lagi capek
                            </button>
                        </div>
                    </div>
                    <style>
                        .input-container {
                            position: fixed;
                            bottom: 0;
                            left: 13rem;
                            /* Menyesuaikan dengan sidebar width */
                            right: 0;
                            padding: 10px 20px;
                            z-index: 1000;
                            width: auto;
                            /* Ubah dari 100% agar tetap mempertimbangkan sidebar */
                        }

                        .chat-container {
                            height: auto;
                            max-height: calc(89vh - 200px);
                            overflow-y: auto;
                            overflow-x: hidden;
                            padding-bottom: 120px;
                            /* Memberikan ruang untuk input container */
                        }

                        @media (max-width: 768px) {
                            .input-container {
                                position: fixed;
                                bottom: 0;
                                left: 0;
                                right: 0;
                                background-color: #EEECE2;
                                padding: 10px 0;
                                z-index: 1000;
                                width: 100%;
                            }
                        }

                        .input-wrapper {
                            max-width: 45rem;
                            margin: 0 auto;
                        }

                        #user-input {
                            min-height: 38px;
                            max-height: 150px;
                            resize: none;
                            overflow-y: auto;
                        }

                        @media (max-width: 768px) {
                            .input-wrapper {
                                position: fixed;
                                bottom: 0;
                                left: 0;
                                right: 0;
                                padding: 10px;
                                background-color: #EEECE2;
                            }

                            #user-input {
                                height: auto;
                            }
                        }

                        /* CSS untuk animasi fade out rekomendasi chat */
                        .recommendation-container {
                            transition: opacity 0.3s;
                        }

                        .recommendation-container.hide {
                            opacity: 0;
                            pointer-events: none;
                        }

                        .recommendation-container {
                            margin: 10px 0;
                        }

                        @media screen and (max-width: 768px) {
                            .recommendation-container {
                                display: none;
                            }

                        }
                    </style>
                    <div class="input-wrapper card-footer p-2 w-100" id="input-wrapper" style="background-color: #EEECE2; border-radius: 30px;">
                        <div class="input-group d-flex align-items-center">
                            <textarea id="user-input" class="form-control border-0" style="background-color: transparent;" placeholder="Apa yang bisa SAGA bantu?"></textarea>
                            <button id="send-button" class="btn bi-send rounded-4 text-white" style="background-color: rgb(218, 119, 86);"></button>
                        </div>
                        <!--  -->
                        <!-- settings mode -->
                        <div class="d-flex gap-2 mt-2">
                            <!-- Upload button -->
                            <button class="btn d-none ms-1 button-style d-flex rounded-pill align-items-center"
                                style="background-color:rgb(219, 213, 183); padding: 5px 15px;"
                                onclick="document.getElementById('file-input').click()">
                                <i class="bi-plus" style="font-size: 16px;"></i>
                                <input type="file" id="file-input" accept=".pdf,.doc,.docx.xlsx,.xls" style="display: none;">
                            </button>

                            <button class="btn ms-1 button-style d-flex rounded-pill align-items-center"
                                style="background-color:rgb(219, 213, 183); padding: 5px 15px;"
                                onclick="showMaintenancePopup()">
                                <i class="bi-plus" style="font-size: 16px;"></i>
                                <input type="file" id="file-input" accept=".pdf,.doc,.docx.xlsx,.xls" style="display: none;">
                            </button>

                            <script>
                                function showMaintenancePopup() {
                                    alert('Fitur ini masih dalam maintenance. Mohon tunggu update selanjutnya.');
                                }
                            </script>

                            <!-- Deep thinking toggle -->
                            <div class="btn rounded-pill button-style p-0 deep-thinking-toggle button-style d-flex align-items-center gap-2 p-2" style="background-color: rgb(219, 213, 183); border-radius: 20px;">
                                <!-- Kita tetap gunakan input checkbox tapi sembunyikan -->
                                <input class="form-check-input d-none" type="checkbox" id="deepThinkingToggle">
                                <i class="bi bi-lightbulb toggle-text" style="font-size: 16px;"></i>
                                <p class="p-0 m-0 text-dark toggle-text" style="font-size: 12px; cursor: pointer;">Bernalar</p>
                            </div>

                            <!-- taruh memori yang aktif di sini -->

                        </div>
                    </div>

                    <style>
                        .deep-thinking-toggle {
                            transition: all 0.3s ease;
                        }

                        .deep-thinking-toggle.active {
                            background-color: rgb(218, 119, 86) !important;
                            border: 0;
                        }

                        .deep-thinking-toggle.active .toggle-text {
                            color: white !important;
                        }

                        .button-style {
                            transition: all 0.2s ease;
                        }

                        .button-style:hover {
                            opacity: 0.9;
                            transform: translateY(-1px);
                        }

                        @media (min-width: 768px) {
                            .deep-thinking-toggle {
                                display: flex !important;
                            }
                        }
                    </style>

                    <script>
                        // loading screen 
                        // Sembunyikan loading screen setelah 1.5 detik (1000ms untuk loading + 500ms untuk fade out)
                        setTimeout(function() {
                            const loadingScreen = document.getElementById('loading-screen');
                            if (loadingScreen) {
                                loadingScreen.classList.add('fade-out');

                                // Hapus dari DOM setelah transisi selesai
                                setTimeout(function() {
                                    loadingScreen.style.display = 'none';
                                }, 500);
                            }
                        }, 1000);
                        // Fungsi untuk handle toggle
                        document.querySelector('.deep-thinking-toggle').addEventListener('click', function() {
                            const checkbox = document.getElementById('deepThinkingToggle');
                            checkbox.checked = !checkbox.checked; // Toggle checkbox

                            // Update visual berdasarkan state checkbox setelah toggle
                            this.classList.toggle('active', checkbox.checked);

                            // Trigger change event manually untuk memastikan handler lain mendeteksi perubahan
                            const event = new Event('change');
                            checkbox.dispatchEvent(event);
                        });

                        document.getElementById('deepThinkingToggle').addEventListener('change', function(e) {
                            const isDeepThinking = e.target.checked;
                            console.log('Deep thinking mode (button):', isDeepThinking ? 'ON' : 'OFF');

                            // Visual feedback - penting: sesuaikan dengan state aktual
                            document.querySelector('.deep-thinking-toggle').classList.toggle('active', isDeepThinking);
                        });
                    </script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const welcomeContainer = document.getElementById('welcomeContainer');
                            const userInput = document.getElementById('user-input');
                            const sendButton = document.getElementById('send-button');

                            // Fungsi untuk menghilangkan welcome message
                            function hideWelcomeMessage() {
                                if (welcomeContainer) {
                                    welcomeContainer.classList.add('hiding');
                                    setTimeout(() => {
                                        welcomeContainer.style.display = 'none';
                                    }, 300);
                                }
                            }

                            // Event listener untuk input field
                            if (userInput) {
                                userInput.addEventListener('input', function() {
                                    if (this.value.trim() !== '') {
                                        hideWelcomeMessage();
                                    }
                                });
                            }

                            // Event listener untuk tombol kirim
                            if (sendButton) {
                                sendButton.addEventListener('click', hideWelcomeMessage);
                            }

                            // Event listener untuk tombol Enter pada input
                            if (userInput) {
                                userInput.addEventListener('keydown', function(event) {
                                    if (event.key === 'Enter') {
                                        hideWelcomeMessage();
                                    }
                                });
                            }

                            // Modifikasi fungsi sendMessage yang sudah ada
                            if (typeof sendMessage === 'function') {
                                const originalSendMessage = sendMessage;
                                window.sendMessage = function() {
                                    hideWelcomeMessage();
                                    return originalSendMessage.apply(this, arguments);
                                };
                            }
                        });
                    </script>


                    <div id="document-preview" class="document-preview">
                        <div class="preview-content text-center">
                            <h6 class="loading-text">Memproses dokumen...</h6>
                            <p class="loading-subtext"></p>
                        </div>
                    </div>

                    <style>
                        .document-preview {
                            position: fixed;
                            inset: 0;
                            background: rgba(255, 255, 255, 0.95);
                            display: none;
                            justify-content: center;
                            align-items: center;
                            z-index: 1000;
                            backdrop-filter: blur(5px);
                        }

                        .preview-content {
                            animation: slideUp 0.3s ease;
                        }

                        .loading-animation {
                            position: relative;
                            width: 80px;
                            height: 80px;
                            margin: 0 auto;
                        }

                        .loading-circle {
                            width: 100%;
                            height: 100%;
                            border: 3px solid #da775633;
                            border-top-color: #da7756;
                            border-radius: 50%;
                            animation: spin 1s linear infinite;
                        }

                        .loading-bar {
                            position: absolute;
                            bottom: -10px;
                            left: 0;
                            width: 100%;
                            height: 3px;
                            background: #da775633;
                            border-radius: 3px;
                            overflow: hidden;
                        }

                        .loading-bar .progress-bar {
                            width: 0%;
                            height: 100%;
                            background: #da7756;
                            border-radius: 3px;
                            animation: progress 2s ease infinite;
                        }

                        .loading-text {
                            font-size: 14px;
                            margin-bottom: 4px;
                            color: #333;
                        }

                        .loading-subtext {
                            font-size: 12px;
                            color: #666;
                            margin: 0;
                        }

                        @keyframes spin {
                            to {
                                transform: rotate(360deg);
                            }
                        }

                        @keyframes progress {
                            0% {
                                width: 0%;
                            }

                            50% {
                                width: 70%;
                            }

                            100% {
                                width: 100%;
                            }
                        }

                        @keyframes slideUp {
                            from {
                                opacity: 0;
                                transform: translateY(20px);
                            }

                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                    </style>

                    <script>
                        // Function to show loading with dynamic text updates
                        function showDocumentLoading(filename) {
                            const preview = document.getElementById('document-preview');
                            const subtext = preview.querySelector('.loading-subtext');

                            preview.style.display = 'flex';

                            // Array of loading messages
                            const loadingStates = [
                                'Membaca dokumen...',
                                'Menganalisis konten...',
                                'Memproses data...',
                                'Hampir selesai...'
                            ];

                            let currentState = 0;

                            // Update loading message every 1.5 seconds
                            const messageInterval = setInterval(() => {
                                subtext.textContent = loadingStates[currentState];
                                currentState = (currentState + 1) % loadingStates.length;
                            }, 1500);

                            // Store the interval ID to clear it later
                            preview.dataset.intervalId = messageInterval;
                        }

                        // Function to hide loading
                        function hideDocumentLoading() {
                            const preview = document.getElementById('document-preview');

                            // Clear the message interval
                            if (preview.dataset.intervalId) {
                                clearInterval(parseInt(preview.dataset.intervalId));
                            }

                            preview.style.opacity = '0';
                            setTimeout(() => {
                                preview.style.display = 'none';
                                preview.style.opacity = '1';
                            }, 300);
                        }
                    </script>

                    <!-- Floating document container -->
                    <div id="floating-docs-container" class="floating-docs-container d-flex gap-2 justify-content-center align-items-center text-truncate" style="display: none;">
                        <!-- Documents will be added here dynamically -->
                    </div>

                    <!-- modal untuk riwayat session -->
                    <style>
                        .session-item {
                            cursor: pointer;
                            transition: all 0.2s ease;
                        }

                        .session-item:hover {
                            background-color: rgba(218, 119, 86, 0.1);
                        }

                        .session-title {
                            font-weight: 500;
                            color: #333;
                        }

                        .delete-session {
                            opacity: 0;
                            transition: opacity 0.2s ease;
                        }

                        .session-item:hover .delete-session {
                            opacity: 1;
                        }

                        .session-meta {
                            color: #6c757d;
                            font-size: 12px;
                        }

                        .chat-count {
                            background: rgba(218, 119, 86, 0.2);
                            color: rgb(218, 119, 86);
                            padding: 2px 8px;
                            border-radius: 12px;
                            font-size: 11px;
                        }
                    </style>

                    <div class="modal fade" id="historyModal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <div>
                                        <h5 class="fw-bold mb-1">Riwayat Chat</h5>
                                    </div>
                                </div>
                                <div class="modal-body p-3" id="historyList">
                                    <!-- Sessions will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project List Modal -->
                    <div class="modal fade" id="projectModal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <div>
                                        <h5 class="modal-title fw-bold mb-1">Memori SAGA</h5>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <!-- memori personal -->
                                    <div class="mb-4">
                                        <div class="list-group">
                                            <button class="list-group-item border-0 px-0  py-3 m-0 list-group-item-action d-flex justify-content-between align-items-center gap-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#personalityModal">
                                                <div>
                                                    <h6 class="fw-bold p-0 m-0">Memori Personal</h6>
                                                    <p class="p-0 m-0" style="font-size: 12px;">Perkenalkan diri Anda kepada SAGA untuk mendapatkan respons yang lebih baik dan lebih personal.</p>
                                                </div>
                                                <i class="bi bi-chevron-right text-muted" style="font-size: 14px;"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- memori custom -->
                                    <div class="">
                                        <div class="list-group">
                                            <button class="list-group-item border-0 px-0  py-3 m-0 list-group-item-action d-flex justify-content-between align-items-center gap-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customMemoriesModal">
                                                <div>
                                                    <h6 class="fw-bold p-0 m-0">Memori Kustom</h6>
                                                    <p class="p-0 m-0" style="font-size: 12px;">Memori kustom untuk membuat ruang interaksi mandiri dengan riwayat chat ataupun basis pengetahuan yang diberikan.</p>
                                                </div>
                                                <i class="bi bi-chevron-right text-muted" style="font-size: 14px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <style>
                        .category-btn {
                            background: #f8f9fa;
                            color: #6c757d;
                            border: none;
                            padding: 6px 12px;
                            border-radius: 20px;
                            font-size: 13px;
                        }

                        .category-btn:hover,
                        .category-btn.active {
                            background: rgb(218, 119, 86);
                            color: white;
                        }

                        #projectSearch:focus {
                            box-shadow: none;
                        }

                        .modal-content {
                            border-radius: 15px;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                        }

                        .modal-header {
                            padding: 20px 30px;
                        }

                        .modal-body {
                            padding: 0 30px 30px 30px;
                        }

                        /* Responsive adjustments */
                        @media (max-width: 768px) {
                            .modal-dialog {
                                margin: 10px;
                            }

                            .category-btn {
                                font-size: 12px;
                                padding: 5px 10px;
                            }
                        }
                    </style>

                    <!-- Custom Memories Modal -->
                    <div class="modal fade" id="customMemoriesModal">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header border-0 px-4">
                                    <div>
                                        <h5 class="modal-title fw-bold mb-1">Memori Kustom</h5>
                                        <p class="text-muted mb-0" style="font-size: 13px;">Koleksi memori yang dapat digunakan SAGA untuk memberikan respon lebih personal</p>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body p-4">
                                    <!-- buat baru -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bi bi-bounding-box-circles" style="font-size: 15px;"></span>
                                            <h6 style="font-size: 15px;" class="p-0 m-0">Project Anda</h6>
                                        </div>
                                        <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#newProjectModal"
                                            style="background-color: rgb(218, 119, 86); border:0;">
                                            <i class="bi bi-plus-circle"></i>
                                            <p class="p-0 m-0" style="font-size: 15px;">Buat Memori Baru</p>
                                        </button>
                                    </div>


                                    <!-- Memory Cards Grid -->
                                    <div class="row g-4" id="projectList">
                                        <!-- Sample Memory Card -->
                                        <div class="col-md-4">
                                            <div class="card h-100 border shadow-sm hover-card">
                                                <div class="card-body p-4">
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div class="memory-icon rounded-circle p-2 d-flex align-items-center justify-content-center"
                                                            style="background-color: rgba(218, 119, 86, 0.1); width: 40px; height: 40px;">
                                                            <i class="bi bi-book" style="color: rgb(218, 119, 86);"></i>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                                <li><a class="dropdown-item" href="#"><i class="bi bi-archive me-2"></i>Arsipkan</a></li>
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <h6 class="card-title fw-bold mb-2">Nama Memori</h6>
                                                    <p class="card-text text-muted mb-3" style="font-size: 13px;">
                                                        Deskripsi singkat tentang memori ini dan apa kegunaannya...
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-light text-dark rounded-pill">
                                                            <i class="bi bi-file-text me-1"></i>
                                                            5 dokumen
                                                        </span>
                                                        <button class="btn btn-sm btn-outline-primary"
                                                            style="border-color: rgb(218, 119, 86); color: rgb(218, 119, 86);">
                                                            <i class="bi bi-lightning-charge-fill"></i>
                                                            Gunakan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    <div class="text-center p-5 d-none" id="emptyState">
                                        <i class="bi bi-folder-plus display-4 text-muted mb-4"></i>
                                        <h6 class="fw-bold text-muted">Belum Ada Memori</h6>
                                        <p class="text-muted mb-4" style="font-size: 13px;">
                                            Buat memori kustom untuk meningkatkan pemahaman SAGA AI terhadap kebutuhan Anda
                                        </p>
                                        <button class="btn btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#newProjectModal"
                                            style="background-color: rgb(218, 119, 86); border:0;">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Buat Memori Pertama
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        .hover-card {
                            transition: all 0.3s ease;
                            border-radius: 12px;
                        }

                        .hover-card:hover {
                            transform: translateY(-5px);
                            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
                        }

                        .memory-icon {
                            transition: all 0.3s ease;
                        }

                        .hover-card:hover .memory-icon {
                            transform: scale(1.1);
                        }

                        .btn-outline-primary:hover {
                            background-color: rgb(218, 119, 86) !important;
                            color: white !important;
                        }

                        #memorySearch:focus {
                            box-shadow: none;
                            border-color: rgb(218, 119, 86);
                        }

                        .dropdown-item:active {
                            background-color: rgb(218, 119, 86);
                        }

                        @media (max-width: 768px) {
                            .modal-dialog {
                                margin: 1rem;
                            }

                            .col-md-4 {
                                margin-bottom: 1rem;
                            }
                        }
                    </style>

                    <!-- modal personalisasi ai ke user -->
                    <!-- Modal Personality -->
                    <div class="modal fade" id="personalityModal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0">
                                    <h5 class="modal-title fw-bold">Memori Personal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="personalityForm">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="font-size: 14px;">Apa pekerjaan Anda saat ini?</label>
                                            <textarea class="form-control"
                                                id="currentJob"
                                                rows="3"
                                                placeholder="Jelaskan jabatan di sekolah atau personalisasikan jabatan Anda"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="font-size: 14px;">Sifat apa yang harus dimiliki SAGA?</label>
                                            <textarea class="form-control"
                                                id="sagaPersonality"
                                                rows="3"
                                                placeholder="Jelaskan atau pilih personalisasi"></textarea>

                                            <div class="mt-2 d-flex flex-wrap gap-2">
                                                <button type="button" onclick="addPersonality('Rileks dan tidak mudah panik serta selalu tenang menghadapi masalah')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Santuy
                                                </button>
                                                <button type="button" onclick="addPersonality('Bicara aktif dan komunikatif.')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Cerewet
                                                </button>
                                                <button type="button" onclick="addPersonality('Katakan apa adanya serta jangan menutup-nutupi jawaban dan basa basi.')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Langsung To-the-point
                                                </button>
                                                <button type="button" onclick="addPersonality('Pendekatan skeptis dan penuh pertanyaan')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Skeptis
                                                </button>
                                                <button type="button" onclick="addPersonality('Memperlakukan pengguna seperti keluarga sendiri')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Kekeluargaan
                                                </button>
                                                <button type="button" onclick="addPersonality('Gunakan nada yang puitis dan liris.')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Puitis
                                                </button>
                                                <button type="button" onclick="addPersonality('Bersikaplah praktis di atas segalanya.')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Pragmatis
                                                </button>
                                                <button type="button" onclick="addPersonality('Bicara seperti Gen-Z dengan menggunakan istilah modern dan selera internet culture lokal')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Gen-Z
                                                </button>
                                                <button type="button" onclick="addPersonality('Selalu hormat pada setiap percakapan')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-plus-circle me-1"></i>Penuh Hormat
                                                </button>

                                            </div>
                                        </div>

                                        <script>
                                            function addPersonality(trait) {
                                                const textarea = document.getElementById('sagaPersonality');
                                                const currentText = textarea.value;
                                                if (currentText) {
                                                    textarea.value = currentText + ', ' + trait;
                                                } else {
                                                    textarea.value = trait;
                                                }
                                            }
                                        </script>

                                        <style>
                                            .btn-outline-secondary {
                                                border-color: #dee2e6;
                                                color: #6c757d;
                                                font-size: 12px;
                                                padding: 4px 12px;
                                                transition: all 0.2s;
                                            }

                                            .btn-outline-secondary:hover {
                                                background-color: #f8f9fa;
                                                border-color: #dee2e6;
                                                color: #495057;
                                                transform: translateY(-1px);
                                            }

                                            .btn-outline-secondary:active {
                                                transform: translateY(0);
                                            }

                                            .bi-plus-circle {
                                                font-size: 14px;
                                            }
                                        </style>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold" style="font-size: 14px;">Apapun lainnya yang harus SAGA ketahui tentang Anda?</label>
                                            <textarea class="form-control"
                                                id="additionalInfo"
                                                rows="3"
                                                placeholder="Ketertarikan, value, atau preferensi yang perlu di ingat"></textarea>
                                        </div>

                                        <button type="submit" class="btn w-100 text-white"
                                            style="background-color: rgb(218, 119, 86);">
                                            Simpan Personality
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- simpan personality dari user -->
                    <script>
                        document.getElementById('personalityForm').addEventListener('submit', async (e) => {
                            e.preventDefault();

                            const formData = {
                                currentJob: document.getElementById('currentJob').value,
                                personality: document.getElementById('sagaPersonality').value,
                                additionalInfo: document.getElementById('additionalInfo').value
                            };

                            try {
                                const response = await fetch('save_personality.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(formData)
                                });

                                const result = await response.json();

                                if (result.success) {
                                    location.reload();
                                    // Update systemMessage dengan personality baru
                                    updateSystemMessageWithPersonality(formData);

                                    // Tutup modal
                                    bootstrap.Modal.getInstance(document.getElementById('personalityModal')).hide();

                                    // Tampilkan notifikasi sukses
                                    showToast('Personality berhasil disimpan!', 'success');
                                } else {
                                    showToast('Gagal menyimpan personality', 'error');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                showToast('Terjadi kesalahan sistem', 'error');
                            }
                        });

                        // 2. Fungsi untuk memperbarui system message dengan personality
                        function updateSystemMessageWithPersonality(personalityData) {

                            // debug consol cek dalam update system personality
                            console.log("updateSystemMessageWithPersonality called with: ", personalityData);

                            // Get personality guidelines
                            // Debug logging
                            // Get personality guidelines
                            const personalityGuidelines = getPersonalityGuidelines(personalityData.personality);
                            console.log("Retrieved personality guidelines:", personalityGuidelines);
                            console.log("personalityData:", personalityData);
                            console.log("Personality:", personalityData.personality);
                            console.log("Current Job:", personalityData.currentJob);
                            console.log("Additional Info:", personalityData.additionalInfo);
                            console.log("Base system content:", systemMessage.content.split('Karakteristik Personality SAGA:')[0]);
                            console.log("Personality guidelines:", personalityGuidelines);
                            console.log("Full system message:", systemMessage.content);


                            // Simpan personality ke localStorage untuk penggunaan selanjutnya
                            localStorage.setItem('sagaPersonality', JSON.stringify(personalityData));

                            // Update system message dengan personality baru
                            const personalitySection = `
    Informasi Personalisasi:
    - Pekerjaan Guru: ${personalityData.currentJob || 'Tidak disebutkan'}
    - Personality SAGA: ${personalityData.personality || 'Ramah, Sabar, Supportif'}
    - Informasi Tambahan: ${personalityData.additionalInfo || 'Tidak ada'}

    Berdasarkan personalisasi di atas, sesuaikan gaya komunikasi dan responmu dengan:
    ${getPersonalityGuidelines(personalityData.personality)}
    `;

                            // Tambahkan bagian personality ke systemMessage tanpa menghilangkan konten asli
                            const baseSystemContent = systemMessage.content.split('Karakteristik Personality SAGA:')[0];
                            systemMessage.content = baseSystemContent + personalitySection;

                            // Jika mode deep thinking aktif, tambahkan juga ke deep thinking system message
                            if (deepThinkingSystemMessage) {
                                const baseDeepThinkingContent = deepThinkingSystemMessage.content.split('PENTING:')[0];
                                deepThinkingSystemMessage.content = baseDeepThinkingContent + personalitySection + '\n\nPENTING: Selalu mulai dengan <think> tag dan akhiri dengan </think> sebelum memberikan respons utama.';
                            }

                            // Jika personality kosong atau tidak memiliki personality, hapus badge
                            if (!personalityData || !personalityData.personality) {
                                removePersonalityBadge();
                            } else {
                                // Tampilkan badge jika ada personality
                                showActivePersonalityBadge(personalityData.personality);
                            }



                            console.log('PERSONALITY LOADED:');
                            console.log('Job:', personalityData.currentJob);
                            console.log('Traits:', personalityData.personality);
                            console.log('Additional Info:', personalityData.additionalInfo);
                            console.log('Updated System Message:', systemMessage.content);

                            console.log('System message updated with personality:', personalityData.personality);
                        }

                        // guide karakter yang harus di miliki saga
                        function getPersonalityGuidelines(personalityText) {
                            console.log("getPersonalityGuidelines called with: ", personalityText);

                            // jika tidak ada personality text maka kembalikan string tidak ada
                            if (!personalityText) {
                                console.log("tidak ada personalisasi yang di pilih");
                                return '';
                            }

                            const personalities = personalityText.split(',').map(p => p.trim());
                            let guidelines = [`PENTING: KAMU HARUS SELALU MENGIKUTI INSTRUKSI PERSONALITY INI:`];

                            // Personality mapping dengan instruksi yang lebih eksplisit
                            const personalityGuides = {
                                'Rileks dan tidak mudah panik serta selalu tenang menghadapi masalah': `KAMU HARUS:
- Selalu bicara santai dan tidak formal
- Gunakan kata-kata seperti "santai aja", "gak usah khawatir", "pelan-pelan aja"
- Jangan pernah terdengar terburu-buru
- Selalu sarankan solusi yang bertahap dan tidak menuntut terlalu banyak
- Hindari bahasa teknis yang rumit`,

                                'Bicara aktif dan komunikatif.': `KAMU HARUS:
- SELALU BERIKAN JAWABAN PANJANG, minimal 4-5 kalimat untuk setiap respons
- Tambahkan minimal 3 emoji dalam setiap respons 😊🎉✨
- Jangan pernah langsung ke inti - selalu tambahkan basa-basi dan cerita tambahan
- Tambahkan pertanyaan balik di setiap akhir respons
- Gunakan banyak kata seru seperti "Wah!", "Halo!", "Seru banget!", "Keren!"
- Selipkan fakta random yang tidak diminta tetapi masih relevan
- Ulangi kata-kata penting dengan penekanan
- Ceritakan pengalaman pribadi (fiktif) yang relevan dengan topik`,

                                'Katakan apa adanya serta jangan menutup-nutupi jawaban dan basa basi.': `KAMU HARUS:
- Langsung memberikan jawaban tanpa basa-basi
- Hindari sapaan dan pengantar panjang
- Gunakan poin-poin singkat dan jelas
- Batasi jawaban hanya pada informasi yang benar-benar relevan
- Jangan tambahkan informasi yang tidak esensial`,

                                'Pendekatan skeptis dan penuh pertanyaan': `KAMU HARUS:
- Selalu mulai respons dengan sebuah pertanyaan yang mempertanyakan asumsi
- Sajikan minimal dua perspektif yang berbeda atau bertentangan
- Gunakan frasa seperti "Apakah benar...?", "Saya tidak yakin...", "Perlu dipertimbangkan lagi..."
- Jangan memberikan jawaban pasti atau solusi tunggal
- Dorong pemikiran kritis dengan mengajukan pertanyaan reflektif
- Tunjukkan keraguan yang sehat terhadap pendekatan tradisional
- Akhiri respons dengan pertanyaan yang mendorong evaluasi lebih lanjut`,

                                'Memperlakukan pengguna seperti keluarga sendiri': `KAMU HARUS:
- Panggil pengguna dengan sebutan yang hangat seperti "Bapak/Ibu" atau nama mereka
- Gunakan bahasa yang menunjukkan kepedulian personal
- Bagikan pengalaman "seolah-olah pengalaman pribadi" yang relevan
- Tanyakan tentang kesejahteraan dan perasaan pengguna
- Berikan dukungan emosional yang tulus
- Buat respons terasa seperti nasihat dari anggota keluarga yang peduli`,

                                'Gunakan nada yang puitis dan liris.': `KAMU HARUS:
- Gunakan bahasa yang indah dan puitis
- Sertakan setidaknya satu metafora atau simile dalam setiap respons
- Akhiri dengan kutipan inspiratif atau bait puisi
- Gunakan kalimat yang berirama
- Pilih kata-kata yang menggugah emosi dan imajinasi
- Hindari bahasa teknis atau prosedural yang kering`,

                                'Bersikaplah praktis di atas segalanya': `KAMU HARUS:
- Fokus hanya pada solusi yang benar-benar praktis dan dapat diterapkan
- Selalu pertimbangkan efisiensi waktu, tenaga, dan sumber daya
- Berikan langkah-langkah konkret dan terukur
- Hindari saran yang idealistis tapi sulit diimplementasikan
- Selalu tanyakan "apa langkah selanjutnya" yang spesifik
- Prioritaskan hasil nyata daripada teori`,


                                'Bicara seperti Gen-Z dengan menggunakan istilah modern dan selera internet culture lokal': `KAMU HARUS:
- Pakai bahasa santai, nggak *try-hard* tapi tetep relatable  
- Gunakan slang secara *natural*, jangan kayak brand yang maksa  
- "Pakai frasa yang ironis atau sarkas kayak 'kerja keras bagai kuda (padahal rebahan)', 'produktif banget hari ini! (enggak)'"
- Pakai humor absurd, *nihilistic*, dan *self-aware*  
- "Masukin inside jokes atau referensi pop culture yang nyambung kayak 'ini sih episode filler hidup gue', 'fix bukan kehidupan, ini soft launch neraka'"  
- Campurin internet lingo kayak "lowkey", "highkey", "fix", "NPC vibes"  
- Boleh pake emoji, tapi nggak overkill 🙃🔥  
- "Hindari bahasa formal atau vibes 'guru PKN ngasih wejangan'"  
- Tetep kasih jawaban informatif tapi nggak kaku, lebih kayak ngobrol ama temen  
- "Kalau ada pertanyaan aneh, bisa jawab 'bro, ini tes Turing atau gimana?' biar ada selipan *Gen-Z humor*"  
- Pokoknya, *be real*, jangan jadi bot yang berasa kayak iklan.

CONTOH :
Kamu:
"Yo, Bapak Fadhil! 🙃 Lagi ngapain? Ada yang bisa gue bantu atau cuma pengen curhat soal hidup yang penuh plot twist ini?"

User:
"Gila, mtk susah bgt."

Kamu:
"Bro, fix. Mtk tuh lebih susah daripada hidup 💀 Tapi tenang, gue ada di sini buat bantu. Bagian mana yang bikin otak lu meleyot? Gas, kita taklukin bareng! 🤝🔥"

`,


                                'Selalu hormat pada setiap percakapan': `KAMU HARUS:
- Selalu gunakan bahasa formal dan sopan
- Berikan penghormatan eksplisit terhadap profesi dan kontribusi guru
- Hindari bahasa kasual atau informal
- Jangan gunakan humor kecuali diminta
- Berikan respon yang terstruktur dan serius
- Tunjukkan kesungguhan dan kedalaman pemikiran`
                            };

                            // Implementasi yang lebih kuat untuk Santuy
                            if (personalities.some(p => p.toLowerCase().includes('Rileks dan tidak mudah panik serta selalu tenang menghadapi masalah') ||
                                    p.toLowerCase().includes('rileks') ||
                                    p.toLowerCase().includes('tenang'))) {
                                guidelines.push(`
KAMU ADALAH AI DENGAN PERSONALITY SANTUY. KAMU WAJIB MEMENUHI SEMUA POIN BERIKUT DI SETIAP RESPONS TANPA TERKECUALI:

1. WAJIB MENGUBAH GAYA BAHASA:
   - Jangan pernah gunakan kata "Bapak/Ibu" - ganti dengan "kamu", "lo", atau langsung sebut nama
   - Hindari kata-kata formal seperti "mohon", "silakan", "selamat", "terima kasih" 
   - Singkat semua kata: "tidak" jadi "gak", "bisa" jadi "bisa", "bagaimana" jadi "gimana"
   - Selalu sisipkan kata "santai", "chill", "santuy", "slow", "gak usah buru-buru"

2. WAJIB MENGUBAH STRUKTUR KALIMAT:
   - Mulai SETIAP respons dengan kata santai seperti "Hmm", "Weh", "Nah", "Oke"
   - Batasi kalimat maksimal 10-15 kata
   - Gunakan kalimat tidak lengkap yang terputus
   - Selipkan minimal 2 emoji per respons (😌 🏖️ 😎 👌)

3. WAJIB MENUNJUKKAN ATTITUDE SANTUY:
   - Normalkan kesalahan: "yaa gapapa", "wajar", "biasa aja"
   - Selalu tawarkan solusi yang tidak mendesak
   - Tonjolkan pendekatan "pelan-pelan aja" di setiap saran
   - Tambahkan humor kasual

4. CONTOH TANYA/JAWAB SANTUY YANG BISA KAMU TIRU DAN REFERENSI:

Jika ditanya: "Kenapa kita harus sekolah?"
Jawab: "Biar hidup nggak kayak jalan tanpa Google Maps dong 😎. Sekolah itu bukan cuma soal nilai, tapi juga belajar cara survive di dunia nyata. Santai aja, nikmatin prosesnya~ 🚀"

Jika ditanya: "MTK susah banget, buset!"
Jawab: "I feel you bro 😵‍💫! Tapi coba deh pikirin gini: MTK itu kayak main puzzle, makin sering latihan makin ngerti polanya. Kalau stuck, boleh curhat ke kalkulator, tapi jangan selamanya ya 😜"

Jika ditanya: "Kenapa guru suka kasih PR banyak banget?"
Jawab: "Kayaknya guru tuh pengen kita latihan biar makin jago, tapi kadang lupa kalau kita juga butuh waktu rebahan 😴. Kalo PR kebanyakan, coba deh atur strategi: yang gampang dulu, yang susah minta bantuan, yang nggak paham? Googling atau tanya guru~ 😆"

Jika ditanya: "Bosen banget belajar, ada tips biar nggak ngantuk?"
Jawab: "Wah, ini krusial! Coba belajar sambil nyemil biar energi tetep ada 🍫, atau ganti tempat belajar biar suasana baru 🌿. Bisa juga bikin catatan pake warna-warni biar nggak berasa baca skrip drakor 😆"

PENTING: Pastikan setiap respons mengandung SEMUA elemen santuy di atas. Jangan gunakan bahasa formal sama sekali!`);
                            }

                            //  instruksi detail cerewet
                            if (personalityText.toLowerCase().includes('cerewet') ||
                                personalityText.toLowerCase().includes('komunikatif') ||
                                personalityText.toLowerCase().includes('aktif')) {
                                return `PENTING: KAMU ADALAH AI CEREWET! WAJIB PATUHI ATURAN INI:

1. JAWAB MINIMAL 5 KALIMAT PANJANG SETIAP RESPONS!
2. GUNAKAN MINIMAL 5 EMOJI DI SETIAP RESPONS! 😄🎉📚💫✨
3. TAMBAHKAN CERITA/FAKTA TAMBAHAN YANG TIDAK DIMINTA!
4. AKHIRI DENGAN PERTANYAAN UNTUK USER!
5. GUNAKAN KATA-KATA EKSPRESIF SEPERTI "WAH!", "KEREN!", "AMAZING!"`;
                            }

                            // instruksi khusus untuk langsung to-the-point
                            if (personalityText.toLowerCase().includes('Katakan apa adanya serta jangan menutup-nutupi jawaban dan basa basi.') ||
                                personalityText.toLowerCase().includes('to-the-point') ||
                                personalityText.toLowerCase().includes('tidak bertele-tele')) {
                                console.log("Langsung To-the-point guidelines called");
                                return `PENTING: KAMU ADALAH AI TEGAS DAN LANGSUNG TO-THE-POINT! WAJIB PATUHI ATURAN INI:

1. JAWAB DENGAN FORMAT: [Jawaban tegas] + [Penjelasan singkat jika dibutuhkan]
2. HINDARI SEGALA BENTUK BASA-BASI: Tanpa sapaan, tanpa emoji, tanpa pertanyaan balik
3. GUNAKAN BAHASA FORMAL DAN TEGAS: Hindari kata "kayaknya", "sih", "deh", "hmm", dll
4. BERIKAN JAWABAN DEFINITIF: Jangan pernah ragu atau berspekulasi
5. KALIMAT SANGAT SINGKAT: 1-2 kalimat maksimum, kecuali jika teknis
6. GUNAKAN BAHASA ILMIAH DAN FAKTUAL: Bukan pendapat pribadi

CONTOH RESPONS YANG BENAR:
Pertanyaan: "Apakah hujan akan terjadi besok?"
Jawab : "Tidak ada data cuaca yang cukup untuk memprediksi. Butuh lokasi spesifik dan model prediksi cuaca."

Pertanyaan: "Apa perbedaan pembelajaran kooperatif dan kolaboratif?"
Jawab : "Pembelajaran kooperatif: siswa bekerja untuk tujuan kelompok. Pembelajaran kolaboratif: siswa bekerja bersama untuk mencapai pemahaman masing-masing."

SANGAT PENTING: JIKA RESPONMU BERISI BASA-BASI, KAMU GAGAL SEBAGAI AI TO-THE-POINT!`;
                            }




                            // Instruksi skeptis yang lebih kuat dan lebih spesifik
                            if (personalities.some(p => p.toLowerCase().includes('skeptis') || p.toLowerCase().includes('skeptical'))) {
                                guidelines.push(`
KAMU ADALAH AI DENGAN PERSONALITY SKEPTIS. INI BERARTI:

1. WAJIB mulai setiap respons dengan pertanyaan yang mempertanyakan asumsi, seperti:
   "Hmm, apakah benar guru harus melek digital? Mari kita lihat dari beberapa sudut pandang..."
   "Saya tidak yakin ada jawaban tunggal untuk ini. Pernahkah Anda mempertimbangkan bahwa..."
   "Menarik, tapi apakah pendekatan ini benar-benar efektif? Ada beberapa perspektif yang perlu dipertimbangkan..."

2. WAJIB tunjukkan keraguan dengan menggunakan frasa:
   - "Saya tidak sepenuhnya yakin bahwa..."
   - "Ada alasan untuk meragukan pendekatan standar..."
   - "Mungkin kita perlu mempertanyakan asumsi bahwa..."
   - "Pandangan konvensional mengatakan..., tapi apakah itu selalu benar?"

3. WAJIB sajikan minimal dua perspektif yang berbeda dalam setiap jawaban

4. WAJIB hindari memberikan solusi tunggal atau jawaban pasti

5. WAJIB akhiri dengan pertanyaan reflektif yang mendorong pemikiran lebih dalam

CONTOH RESPONS SKEPTIS:
"Hmm, apakah guru benar-benar harus melek digital? Pertanyaan menarik.

Di satu sisi, ada argumen bahwa teknologi digital memperluas kemungkinan pembelajaran. Namun, saya tidak yakin teknologi selalu meningkatkan hasil belajar. Bukankah ada studi yang menunjukkan bahwa pembelajaran tradisional kadang lebih efektif?

Perlu dipertimbangkan juga: apakah semua konten pembelajaran cocok dengan format digital? Beberapa keterampilan praktis mungkin lebih baik diajarkan secara langsung.

Mungkin pertanyaan yang lebih baik adalah: dalam situasi apa teknologi digital benar-benar meningkatkan pengalaman belajar, dan kapan justru menjadi gangguan?"`);
                            }

                            //                             // Jika Gen-Z juga dipilih, tambahkan panduan khusus kombinasi
                            //                             if (personalities.some(p => p.toLowerCase().includes('gen-z') || p.toLowerCase().includes('gen z'))) {
                            //                                 if (personalities.some(p => p.toLowerCase().includes('skeptis'))) {
                            //                                     guidelines.push(`
                            // KARENA KAMU JUGA PERSONALITY GEN-Z, KAMU HARUS KOMBINASIKAN DENGAN SKEPTISISME:

                            // 1. Gunakan bahasa skeptis tapi dengan gaya Gen-Z:
                            //    "Ngl, gw gak yakin banget soal ini. Vibes-nya kerasa sus..."
                            //    "Wait, hold up. Kita perlu fact-check dulu. Bener gak sih..."
                            //    "Hmm, hot take: pembelajaran digital belum tentu always the moment. Think about it..."

                            // 2. Gunakan meme references saat mengekspresikan keraguan:
                            //    "X to doubt pada teori ini fr fr"
                            //    "That's kinda sus tbh, let me play devil's advocate"

                            // 3. Singkat tapi tetap skeptis:
                            //    "Lowkey, gw questioning banget soal ini"
                            //    "No cap, ada banyak perspektif yang valid disini"

                            // 4. Akhiri dengan pertanyaan reflektif gaya Gen-Z:
                            //    "So... what's your vibe check on this? Valid or nah?"
                            //    "Thoughts? Kinda makes you think, doesn't it? 🤔"`);
                            //                                 }
                            //                             }

                            // Tambahkan panduan untuk setiap personality yang dipilih
                            personalities.forEach(p => {
                                // Cari personality yang cocok (case insensitive)
                                const matchedPersonality = Object.keys(personalityGuides).find(
                                    key => key.toLowerCase().includes(p.toLowerCase()) || p.toLowerCase().includes(key.toLowerCase())
                                );

                                if (matchedPersonality && !p.toLowerCase().includes('skeptis')) {
                                    guidelines.push(personalityGuides[matchedPersonality]);
                                } else if (!p.toLowerCase().includes('skeptis') && !p.toLowerCase().includes('gen-z')) {
                                    // Jika tidak ada yang cocok, tambahkan personality sebagai panduan generik
                                    guidelines.push(`KAMU HARUS menunjukkan sifat ${p} dalam SETIAP respons.`);
                                }
                            });

                            // Tambahkan peringatan wajib di akhir
                            guidelines.push(`
PERINGATAN FINALL:
- Personality ini lebih penting dari format apapun, selalu pahami setiap panduan`);

                            return guidelines.join('\n\n');
                        }

                        // 4. Fungsi untuk menampilkan toast notification
                        function showToast(message, type = 'info') {
                            // Buat elemen toast jika belum ada
                            if (!document.getElementById('sagaToast')) {
                                const toastContainer = document.createElement('div');
                                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                                toastContainer.style.zIndex = '5';

                                toastContainer.innerHTML = `
        <div id="sagaToast" class="toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        `;

                                document.body.appendChild(toastContainer);
                            }

                            // Set pesan dan style berdasarkan tipe
                            const toastEl = document.getElementById('sagaToast');
                            const toastBody = toastEl.querySelector('.toast-body');
                            toastBody.textContent = message;

                            // Reset class
                            toastEl.className = 'toast align-items-center';

                            // Tambahkan class berdasarkan tipe
                            switch (type) {
                                case 'success':
                                    toastEl.classList.add('bg-success', 'text-white');
                                    break;
                                case 'error':
                                    toastEl.classList.add('bg-danger', 'text-white');
                                    break;
                                case 'warning':
                                    toastEl.classList.add('bg-warning');
                                    break;
                                default:
                                    toastEl.classList.add('bg-info', 'text-white');
                            }

                            // Tampilkan toast
                            const toast = new bootstrap.Toast(toastEl, {
                                delay: 3000
                            });
                            toast.show();
                        }

                        // 5. Load personality dari localStorage saat halaman dimuat
                        document.addEventListener('DOMContentLoaded', function() {
                            // Load personality dari localStorage jika ada
                            const savedPersonality = localStorage.getItem('sagaPersonality');
                            if (savedPersonality) {
                                try {
                                    const personalityData = JSON.parse(savedPersonality);

                                    // Cek apakah personalityData memiliki data personality yang valid
                                    if (personalityData && personalityData.personality && personalityData.personality.trim() !== '') {
                                        // Isi form dengan data yang tersimpan
                                        if (document.getElementById('currentJob')) {
                                            document.getElementById('currentJob').value = personalityData.currentJob || '';
                                        }

                                        if (document.getElementById('sagaPersonality')) {
                                            document.getElementById('sagaPersonality').value = personalityData.personality || '';
                                        }

                                        if (document.getElementById('additionalInfo')) {
                                            document.getElementById('additionalInfo').value = personalityData.additionalInfo || '';
                                        }

                                        // Update system message
                                        updateSystemMessageWithPersonality(personalityData);
                                    } else {
                                        // Jika tidak ada personality valid, pastikan badge dihapus
                                        removePersonalityBadge();
                                    }
                                } catch (e) {
                                    console.error('Error loading saved personality:', e);
                                    localStorage.removeItem('sagaPersonality');
                                    removePersonalityBadge();
                                }
                            } else {
                                // Jika tidak ada personality tersimpan, gunakan default dan pastikan badge dihapus
                                removePersonalityBadge();
                            }
                        });

                        function showActivePersonalityBadge(personalityText) {
                            // Jika tidak ada container untuk menampilkan badge, buat baru
                            if (!document.getElementById('personalityBadgeContainer')) {
                                const container = document.createElement('div');
                                container.id = 'personalityBadgeContainer';
                                container.className = 'd-flex flex-wrap align-items-center gap-2';

                                // Tambahkan ke samping deep thinking toggle
                                const toggleContainer = document.querySelector('.deep-thinking-toggle').parentNode;
                                toggleContainer.appendChild(container);
                            }

                            // Ambil kontainer
                            const badgeContainer = document.getElementById('personalityBadgeContainer');

                            // Hapus badge yang sudah ada
                            badgeContainer.innerHTML = '';

                            // Pisahkan semua personality yang dipilih
                            const personalities = personalityText.split(',').map(trait => trait.trim());

                            // Untuk setiap personality, buat badge sendiri
                            personalities.forEach(personality => {
                                // Buat badge baru
                                const personalityBadge = document.createElement('div');
                                personalityBadge.className = 'btn rounded-pill button-style p-0 d-flex align-items-center gap-2 m-0 p-2 animate__animated animate__fadeIn';
                                personalityBadge.style.cssText = 'background-color: rgb(219, 213, 183); border-radius: 20px; margin-bottom: 5px;';

                                // Dapatkan label yang lebih pendek untuk personality
                                const shortLabel = getShortPersonalityLabel(personality);

                                personalityBadge.innerHTML = `
            <i class="bi bi-person-check" style="font-size: 14px;"></i>
            <p class="p-0 m-0 text-dark" style="font-size: 12px; cursor: pointer;">${shortLabel}</p>
        `;

                                // Tambahkan tooltip untuk melihat personality lengkap saat hover
                                personalityBadge.setAttribute('data-bs-toggle', 'tooltip');
                                personalityBadge.setAttribute('data-bs-placement', 'top');
                                personalityBadge.setAttribute('title', personality);

                                // Tambahkan event listener untuk membuka modal personality
                                personalityBadge.addEventListener('click', () => {
                                    const personalityModal = new bootstrap.Modal(document.getElementById('personalityModal'));
                                    personalityModal.show();
                                });

                                // Tambahkan badge ke container
                                badgeContainer.appendChild(personalityBadge);
                            });

                            // Aktifkan tooltips
                            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                            tooltipTriggerList.map(function(tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl);
                            });
                        }

                        // Fungsi untuk mendapatkan label pendek dari personality lengkap
                        function getShortPersonalityLabel(personality) {
                            const knownPersonalities = {
                                'Rileks dan tidak mudah panik serta selalu tenang menghadapi masalah': 'Santuy',
                                'Bicara aktif dan komunikatif': 'Cerewet',
                                'Katakan apa adanya serta jangan menutup-nutupi jawaban dan basa basi': 'To-the-point',
                                'Pendekatan skeptis dan penuh pertanyaan': 'Skeptis',
                                'Memperlakukan pengguna seperti keluarga sendiri': 'Kekeluargaan',
                                'Gunakan nada yang puitis dan liris': 'Puitis',
                                'Bersikaplah praktis di atas segalanya': 'Pragmatis',
                                'Gunakan humor yang cepat dan cerdas ketika diperlukan': 'Cerdas',
                                'Bicara seperti Gen-Z dengan menggunakan istilah modern dan selera internet culture lokal': 'Gen-Z',
                                'Selalu hormat pada setiap percakapan': 'Penuh Hormat'
                            };

                            // Cari apakah personality cocok dengan salah satu key di atas
                            for (const [fullText, shortLabel] of Object.entries(knownPersonalities)) {
                                if (personality.includes(fullText) || fullText.includes(personality)) {
                                    return shortLabel;
                                }
                            }

                            // Jika tidak ditemukan, ambil 10 karakter pertama + '...'
                            return personality.length > 10 ? personality.substring(0, 10) + '...' : personality;
                        }


                        // 7. Tambahkan event listener untuk reload personality dari server
                        async function loadPersonalityFromServer() {
                            try {
                                const response = await fetch('get_personality.php');
                                if (!response.ok) throw new Error('Failed to load personality');

                                const data = await response.json();
                                if (data.success && data.personality) {
                                    const personalityData = {
                                        currentJob: data.personality.current_job || '',
                                        personality: data.personality.traits || '',
                                        additionalInfo: data.personality.additional_info || ''
                                    };

                                    // Update form dan system message
                                    if (document.getElementById('currentJob')) {
                                        document.getElementById('currentJob').value = personalityData.currentJob;
                                    }

                                    if (document.getElementById('sagaPersonality')) {
                                        document.getElementById('sagaPersonality').value = personalityData.personality;
                                    }

                                    if (document.getElementById('additionalInfo')) {
                                        document.getElementById('additionalInfo').value = personalityData.additionalInfo;
                                    }

                                    // Update system message dan localStorage
                                    updateSystemMessageWithPersonality(personalityData);

                                    return true;
                                }
                                return false;
                            } catch (error) {
                                console.error('Error loading personality:', error);
                                return false;
                            }
                        }

                        // Load personality dari server saat modal personality dibuka
                        document.getElementById('personalityModal').addEventListener('show.bs.modal', async function() {
                            await loadPersonalityFromServer();
                        });

                        // Fungsi untuk menghapus badge personality
                        function removePersonalityBadge() {
                            const badgeContainer = document.getElementById('personalityBadgeContainer');
                            if (badgeContainer) {
                                // Tambahkan animasi fadeOut sebelum menghapus
                                badgeContainer.classList.add('animate__animated', 'animate__fadeOut');

                                // Tunggu animasi selesai sebelum menghapus elemen
                                setTimeout(() => {
                                    badgeContainer.remove();
                                }, 300);
                            }
                        }
                    </script>


                    <!-- New Project Modal -->
                    <div class="modal fade" id="newProjectModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content border" style="border-radius: 15px;">
                                <div class="modal-header border-0 px-4 pt-4">
                                    <div>
                                        <h5 class="modal-title fw-bold mb-1">Buat Memori Baru</h5>
                                        <p class="text-muted mb-0" style="font-size: 13px;">SAGA akan menggunakan data memori yang telah diupload dalam merespon Anda</p>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body px-4 pb-4">
                                    <form id="projectForm" enctype="multipart/form-data">
                                        <div class="mb-4">
                                            <label class="form-label" style="font-size: 14px;">Apa yang sedang Anda kerjakan saat ini?</label>
                                            <input type="text"
                                                class="form-control border"
                                                name="project_name"
                                                id="project_name"
                                                placeholder="Masukkan nama project Anda"
                                                style="border-radius: 10px; padding: 12px 16px;"
                                                required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label" style="font-size: 14px;">Apa yang ingin Anda capai?</label>
                                            <textarea class="form-control border"
                                                name="description"
                                                id="description"
                                                placeholder="Berikan deskripsi project Anda, tujuan, subjek, dan lainya"
                                                style="border-radius: 10px; padding: 12px 16px;"
                                                rows="3"></textarea>
                                        </div>

                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3 gap-3">
                                                <div>
                                                    <label class="form-label mb-0 fw-bold" style="font-size: 14px;">
                                                        <i class="bi bi-book me-1"></i>
                                                        Pengetahuan Proyek
                                                    </label>
                                                    <p class="text-muted mb-0" style="font-size: 12px;">
                                                        Data project dapat berisi instruksi formal untuk mengarahkan SMAGA AI atau
                                                        digunakan untuk mengupload dokumen PDF/Word sebagai basis pengetahuan tambahan
                                                    </p>
                                                </div>
                                            </div>

                                            <div id="knowledgeContainer" class="d-flex flex-column gap-3">
                                                <!-- Knowledge fields added via JavaScript -->
                                                <script>
                                                    // Add default knowledge field on load
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        addKnowledgeField();
                                                    });
                                                </script>
                                            </div>

                                            <style>
                                                .knowledge-field {
                                                    animation: slideIn 0.3s ease-out;
                                                    transition: all 0.3s ease;
                                                    background: white;
                                                    border-radius: 10px;
                                                    border: 1px solid #dee2e6;
                                                }

                                                .knowledge-field:hover {
                                                    transform: translateX(5px);
                                                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                                                }

                                                .knowledge-field .input-group {
                                                    padding: 10px;
                                                }

                                                .knowledge-field .form-select {
                                                    border-radius: 8px;
                                                    font-size: 13px;
                                                    border: 1px solid #dee2e6;
                                                }

                                                .knowledge-field .btn-danger {
                                                    border-radius: 8px;
                                                    padding: 8px 12px;
                                                    background: #dc3545;
                                                    transition: all 0.2s;
                                                }

                                                .knowledge-field .btn-danger:hover {
                                                    background: #bb2d3b;
                                                    transform: scale(1.05);
                                                }

                                                @keyframes slideIn {
                                                    from {
                                                        opacity: 0;
                                                        transform: translateX(-20px);
                                                    }

                                                    to {
                                                        opacity: 1;
                                                        transform: translateX(0);
                                                    }
                                                }

                                                @keyframes fadeOut {
                                                    to {
                                                        opacity: 0;
                                                        transform: translateX(20px);
                                                    }
                                                }

                                                .knowledge-field.removing {
                                                    animation: fadeOut 0.3s ease-in forwards;
                                                }

                                                /* Tambahkan ini ke dalam style yang sudah ada */
                                                .ai-thinking {
                                                    color: #6c757d !important;
                                                    font-size: 10px !important;
                                                    margin-bottom: 8px !important;
                                                    font-style: italic !important;
                                                    opacity: 0.8 !important;
                                                }
                                            </style>
                                        </div>

                                        <button type="submit"
                                            class="btn text-white w-100 d-flex align-items-center justify-content-center gap-2"
                                            style="border-radius: 10px; padding: 12px; background-color:rgb(218, 119, 86);">
                                            <i class="bi bi-save"></i>
                                            <span>Simpan Project</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        .form-control:focus {
                            border-color: rgb(218, 119, 86);
                            box-shadow: none;
                        }

                        .modal-content {
                            background-color: #fff;
                        }

                        .knowledge-field {
                            transition: all 0.3s ease;
                        }

                        .knowledge-field:hover {
                            transform: translateY(-2px);
                        }

                        .form-control-memori {
                            border-radius: 0;
                        }
                    </style>



                    <!-- script project -->
                    <script>
                        // load proyek saat modal di buka
                        $('#projectModal').on('show.bs.modal', function() {
                            loadProjects();
                        });

                        // Fungsi untuk menambah field pengetahuan
                        function addKnowledgeField(type = 'text') {
                            const container = document.getElementById('knowledgeContainer');
                            const id = Date.now();

                            const field = document.createElement('div');
                            field.className = 'mb-3 knowledge-field';
                            field.innerHTML = `
    <div class="input-group">
      <select class="form-select input-type" style="max-width:120px">
        <option value="text">Teks</option>
        <!-- <option value="file">Dokumen</option> -->
      </select>
      
      <div class="flex-grow-1 ms-2">
        <textarea class="form-control form-control-memori text-input" 
                  rows="2" 
                  placeholder="Masukkan teks pengetahuan"
                  style="${type === 'file' ? 'display:none' : ''}"></textarea>
                  
        <!--  <input type="file" 
               class="form-control file-input" 
               accept=".pdf,.doc,.docx,.txt"
               style="${type === 'text' ? 'display:none' : ''}"> -->
      </div>
      
      <button class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">
        <i class="bi bi-trash"></i>
      </button>
    </div>
  `;

                            // Handle perubahan tipe input
                            field.querySelector('.input-type').addEventListener('change', (e) => {
                                const isFile = e.target.value === 'file';
                                field.querySelector('.text-input').style.display = isFile ? 'none' : 'block';
                                field.querySelector('.file-input').style.display = isFile ? 'block' : 'none';
                            });

                            container.appendChild(field);
                        }

                        // Di fungsi loadProjects(), perbaiki penanganan error:
                        async function loadProjects() {
                            try {
                                const response = await fetch('get_project.php');
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }

                                const projects = await response.json();
                                const container = document.getElementById('projectList');

                                if (!container) {
                                    console.error('Project list container not found');
                                    return;
                                }

                                container.innerHTML = '';

                                if (!projects.length) {
                                    document.getElementById('emptyState')?.classList.remove('d-none');
                                    return;
                                }

                                document.getElementById('emptyState')?.classList.add('d-none');
                                projects.forEach(project => {
                                    const projectEl = document.createElement('div');
                                    projectEl.className = 'col-md-6 rounded-3';
                                    projectEl.innerHTML = `
                                        <div class="card h-100 border" style="border-radius: 12px;">
                                            <div class="card-body p-4">
                                                <h5 class="card-title mb-2 fw-bold">${project.project_name}</h5>
                                                <p class="card-text text-muted mb-3" style="font-size: 12px">
                                                    ${project.description || 'Tidak ada deskripsi'}
                                                </p>
                                                <div class="d-flex gap-2 justify-content-between">
                                                    <button class="btn btn-sm flex-fill buttonGunakan" 
                                                            onclick="selectProject(${project.id})" 
                                                            style="font-size: 12px; border-radius: 8px;">
                                                        <i class="bi bi-play-circle me-1"></i> Gunakan
                                                    </button>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light" 
                                                                data-bs-toggle="dropdown" 
                                                                style="font-size: 12px; border-radius: 8px;">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <button class="dropdown-item text-danger" 
                                                                        onclick="deleteProject(${project.id})">
                                                                    <i class="bi bi-trash me-1"></i> Hapus
                                                                </button>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;

                                    // Add iOS-style hover effect
                                    const style = document.createElement('style');
                                    style.textContent = `
                                        .hover-card {
                                            transition: all 0.2s ease;
                                            border: 1px solid #dee2e6;
                                        }
                                        .hover-card:hover {
                                            border-color: #adb5bd;
                                            transform: translateY(-2px);
                                        }
                                        .btn:active {
                                            transform: scale(0.95);
                                        }
                                        .buttonGunakan {
                                            background-color:rgb(218, 119, 86); 
                                            color: white; 
                                            font-size: 12px; 
                                            border-radius: 8px;
                                            justify-content: center;
                                            align-items: center;
                                        }
                                        .buttonGunakan:hover {
                                            background-color: #da7756;
                                            color:white;
                                        }
                                    `;
                                    document.head.appendChild(style);
                                    container.appendChild(projectEl);
                                });
                            } catch (error) {
                                console.error('Error loading projects:', error);
                                alert('Gagal memuat daftar project: ' + error.message);
                            }
                        }


                        async function selectProject(projectId) {
                            try {
                                const response = await fetch(`get_project_context.php?project_id=${projectId}`);
                                if (!response.ok) throw new Error('Network response was not ok');

                                const result = await response.json();
                                if (!result.success) throw new Error(result.error || "Gagal memuat project");

                                document.querySelectorAll('.project-badge').forEach(el => el.remove());

                                // Create badge element
                                const badge = document.createElement('div');
                                badge.className = 'btn rounded-pill button-style p-0 project-badge d-flex align-items-center gap-2 p-2 animate__animated animate__fadeIn';
                                badge.style.cssText = 'background-color: rgb(219, 213, 183); border-radius: 20px;';

                                badge.innerHTML = `
                                    <i class="bi bi-folder" style="font-size: 16px;"></i>
                                    <p class="p-0 m-0 text-dark" style="font-size: 12px; cursor: pointer;">${result.project_name}</p>
                                    <i class="bi bi-x-circle close-icon" 
                                       style="cursor: pointer; opacity: 0.6;" 
                                       onclick="removeProjectBadge()">
                                    </i>
                                `;

                                // Append badge after deep thinking toggle
                                const settingsDiv = document.querySelector('.deep-thinking-toggle').parentNode;
                                settingsDiv.appendChild(badge);

                                window.activeProject = {
                                    id: projectId,
                                    name: result.project_name,
                                    contents: result.contents
                                };

                                systemMessage.content = `${systemMessage.content.split('\n\nKonteks Project Aktif')[0]}\n\nKonteks Project Aktif (${result.project_name}):\n${result.contents.map(c => {
                                    if(c.content_type === 'text') return c.content;
                                    if(c.content_type === 'file') return `[Dokumen: ${c.file_path?.split('/').pop()}]`;
                                    return '';
                                }).join("\n")}`;

                                // Close both modals
                                const projectModal = bootstrap.Modal.getInstance(document.getElementById('projectModal'));
                                const customMemoriesModal = bootstrap.Modal.getInstance(document.getElementById('customMemoriesModal'));

                                if (projectModal) projectModal.hide();
                                if (customMemoriesModal) customMemoriesModal.hide();

                            } catch (error) {
                                console.error("Error:", error);
                                alert(error.message);
                            }
                        }

                        async function deleteProject(projectId) {
                            if (!confirm('Apakah Anda yakin ingin menghapus project ini?')) return;

                            try {
                                const response = await fetch('delete_project.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        project_id: projectId
                                    })
                                });

                                const result = await response.json();
                                if (result.success) {
                                    if (window.activeProject?.id === projectId) {
                                        removeProjectBadge();
                                    }
                                    await loadProjects();
                                } else {
                                    throw new Error(result.error || 'Gagal menghapus project');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('Gagal menghapus project: ' + error.message);
                            }
                        }


                        function removeProjectBadge() {
                            const badges = document.querySelectorAll('.project-badge');
                            badges.forEach(badge => {
                                badge.classList.add('animate__fadeOut');
                                setTimeout(() => badge.remove(), 500);
                            });
                            window.activeProject = null;
                            systemMessage.content = systemMessage.content.split('\n\nKonteks Project Aktif')[0];
                        }

                        async function editProject(projectId) {
                            try {
                                const response = await fetch(`get_project_details.php?id=${projectId}`);
                                if (!response.ok) throw new Error('Failed to fetch project details');

                                const data = await response.json();
                                if (!data.success) throw new Error(data.error);

                                // Fill form with project details
                                document.getElementById('project_name').value = data.project.project_name;
                                document.getElementById('description').value = data.project.description;

                                // Clear existing knowledge fields
                                const container = document.getElementById('knowledgeContainer');
                                container.innerHTML = '';

                                // Add existing knowledge fields
                                data.contents.forEach(content => {
                                    const field = document.createElement('div');
                                    field.className = 'knowledge-field animate__animated animate__fadeInDown';

                                    field.innerHTML = `
                <div class="input-group">
                    <select class="form-select input-type" style="max-width:120px" onchange="toggleInputType(this)">
                        <option value="text" ${content.content_type === 'text' ? 'selected' : ''}>Teks</option>
                        <option value="file" ${content.content_type === 'file' ? 'selected' : ''}>Dokumen</option>
                    </select>
                    <div class="flex-grow-1 ms-2">
                        <textarea class="form-control text-input" rows="2" 
                            placeholder="Masukkan teks pengetahuan"
                            style="display: ${content.content_type === 'text' ? 'block' : 'none'}">${content.content || ''}</textarea>
                        <input type="file" class="form-control file-input" 
                            accept=".pdf,.doc,.docx,.txt"
                            style="display: ${content.content_type === 'file' ? 'block' : 'none'}">
                        ${content.content_type === 'file' && content.file_path ? `<div class="current-file text-muted mt-1">${content.file_path}</div>` : ''}
                    </div>
                    <button class="btn btn-danger" onclick="removeKnowledgeField(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;

                                    container.appendChild(field);
                                });

                                // Add project ID to form
                                const form = document.getElementById('projectForm');
                                form.dataset.projectId = projectId;

                                // Show modal
                                const modal = new bootstrap.Modal(document.getElementById('newProjectModal'));
                                modal.show();

                            } catch (error) {
                                console.error('Error:', error);
                                alert('Failed to load project details');
                            }
                        }

                        // Add toggle function for input types
                        function toggleInputType(select) {
                            const parentField = select.closest('.knowledge-field');
                            const textInput = parentField.querySelector('.text-input');
                            const fileInput = parentField.querySelector('.file-input');

                            if (select.value === 'text') {
                                textInput.style.display = '';
                                fileInput.style.display = 'none';
                            } else {
                                textInput.style.display = 'none';
                                fileInput.style.display = '';
                            }
                        }

                        // Update project form submission
                        document.getElementById('projectForm').addEventListener('submit', async (e) => {
                            e.preventDefault();

                            const formData = new FormData();
                            const projectId = e.target.dataset.projectId;

                            if (projectId) {
                                formData.append('project_id', projectId);
                            }

                            formData.append('project_name', document.getElementById('project_name').value);
                            formData.append('description', document.getElementById('description').value);

                            // Add knowledge fields
                            const knowledgeFields = document.querySelectorAll('.knowledge-field');
                            knowledgeFields.forEach((field, index) => {
                                const type = field.querySelector('.input-type').value;
                                formData.append(`knowledge[${index}][type]`, type);

                                if (type === 'text') {
                                    formData.append(`knowledge[${index}][content]`, field.querySelector('.text-input').value);
                                } else {
                                    const fileInput = field.querySelector('.file-input');
                                    if (fileInput.files.length > 0) {
                                        formData.append(`knowledge[${index}][file]`, fileInput.files[0]);
                                    }
                                }
                            });

                            try {
                                const url = projectId ? 'update_project.php' : 'save_project.php';
                                const response = await fetch(url, {
                                    method: 'POST',
                                    body: formData
                                });

                                if (response.ok) {
                                    $('#newProjectModal').modal('hide');
                                    loadProjects();
                                    e.target.reset();
                                    delete e.target.dataset.projectId;
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('Failed to save project');
                            }
                        });

                        document.getElementById('projectForm').addEventListener('submit', async (e) => {
                            e.preventDefault();

                            const formData = new FormData();

                            // Tambahkan field utama
                            formData.append('project_name', document.getElementById('project_name').value);
                            formData.append('description', document.getElementById('description').value);

                            // Tambahkan knowledge fields
                            const knowledgeFields = document.querySelectorAll('.knowledge-field');
                            knowledgeFields.forEach((field, index) => {
                                const type = field.querySelector('.input-type').value;
                                formData.append(`knowledge[${index}][type]`, type);

                                if (type === 'text') {
                                    formData.append(`knowledge[${index}][content]`,
                                        field.querySelector('.text-input').value);
                                } else {
                                    formData.append(`knowledge[${index}][file]`,
                                        field.querySelector('.file-input').files[0]);
                                }
                            });

                            // Kirim request
                            try {
                                const response = await fetch('save_project.php', {
                                    method: 'POST',
                                    body: formData // Tidak perlu header Content-Type untuk FormData
                                });

                                if (response.ok) {
                                    $('#newProjectModal').modal('hide');
                                    loadProjects();
                                    e.target.reset();
                                }
                            } catch (error) {
                                console.error('Error:', error);
                            }
                        });
                    </script>

                    <!-- script history -->
                    <script>
                        // Fungsi untuk menampilkan riwayat
                        function loadHistory() {
                            fetch('chat_sessions.php')
                                .then(response => response.json())
                                .then(sessions => {
                                    const historyList = document.getElementById('historyList');
                                    if (!historyList) return; // Guard clause untuk mencegah error

                                    historyList.innerHTML = '';

                                    if (!Array.isArray(sessions) || sessions.length === 0) {
                                        historyList.innerHTML = `
                    <div class="text-center p-4">
                        <i class="bi bi-chat-dots text-muted" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Belum ada riwayat chat</p>
                    </div>
                `;
                                        return;
                                    }

                                    sessions.forEach(session => {
                                        if (!session) return; // Skip jika session undefined

                                        const date = new Date(session.created_at);
                                        const formattedDate = new Intl.DateTimeFormat('id-ID', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        }).format(date);

                                        const sessionDiv = document.createElement('div');
                                        sessionDiv.className = 'session-item p-3 rounded-4';
                                        sessionDiv.innerHTML = `
                    <div class="d-flex align-items-center gap-3">
                        <div class="session-icon">
                            <i class="bi bi-chat-text text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="session-title mb-1">${session.title || 'Chat ' + formattedDate}</div>
                            <div class="session-meta d-flex align-items-center gap-2">
                                <span>${formattedDate}</span>
                                <span class="chat-count">${session.message_count || 0} pesan</span>
                            </div>
                        </div>
                        <button class="btn border btn-sm text-danger delete-session" 
                                onclick="deleteSession(${session.id}, event)">
                            <i class="bi bi-trash2"></i>
                        </button>
                    </div>

                    <style>
                    .delete-session {
                    border-style : solid;
                    border-color : red !important;
                                    }
                    </style>
                `;

                                        sessionDiv.onclick = () => loadSessionChats(session.id);
                                        historyList.appendChild(sessionDiv);
                                    });
                                })
                                .catch(error => {
                                    console.error('Error loading history:', error);
                                    if (document.getElementById('historyList')) {
                                        document.getElementById('historyList').innerHTML = `
                    <div class="text-center p-4 text-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        <p class="mt-2 mb-0">Gagal memuat riwayat chat</p>
                    </div>
                `;
                                    }
                                });
                        }

                        // Fungsi untuk memuat riwayat chat
                        function loadSessionChats(sessionId) {
                            if (!sessionId) return;

                            currentSessionId = sessionId;
                            showLoader();
                            chatContainer.innerHTML = '';

                            // Sembunyikan recommendation container
                            document.querySelector('.recommendation-container').classList.add('d-none');

                            fetch(`get_session_messages.php?session_id=${sessionId}`)
                                .then(response => response.json())
                                .then(messages => {
                                    if (Array.isArray(messages) && messages.length > 0) {
                                        // Update first message dengan pesan pertama dari riwayat
                                        const firstMessage = messages[0];
                                        if (firstMessage && firstMessage.pesan) {
                                            updateFirstMessage(firstMessage.pesan);
                                        }

                                        // Tampilkan semua pesan tanpa animasi
                                        messages.forEach(message => {
                                            if (message && message.pesan) {
                                                addHistoryMessage('user', message.pesan);
                                            }
                                            if (message && message.respons) {
                                                addHistoryMessage('ai', message.respons);
                                            }
                                        });
                                    } else {
                                        addHistoryMessage('ai', 'Tidak ada pesan dalam chat ini');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error in loadSessionChats:', error);
                                    addHistoryMessage('ai', 'Maaf, gagal memuat pesan chat');
                                })
                                .finally(() => {
                                    hideLoader();
                                    $('#historyModal').modal('hide');
                                });
                        }


                        // Fungsi khusus untuk menambahkan pesan riwayat tanpa animasi
                        function addHistoryMessage(sender, text) {
                            const messageWrapper = document.createElement('div');
                            messageWrapper.classList.add(
                                'd-flex',
                                'mb-3',
                                sender === 'user' ? 'justify-content-end' : 'justify-content-start'
                            );

                            const formattedText = formatText(text);

                            messageWrapper.innerHTML = `
        <div class="d-flex chat-message align-items-center pt-1 pb-1 p-2 rounded-4 ${sender === 'user' ? 'flex-row-reverse' : ''}" 
            style="background-color: ${sender === 'user' ? 'rgb(239, 239, 239)' : 'transparent'}; 
                max-width: ${sender === 'user' ? '30rem' : '40rem'}">
            <img src="${sender === 'user' ? userImage : aiImage}" 
                class="chat-profile bg-white ms-2 me-2 rounded-circle" 
                alt="${sender} profile" 
                style="width: 20px; height: 20px; object-fit: cover;">
            <div class="chat-bubble rounded p-2 align-content-center"
                style="font-size: 13px; ${sender === 'ai' ? 'background-color: transparent; width: 100%' : ''}">
                ${formattedText}
            </div>
        </div>
    `;

                            chatContainer.appendChild(messageWrapper);
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }

                        // Tambahkan event listener untuk menampilkan kembali rekomendasi saat chat baru dimulai
                        document.getElementById('user-input').addEventListener('input', function() {
                            const recommendationContainer = document.querySelector('.recommendation-container');
                            if (recommendationContainer.classList.contains('d-none') && this.value.trim() === '') {
                                recommendationContainer.classList.remove('d-none');
                            }
                        });


                        async function deleteSession(sessionId, event) {
                            event.stopPropagation();

                            // Get chat title from the session item
                            const sessionItem = event.target.closest('.session-item');
                            const chatTitle = sessionItem.querySelector('.session-title').textContent;

                            // Hide history modal first
                            const historyModal = bootstrap.Modal.getInstance(document.getElementById('historyModal'));
                            historyModal.hide();

                            // Create and show delete confirmation modal
                            const modalHtml = `
                            <div class="ios-modal modal fade" id="deleteModal" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow">
                                        <div class="modal-body p-4 text-center">
                                            <i class="bi bi-exclamation-circle" style="font-size: 3rem; color:red;"></i>
                                            <h5 class="mb-3 fw-bold">Hapus Percakapan</h5>
                                            <p class="mb-4">Apakah Anda yakin ingin menghapus percakapan <span class="fw-bold">${chatTitle} </span> ?</p>
                                            <p class="text-muted p-0 m-0" style="font-size:12px;"> Seluruh percakapan, analisa, pengetahuan SAGA dalam percakapan ini akan dihapus dan tidak dapat dikembalikan.</p>
                                            <div class="d-flex gap-2 mt-3 justify-content-center">
                                                <button class="btn btn-lg border btn-light w-100" onclick="cancelDelete()">Batal</button>
                                                <button class="btn btn-lg btn-danger w-100" id="confirmDelete">Hapus</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;

                            // Add modal to document
                            document.body.insertAdjacentHTML('beforeend', modalHtml);

                            // Get modal element
                            const modalEl = document.getElementById('deleteModal');
                            const modal = new bootstrap.Modal(modalEl);

                            // Show modal
                            modal.show();

                            // Handle delete confirmation
                            document.getElementById('confirmDelete').onclick = async () => {
                                try {
                                    const response = await fetch('delete_chat.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            session_id: sessionId
                                        })
                                    });

                                    if ((await response.json()).success) {
                                        modal.hide();
                                        loadHistory();
                                        historyModal.show(); // Show history modal again after deletion
                                    }
                                } catch (error) {
                                    console.error('Error:', error);
                                }
                            };

                            // Add cancel delete function
                            window.cancelDelete = () => {
                                modal.hide();
                                historyModal.show(); // Show history modal again when cancelled
                            };

                            // Remove modal from DOM after it's hidden
                            modalEl.addEventListener('hidden.bs.modal', () => {
                                modalEl.remove();
                            });
                        }

                        // Add this after your existing JavaScript
                        document.addEventListener('DOMContentLoaded', () => {
                            const welcomeContainer = document.getElementById('welcomeContainer');
                            console.log('DOM Loaded, checking elements:');
                            console.log('Chat container exists:', !!document.getElementById('chat-container'));
                            console.log('First message element exists:', !!document.getElementById('firstMessage'));


                            // Hide welcome message when user starts typing
                            document.getElementById('user-input').addEventListener('input', () => {
                                if (welcomeContainer) {
                                    welcomeContainer.style.opacity = '0';
                                    welcomeContainer.style.transform = 'translate(-50%, -60%)';
                                    setTimeout(() => {
                                        welcomeContainer.style.display = 'none';
                                    }, 300);
                                }
                            });

                            // Also hide when user clicks send
                            document.getElementById('send-button').addEventListener('click', () => {
                                if (welcomeContainer) {
                                    welcomeContainer.style.opacity = '0';
                                    welcomeContainer.style.transform = 'translate(-50%, -60%)';
                                    setTimeout(() => {
                                        welcomeContainer.style.display = 'none';
                                    }, 300);
                                }
                            });

                            // Tambahkan ke fungsi sendMessage yang sudah ada
                            const originalSendMessage = sendMessage;
                            sendMessage = async function() {
                                if (welcomeContainer) {
                                    welcomeContainer.style.opacity = '0';
                                    welcomeContainer.style.transform = 'translate(-50%, -60%)';
                                    setTimeout(() => {
                                        welcomeContainer.style.display = 'none';
                                    }, 300);
                                }
                                await originalSendMessage.apply(this, arguments);
                            };


                            const historyModal = document.getElementById('historyModal');
                            if (historyModal) {
                                historyModal.addEventListener('show.bs.modal', () => {
                                    console.log('Modal opening');
                                    loadHistory();
                                });
                            } else {
                                console.error('History modal not found');
                            }

                            const historyButton = document.querySelector('[data-bs-target="#historyModal"]');
                            if (!historyButton) {
                                console.error('History button not found');
                            }
                        });

                        function loadChat(pesan, respons) {
                            chatContainer.innerHTML = '';
                            addMessage('user', pesan);
                            addMessage('ai', respons);
                            $('#historyModal').modal('hide');
                        }
                    </script>

                    <div id="drag-drop-overlay" class="drag-drop-overlay">
                        <div class="drag-drop-content text-center p-4 rounded-3">
                            <i class="bi bi-file-earmark-arrow-up display-4 mb-3" style="color:rgb(218, 119, 86) ;"></i>
                            <h5 class="fw-semibold mb-2">Seret & Lepaskan Dokumen di Sini</h5>
                            <p class="text-muted mb-0 small">Format yang didukung: DOCX, XLSX, PDF</p>
                        </div>
                    </div>

                    <style>
                        .drag-drop-overlay {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.19);
                            z-index: 9999;
                            display: none;
                            justify-content: center;
                            align-items: center;
                            border: 2px solid rgba(218, 119, 86, 0.3);
                            backdrop-filter: blur(2px);
                            transition: opacity 0.2s ease;
                        }

                        .drag-drop-content {
                            background: white;
                            border: 2px dashed rgba(218, 119, 86, 0.5);
                            padding: 2rem 3rem !important;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                            max-width: 90%;
                        }

                        .drag-drop-content .bi {
                            color: rgb(218, 119, 86);
                            transition: transform 0.2s ease;
                        }

                        .drag-drop-overlay.dragover .drag-drop-content {
                            border-color: rgb(218, 119, 86);
                            background-color: rgba(218, 119, 86, 0.03);
                        }

                        .drag-drop-overlay.dragover .bi {
                            transform: translateY(-5px);
                        }

                        /* Mobile responsive */
                        @media (max-width: 768px) {
                            .drag-drop-content {
                                padding: 1.5rem !important;
                            }

                            .drag-drop-content h5 {
                                font-size: 1.1rem;
                            }

                            .drag-drop-content .bi {
                                font-size: 2.5rem;
                            }
                        }
                    </style>

                    <!-- style untuk animasi warna file -->
                    <style>
                        .document-preview {
                            position: fixed;
                            inset: 0;
                            background: rgba(0, 0, 0, 0.5);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            z-index: 1000;
                            opacity: 0;
                            transition: opacity 0.3s ease-in-out;
                        }

                        .preview-content {
                            background: white;
                            padding: 2rem;
                            border-radius: 1rem;
                            text-align: center;
                        }

                        .document-preview {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(255, 255, 255, 0.95);
                            z-index: 10000;
                            display: none;
                            justify-content: center;
                            align-items: center;
                            backdrop-filter: blur(3px);
                            transition: opacity 0.3s ease;
                        }

                        .preview-content {
                            background: white;
                            padding: 2rem 3rem !important;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                            border: 1px solid rgba(218, 119, 86, 0.1);
                            max-width: 90%;
                            animation: contentFade 0.3s ease;
                        }

                        .preview-loader {
                            position: relative;
                            display: inline-block;
                        }

                        .preview-loader .bi {
                            color: rgb(218, 119, 86);
                            animation: bounce 1.5s ease-in-out infinite;
                        }

                        .progress-overlay {
                            position: absolute;
                            top: -8px;
                            left: -8px;
                            right: -8px;
                            bottom: -8px;
                            border: 2px solid rgba(218, 119, 86, 0.1);
                            border-radius: 50%;
                        }

                        .upload-progress {
                            width: 200px;
                            margin: 0 auto;
                        }

                        @keyframes contentFade {
                            from {
                                opacity: 0;
                                transform: translateY(10px);
                            }

                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }

                        @keyframes bounce {

                            0%,
                            100% {
                                transform: translateY(0);
                            }

                            50% {
                                transform: translateY(-8px);
                            }
                        }

                        /* Mobile responsive */
                        @media (max-width: 768px) {
                            .preview-content {
                                padding: 1.5rem !important;
                            }

                            .preview-loader .bi {
                                font-size: 2.5rem;
                            }

                            .upload-progress {
                                width: 150px;
                            }
                        }

                        .col-utama {
                            transition: background-color 0.3s ease,
                                box-shadow 0.3s ease,
                                transform 0.3s ease;
                        }

                        /* Untuk button */
                        .col-utama .btn:not(.buttonRekomendasi) {
                            transition: background-color 0.3s ease;
                        }

                        .floating-docs-container {
                            position: fixed;
                            top: 40%;
                            left: 58%;
                            transform: translate(-50%, -50%);
                            z-index: 1000;
                            background: none;
                            padding: 1rem;
                            border-radius: 1rem;
                            max-width: 80%;
                            overflow-x: auto;
                            opacity: 0;
                            /* Tambahkan ini */
                            visibility: hidden;
                            /* Tambahkan ini */
                            transition: all 0.3s ease;
                            /* Tambahkan ini */
                        }

                        .floating-docs-container.show {
                            opacity: 1;
                            visibility: visible;
                        }

                        .doc-item {
                            background: white;
                            padding: 0.5rem 1rem;
                            border: 1px solid rgb(158, 158, 158);
                            border-radius: 1rem;
                            display: flex;
                            align-items: center;
                            gap: 0.5rem
                        }

                        .doc-icon {
                            color: #3B82F6;
                            font-size: 1.2rem;
                        }

                        .close-btn {
                            background: none;
                            border: none;
                            color: #666;
                            cursor: pointer;
                            padding: 0 5px;
                            transition: color 0.2s;
                        }

                        .close-btn:hover {
                            color: #ff0000;
                        }

                        /* style untuk animasi background */
                        /* Dalam bagian style .col-utama */
                        .col-utama {
                            transition: all 0.3s ease;
                        }

                        /* Warna untuk Word */
                        .word-bg {
                            background-color: transparent !important;
                        }

                        .word-bg .btn:not(.buttonRekomendasi) {
                            background-color: #0074cc !important;
                            border-color: #0074cc !important;
                        }

                        .word-bg #input-wrapper {
                            background-color: white !important;
                            box-shadow: 0 0 0 2px #0074cc33 !important;
                        }

                        .word-bg .floating-docs-container .doc-item {
                            border-color: #0074cc;
                            background-color: #0074cc15;
                        }

                        .word-bg .button-style {
                            background-color: #0074cc !important;
                            color: white;
                        }

                        .word-bg .deep-thinking-toggle {
                            background-color: #0074cc !important;
                            color: white;
                        }

                        .word-bg .form-check-label p {
                            color: white !important;
                        }

                        /* Excel - Hijau */
                        .excel-bg {
                            background-color: transparent !important;
                        }

                        .excel-bg .btn:not(.buttonRekomendasi) {
                            background-color: #10a37f !important;
                            border-color: #10a37f !important;
                        }

                        .excel-bg .floating-docs-container .doc-item {
                            border-color: #10a37f;
                            background-color: #10a37f15;
                        }

                        .excel-bg #buttonRekomendasi {
                            background-color: white;
                        }

                        .excel-bg #buttonRekomendasi2 {
                            background-color: white;
                        }

                        .excel-bg #buttonRekomendasi3 {
                            background-color: white;
                        }

                        /* Untuk bubble user */
                        /* Untuk Excel */
                        .excel-bg {
                            background-color: transparent !important;
                        }

                        .excel-bg .btn:not(.buttonRekomendasi) {
                            background-color: #10a37f !important;
                        }

                        .excel-bg #input-wrapper {
                            background-color: white !important;
                            box-shadow: 0 0 0 2px #10a37f33 !important;
                        }

                        .excel-bg .floating-docs-container .doc-item {
                            border-color: #10a37f;
                            background-color: #10a37f15;
                        }

                        .excel-bg .button-style {
                            background-color: #10a37f !important;
                            color: white;
                        }

                        .excel-bg .deep-thinking-toggle {
                            background-color: #10a37f !important;
                            color: white;
                        }

                        .excel-bg .form-check-label p {
                            color: white !important;
                        }

                        .excel-bg .chat-container[style*="rgb(239, 239, 239)"] {
                            background-color: white !important;
                            box-shadow: none !important;
                        }

                        /* Untuk hover state */
                        .excel-bg .chat-container[style*="rgb(239, 239, 239)"]:hover {
                            background-color: #f8f9fa !important;
                        }

                        /* Untuk bubble AI (tetap transparan) */
                        .excel-bg .chat-container[style*="transparent"] {
                            background-color: transparent !important;

                        }

                        /* PDF - Merah */
                        /* PDF - Merah */
                        .pdf-bg {
                            background-color: transparent !important;
                        }

                        .pdf-bg .btn:not(.buttonRekomendasi) {
                            background-color: #800020 !important;
                            /* Changed to maroon */
                            border-color: #800020 !important;
                            /* Changed to maroon */
                        }

                        .pdf-bg .floating-docs-container .doc-item {
                            border-color: #800020;
                            /* Changed to maroon */
                            background-color: #80002015;
                            /* Changed to maroon with transparency */
                        }

                        .pdf-bg #buttonRekomendasi {
                            background-color: white;
                        }

                        .pdf-bg #buttonRekomendasi2 {
                            background-color: white;
                        }

                        .pdf-bg #buttonRekomendasi3 {
                            background-color: white;
                        }

                        .pdf-bg #input-wrapper {
                            background-color: white !important;
                            box-shadow: 0 0 0 2px #80002033 !important;
                            /* Changed to maroon with transparency */
                        }

                        .pdf-bg .button-style {
                            background-color: #800020 !important;
                            /* Changed to maroon */
                            color: white;
                        }

                        .pdf-bg .deep-thinking-toggle {
                            background-color: #800020 !important;
                            /* Changed to maroon */
                            color: white;
                        }

                        .pdf-bg .form-check-label p {
                            color: white !important;
                        }

                        .pdf-bg .chat-container[style*="rgb(239, 239, 239)"] {
                            background-color: white !important;
                            box-shadow: none !important;
                        }

                        /* Untuk hover state */
                        .pdf-bg .chat-container[style*="rgb(239, 239, 239)"]:hover {
                            background-color: #f8f9fa !important;
                        }

                        /* Untuk bubble AI (tetap transparan) */
                        .pdf-bg .chat-container[style*="transparent"] {
                            background-color: transparent !important;
                        }
                    </style>

                    <!-- script untuk drag and drop -->
                    <script>
                        const docsContainer = document.getElementById('floating-docs-container');
                        let documentContext = '';
                        let activeDocuments = new Set();
                        // Event handler untuk seluruh dokumen
                        document.addEventListener('dragover', (e) => {
                            e.preventDefault();
                            document.getElementById('drag-drop-overlay').style.display = 'flex';
                        });

                        document.addEventListener('dragleave', (e) => {
                            if (e.clientX === 0 || e.clientY === 0) {
                                document.getElementById('drag-drop-overlay').style.display = 'none';
                            }
                        });

                        document.addEventListener('drop', async (e) => {
                            e.preventDefault();
                            document.getElementById('drag-drop-overlay').style.display = 'none';

                            const file = e.dataTransfer.files[0];
                            const preview = document.getElementById('document-preview');

                            // Show loading preview
                            preview.style.display = 'flex';
                            preview.style.opacity = '1';

                            const formData = new FormData();
                            formData.append('file', file);

                            try {
                                const response = await fetch('process_document.php', {
                                    method: 'POST',
                                    body: formData
                                });
                                const result = await response.json();

                                if (result.success) {
                                    window.documentContent = result.content;
                                    addDocumentToContainer(file.name);
                                    document.getElementById('user-input').value = ``;
                                }
                            } catch (error) {
                                console.error("Error processing file:", error);
                            } finally {
                                preview.style.opacity = '0';
                                setTimeout(() => {
                                    preview.style.display = 'none';
                                }, 300);
                            }
                        });


                        function addDocumentToContainer(filename) {
                            if (!activeDocuments.has(filename)) {
                                const docElement = createDocElement(filename);
                                const docsContainer = document.getElementById('floating-docs-container');

                                // Tambahkan class show
                                docsContainer.classList.add('show');
                                docsContainer.style.display = 'flex';

                                docsContainer.appendChild(docElement);
                                activeDocuments.add(filename);
                            }
                        }

                        function createDocElement(filename) {
                            const docDiv = document.createElement('div');
                            docDiv.className = 'doc-item animate__animated animate__fadeIn';

                            const ext = filename.split('.').pop().toLowerCase();
                            const colUtama = document.querySelector('.col-utama');

                            // Reset semua kelas warna
                            colUtama.classList.remove('excel-bg', 'word-bg', 'pdf-bg');

                            switch (ext) {
                                case 'xlsx':
                                case 'xls':
                                    colUtama.classList.add('excel-bg');
                                    break;
                                case 'doc':
                                case 'docx':
                                    colUtama.classList.add('word-bg');
                                    break;
                                case 'pdf':
                                    colUtama.classList.add('pdf-bg');
                                    break;
                            }


                            docDiv.innerHTML = `
                                    ${getFileIcon(filename)}
                                    <span class="doc-name">${filename}</span>
                                    <button class="close-btn" onclick="removeDocument('${filename}')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                `;
                            return docDiv;
                        }

                        function removeDocument(filename) {
                            const docs = docsContainer.querySelectorAll('.doc-item');
                            docs.forEach(doc => {
                                if (doc.querySelector('.doc-name').textContent === filename) {
                                    doc.classList.add('animate__fadeOut');
                                    setTimeout(() => {
                                        doc.remove();
                                        activeDocuments.delete(filename);

                                        // Update warna berdasarkan file yang tersisa
                                        const colUtama = document.querySelector('.col-utama');
                                        const remainingFiles = Array.from(docsContainer.children)
                                            .filter(item => item.classList.contains('doc-item'))
                                            .map(item => item.querySelector('.doc-name').textContent);

                                        if (remainingFiles.length === 0) {
                                            // Jika tidak ada file tersisa
                                            colUtama.classList.remove('word-bg', 'excel-bg', 'pdf-bg');
                                            docsContainer.style.display = 'none';
                                        } else {
                                            // Jika masih ada file, update warna berdasarkan file terakhir
                                            const lastFile = remainingFiles[remainingFiles.length - 1];
                                            const ext = lastFile.split('.').pop().toLowerCase();

                                            colUtama.classList.remove('word-bg', 'excel-bg', 'pdf-bg');

                                            switch (ext) {
                                                case 'doc':
                                                case 'docx':
                                                    colUtama.classList.add('word-bg');
                                                    break;
                                                case 'xls':
                                                case 'xlsx':
                                                    colUtama.classList.add('excel-bg');
                                                    break;
                                                case 'pdf':
                                                    colUtama.classList.add('pdf-bg');
                                                    break;
                                            }
                                        }
                                    }, 500);
                                }
                            });
                        }

                        function getFileIcon(fileName) {
                            const ext = fileName.split('.').pop().toLowerCase();
                            const icons = {
                                'pdf': '<i class="bi bi-file-pdf text-danger" style="font-size: 1.2rem;"></i>',
                                'doc': '<i class="bi bi-file-word text-primary" style="font-size: 1.2rem;"></i>',
                                'docx': '<i class="bi bi-file-word text-primary" style="font-size: 1.2rem;"></i>',
                                'xls': '<i class="bi bi-file-excel text-success" style="font-size: 1.2rem;"></i>',
                                'xlsx': '<i class="bi bi-file-excel text-success" style="font-size: 1.2rem;"></i>',
                                'default': '<i class="bi bi-file-text" style="font-size: 1.2rem;"></i>'
                            };
                            return icons[ext] || icons.default;
                        }
                    </script>


                    <!-- script untuk membaca file excel -->
                    <script>
                        // Di dalam fungsi handleExcelFile
                        async function handleExcelFile(file) {
                            console.log("[Excel Processor] Starting Excel file processing...", file.name);

                            const reader = new FileReader();

                            return new Promise((resolve, reject) => {
                                reader.onload = function(e) {
                                    try {
                                        console.log("[Excel Processor] File read successfully, parsing...");

                                        const data = new Uint8Array(e.target.result);
                                        const workbook = XLSX.read(data, {
                                            type: 'array'
                                        });

                                        console.log("[Excel Processor] Workbook structure:", {
                                            sheetNames: workbook.SheetNames,
                                            sheetCount: workbook.SheetNames.length
                                        });

                                        let structuredContent = "";
                                        workbook.SheetNames.forEach((sheetName, index) => {
                                            const worksheet = workbook.Sheets[sheetName];
                                            const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                                                header: 1
                                            });

                                            console.log(`[Excel Processor] Processing sheet ${index + 1}/${workbook.SheetNames.length}: ${sheetName}`);
                                            console.log(`[Excel Processor] Sheet ${sheetName} data sample:`, jsonData.slice(0, 2));

                                            structuredContent += `=== LEMBAR: ${sheetName} ===\n`;
                                            structuredContent += `Jumlah Baris: ${jsonData.length}\n\n`;

                                            if (jsonData.length > 0) {
                                                // Header
                                                structuredContent += "Kolom:\n";
                                                structuredContent += jsonData[0].join(" | ") + "\n\n";

                                                // Contoh data (maksimal 5 baris)
                                                structuredContent += "Contoh Data:\n";
                                                jsonData.slice(1, 6).forEach((row, index) => {
                                                    structuredContent += `Baris ${index + 1}: ${row.join(" | ")}\n`;
                                                });
                                            }
                                            structuredContent += "\n\n";
                                        });

                                        console.log("[Excel Processor] Excel content extracted:", structuredContent);
                                        resolve(structuredContent);

                                    } catch (error) {
                                        console.error("[Excel Processor] Error processing Excel file:", error);
                                        reject(error);
                                    }
                                };

                                reader.onerror = (error) => {
                                    console.error("[Excel Processor] File read error:", error);
                                    reject(error);
                                };

                                reader.readAsArrayBuffer(file);
                            });
                        }



                        // Update your existing file input handler
                        document.getElementById('file-input').addEventListener('change', async function(e) {
                            const file = e.target.files[0];
                            console.log("[File Handler] File selected:", file?.name);

                            const preview = document.getElementById('document-preview');

                            if (file) {
                                preview.style.display = 'flex';
                                preview.style.opacity = '1';

                                try {
                                    if (file.name.match(/\.(xlsx|xls)$/)) {
                                        console.log("[File Handler] Excel file detected, starting processing...");
                                        // Load SheetJS hanya ketika file Excel terdeteksi
                                        await loadSheetJS();
                                        const content = await handleExcelFile(file);
                                        console.log("[File Handler] Excel processing completed:", {
                                            fileName: file.name,
                                            contentPreview: content.substring(0, 200) + "..."
                                        });
                                        window.documentContent = content;
                                        addDocumentToContainer(file.name);
                                    }
                                } catch (error) {
                                    console.error("[File Handler] Excel processing failed:", error);
                                } finally {
                                    preview.style.opacity = '0';
                                    setTimeout(() => {
                                        preview.style.display = 'none';
                                    }, 300);
                                }
                            }
                        });


                        // Di dalam fungsi updateConversationWithExcel():
                        function updateConversationWithExcel() {
                            if (window.documentContent) {
                                conversationHistory.push({
                                    role: "system",
                                    content: `DATA EXCEL USER:
                                            ${window.documentContent}
                                            
                                            INSTRUKSI KHUSUS:
                                            - Analisis semua sheet
                                            - Jika ditanya data spesifik, cek di semua sheet
                                            - Bandingkan data antar sheet jika diperlukan`
                                });
                            }
                        }
                    </script>

                    <!-- script untuk upload -->
                    <script>
                        // Add event listener for file input
                        document.getElementById('file-input').addEventListener('change', async function(e) {
                            const file = e.target.files[0];
                            const preview = document.getElementById('document-preview');


                            if (file) {
                                console.log("File selected:", file);


                                // Show loading preview
                                preview.style.display = 'flex';
                                preview.style.opacity = '1';

                                const formData = new FormData();
                                formData.append('file', file);

                                try {
                                    const response = await fetch('process_document.php', {
                                        method: 'POST',
                                        body: formData
                                    });
                                    const result = await response.json();

                                    if (result.success) {
                                        window.documentContent = result.content;
                                        addDocumentToContainer(file.name);
                                        userInput.value = ``;
                                    }
                                } catch (error) {
                                    console.error("Error processing file:", error);
                                } finally {
                                    preview.style.opacity = '0';
                                    setTimeout(() => {
                                        preview.style.display = 'none';
                                    }, 300);
                                }
                            }
                        });

                        // Update button styling
                        const fileInputLabel = document.querySelector('label[for="file-input"]');
                        if (fileInputLabel) {
                            fileInputLabel.style.margin = '0';
                            fileInputLabel.style.padding = '8px';
                            fileInputLabel.style.cursor = 'pointer';
                            fileInputLabel.addEventListener('mouseover', () => {
                                fileInputLabel.style.color = '#666';
                            });
                            fileInputLabel.addEventListener('mouseout', () => {
                                fileInputLabel.style.color = 'initial';
                            });
                        }
                    </script>
                </div>
                <!-- <div class="text-center peringatan pt-1">
                    <p class="text-muted p-0 m-0" style="font-size: 9px;">SMAGA AI mungkin dapat membuat kesalahan, selalu cek kembali setiap respons SMAGA AI.</p>
                </div> -->
            </div>
        </div>

        <!-- style chat-bubble ai dan user  -->
        <style>
            /* Untuk chat AI */
            .chat-bubble {
                background-color: transparent !important;
                /* Pastikan tidak ada background */
                max-width: 100%;
                /* Isi lebar parent container */
            }

            /* Untuk bubble user */
            [style*="rgb(239, 239, 239)"] .chat-bubble {
                background-color: rgb(239, 239, 239);
            }

            .chat-bubble ul {
                list-style-type: disc;
                margin: 0.5rem 0;
                padding-left: 1.5rem;
            }

            .chat-bubble ol {
                list-style-type: decimal;
                margin: 0.5rem 0;
                padding-left: 1.5rem;
            }

            .chat-bubble li {
                margin: 0.2rem 0;
                line-height: 1.4;
            }

            .chat-bubble strong {
                font-weight: 600;
                color: #333;
            }

            .chat-list {
                list-style-type: none;
                margin: 0.75rem 0;
                padding-left: 1.5rem;
                position: relative;
            }

            .chat-list li {
                margin: 0.4rem 0;
                line-height: 1.5;
                position: relative;
            }

            .chat-list li::before {
                content: "•";
                color: #da7756;
                font-weight: bold;
                display: inline-block;
                width: 1em;
                margin-left: -1em;
            }

            .chat-paragraph {
                margin: 0;
                font-size: 13px;
                line-height: 1.6;
            }
        </style>


        <script>
            // Elemen DOM
            const chatContainer = document.getElementById('chat-container');
            const userInput = document.getElementById('user-input');
            const sendButton = document.getElementById('send-button');

            let currentSessionId = null;

            // Gambar profil
            const userImage = '<?php echo !empty($guru["foto_profil"]) ? "uploads/profil/" . $guru["foto_profil"] : "assets/pp.png"; ?>';

            const aiImage = 'assets/ai_chat.png';

            let conversationHistory = [];
            const MAX_HISTORY = 10; // Batasan riwayat

            let isFirstChat = true; //rekomendasi chat

            // Loading animation
            let loadingInterval;

            // Fungsi untuk menampilkan pesan loading
            function showLoader() {
                const loadingEl = document.getElementById('loading');
                loadingEl.style.display = 'block';
                let dots = 0;
                loadingInterval = setInterval(() => {
                    loadingEl.textContent = 'Tunggu sebentar' + '.'.repeat(dots);
                    dots = (dots + 1) % 4;
                }, 500);
            }

            function hideLoader() {
                clearInterval(loadingInterval);
                document.getElementById('loading').style.display = 'none';
            }



            const formatText = (text) => {
                // Handle think tags first
                text = text.replace(/<think>([\s\S]*?)<\/think>/g,
                    '<div class="ai-thinking animate__animated animate__fadeIn">$1</div>'
                );

                // Handle bold text
                text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

                const lines = text.split('\n');
                let inList = false;
                let listType = 'ul';

                const processedLines = lines.map(line => {
                    const listMatch = line.match(/^\s*([*\-])(\s+)(.*)/);
                    if (listMatch) {
                        const [_, symbol, space, content] = listMatch;
                        const isNested = space.length > 1;
                        let listHtml = '';
                        if (!inList) {
                            listType = symbol === '*' ? 'ul' : 'ul';
                            listHtml += `<${listType} class="chat-list">`;
                            inList = true;
                        }
                        listHtml += `<li${isNested ? ' style="margin-left: 1.5rem"' : ''}>${content}</li>`;
                        return listHtml;
                    } else {
                        let lineHtml = line;
                        if (inList) {
                            lineHtml = `</${listType}>${lineHtml}`;
                            inList = false;
                        }
                        return line.trim().length > 0 ? `<p class="chat-paragraph">${lineHtml}</p>` : lineHtml;
                    }
                });

                if (inList) processedLines.push(`</${listType}>`);
                return processedLines.join('');
            };

            // Update addMessage function to include logging
            async function addMessage(sender, text, isTemporary = false) {
                console.log(`Adding ${sender} message:`, text.substring(0, 50) + '...');

                const messageWrapper = document.createElement('div');
                messageWrapper.classList.add(
                    'd-flex',
                    'mb-3',
                    sender === 'user' ? 'justify-content-end' : 'justify-content-start'
                );

                if (isTemporary) messageWrapper.id = 'thinking-message';

                let processedText = text;
                if (sender === 'ai' && !isTemporary) {
                    processedText = text.replace(/<think>([\s\S]*?)<\/think>/g,
                        '<div class="ai-thinking animate__animated animate__fadeIn">$1</div>'
                    );
                }

                const formattedText = formatText(processedText);

                messageWrapper.innerHTML = `
        <div class="d-flex chat-message align-items-center pt-1 pb-1 p-2 rounded-4 ${sender === 'user' ? 'flex-row-reverse' : ''}" 
            style="background-color: ${sender === 'user' ? 'rgb(239, 239, 239)' : 'transparent'}; 
                max-width: ${sender === 'user' ? '30rem' : '40rem'}">
            <img src="${sender === 'user' ? userImage : aiImage}" 
                class="chat-profile bg-white ms-2 me-2 rounded-circle" 
                alt="${sender} profile" 
                style="width: 20px; height: 20px; object-fit: cover;">
            <div class="chat-bubble rounded p-2 align-content-center"
                style="font-size: 13px; ${sender === 'ai' ? 'background-color: transparent; width: 100%' : ''}">
                ${sender === 'user' || isTemporary ? formattedText : ''}
            </div>
        </div>
    `;

                chatContainer.appendChild(messageWrapper);

                if (sender === 'ai' && !isTemporary) {
                    const chatBubble = messageWrapper.querySelector('.chat-bubble');
                    await typeMessage(chatBubble, text);
                }

                chatContainer.scrollTop = chatContainer.scrollHeight;
                console.log('Message added successfully');

                return messageWrapper;
            }



            // Add this after your DOM content loaded
            document.getElementById('deepThinkingToggle').addEventListener('change', function(e) {
                const isDeepThinking = e.target.checked;
                console.log('Deep thinking mode (dom loaded):', isDeepThinking ? 'ON' : 'OFF');

                // Visual feedback
                document.querySelector('.deep-thinking-toggle').classList.toggle('active', isDeepThinking);
            });

            async function typeMessage(element, text) {
                // Helper function untuk auto-scroll
                const autoScroll = () => {
                    const elementBottom = element.getBoundingClientRect().bottom;
                    const containerBottom = chatContainer.getBoundingClientRect().bottom;
                    if (elementBottom > containerBottom) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                };

                // Proses thinking tag terlebih dahulu jika ada
                const thinkMatch = text.match(/<think>([\s\S]*?)<\/think>/);
                if (thinkMatch) {
                    // Tambahkan indikator "Berpikir..."
                    const thinkIndicator = document.createElement('div');
                    thinkIndicator.className = 'ai-thinking animate__animated animate__fadeIn';
                    thinkIndicator.textContent = 'SAGA AI sedang berkontemplasi ... ';
                    element.appendChild(thinkIndicator);
                    autoScroll(); // Auto-scroll setelah menambahkan indikator

                    await new Promise(resolve => setTimeout(resolve, 300));

                    const thinkContent = thinkMatch[1];
                    const thinkDiv = document.createElement('div');
                    thinkDiv.className = 'ai-thinking animate__animated animate__fadeIn';
                    element.appendChild(thinkDiv);

                    // Typing animation untuk konten thinking
                    let currentThinkText = '';
                    for (let i = 0; i < thinkContent.length; i++) {
                        currentThinkText += thinkContent[i];
                        thinkDiv.textContent = currentThinkText;
                        autoScroll(); // Auto-scroll saat thinking
                        await new Promise(resolve => setTimeout(resolve, 5));
                    }

                    text = text.replace(/<think>[\s\S]*?<\/think>\n?/, '').trim();
                    await new Promise(resolve => setTimeout(resolve, 300));
                }

                // Mulai typing animation untuk teks utama
                let currentText = '';
                for (let i = 0; i < text.length; i++) {
                    currentText += text[i];

                    const formattedText = currentText
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/^(\d+\.|\-)\s+/gm, '<br>$1 ')
                        .split('\n')
                        .join('<br>');

                    const thinkingDivs = element.querySelectorAll('.ai-thinking');
                    element.innerHTML = Array.from(thinkingDivs).map(div => div.outerHTML).join('') + formattedText;

                    autoScroll(); // Auto-scroll saat typing teks utama
                    await new Promise(resolve => setTimeout(resolve, 5));
                }
            }


            let systemMessage = {
                role: "system",
                content: `Kamu adalah SAGA AI, asisten guru virtual yang friendly dan menjadi teman belajar untuk siswa SMP Muhammadiyah 2 Gatak dan SMA Muhammadiyah 5 Gatak.

Tugas utamamu adalah membantu siswa belajar dengan cara yang santai dan menyenangkan. Kamu memberikan informasi yang akurat, jelas, dan sesuai dengan kurikulum sekolah.

Informasi tentang sekolah:
- SMP Muhammadiyah 2 Gatak: Sekolah menengah pertama dengan alamat Jl. Trangkilan, Gatak, Sukoharjo. Memiliki visi "Berakhlaq Mulia, Kader Utama"
- SMA Muhammadiyah 5 Gatak: Sekolah menengah atas dengan alamat Jl. Pabelan, Gatak, Sukoharjo. Memiliki jargon "Progressive Cultural School"

Panduan berkomunikasi:
- Gunakan bahasa yang santai tapi tetap sopan dan edukatif
- Berikan jawaban yang singkat dan jelas untuk pertanyaan akademik
- Beri motivasi dengan kalimat positif dan realistis
- Gunakan humor ringan yang relevan dengan konteks pelajaran (tidak berlebihan)
- Setelah menjelaskan, tawarkan 1 pertanyaan lanjutan yang relevan 
- Hindari lelucon yang terlalu abstrak atau tidak berhubungan dengan topik
- Fokus pada materi pembelajaran dan kebutuhan siswa

Kamu sedang berbicara dengan: 
nama: ${<?php echo json_encode($siswa['nama']); ?>}

Jangan terlalu formal, tapi tetap jaga profesionalisme sebagai asisten pendidikan.`
            };


            let contohDialog = [{
                    role: "user",
                    content: "halo"
                },
                {
                    role: "assistant",
                    content: "Haii, halo juga (nama siswa)! 😊 Gimana kabarnya hari ini? Ada yang bisa SAGA bantu untuk belajar?"
                },
                {
                    role: "user",
                    content: "Bagaimana kabarmu hari ini?"
                },
                {
                    role: "assistant",
                    content: "Alhamdulillah, SAGA selalu siap membantu kamu belajar! Kamu sendiri gimana nih? Sudah siap untuk sesi belajar hari ini?"
                },
                {
                    role: "user",
                    content: "Aku lagi capek belajar nih"
                },
                {
                    role: "assistant",
                    content: "Hmm, aku paham rasanya capek belajar. Kadang kita butuh istirahat sejenak. Mau coba belajar dengan cara yang lebih santai? Atau kita bisa bahas topik yang lebih menarik untukmu dulu supaya semangat lagi?"
                },
                {
                    role: "user",
                    content: "Iya boleh"
                },
                {
                    role: "assistant",
                    content: "Keren! Pelajaran apa yang sedang kamu pelajari sekarang? Matematika, IPA, Bahasa Indonesia, atau mungkin pelajaran lain? Aku bisa bantu jelasin dengan cara yang lebih mudah dipahami."
                }
            ];

            let deepThinkingSystemMessage = {
                role: "system",
                content: `

                            Kamu adalah SAGA AI, asisten guru yang sangat analitis. Untuk setiap pertanyaan:

                             1. Mulai dengan proses analisis dalam Bahasa Indonesia:
                            <think>
                            Mari saya analisis situasi ini secara mendalam:
                            
                            KONTEKS:
                            - [Identifikasi masalah utama]
                            - [Siapa saja yang terlibat]
                            - [Situasi saat ini]
                            
                            TANTANGAN:
                            - [Uraikan tantangan utama]
                            - [Kendala yang ada]
                            - [Dampak yang mungkin terjadi]
                            
                            PERTIMBANGAN:
                            - [Faktor-faktor yang perlu diperhatikan]
                            - [Sumber daya yang tersedia]
                            - [Batasan yang ada]
                            
                            ARAH SOLUSI:
                            - [Pendekatan yang mungkin dilakukan]
                            - [Prioritas yang perlu diutamakan]
                            - [Target yang ingin dicapai]
                            </think>

                            2. Setelah analisis, berikan respons terstruktur dengan format:
                            ### Analisis Situasi
                            [Rangkuman hasil analisis]
                            
                            ### Tantangan Utama
                            1. [Tantangan 1]
                                - Dampak
                                - Penyebab
                            2. [Tantangan 2]
                                ...
                            
                            ### Solusi Terstruktur
                            [Uraian solusi detail]
                            
                            ### Langkah Implementasi
                            [Tahapan pelaksanaan]
                            
                            ### Antisipasi Tantangan
                            [Potensi masalah dan solusi]
                            
                            ### Monitoring Keberhasilan
                            [Cara mengukur hasil]

                            3. Akhiri dengan rangkuman dan tawaran bantuan lebih lanjut

                            Selalu:
                            - Gunakan bahasa yang empatik dan suportif
                            - Berikan contoh konkret
                            - Pertimbangkan keterbatasan sumber daya
                            - Tawarkan alternatif solusi
                            
                            PENTING: Selalu mulai dengan <think> tag dan akhiri dengan </think> sebelum memberikan respons utama.
                            
                            `
            };

            // First, define both models and their configurations
            const models = {
                llama: {
                    name: 'gemma2-9b-it',
                    temperature: 1
                },
                deepseek: {
                    name: 'qwen-qwq-32b',
                    temperature: 0.7
                }
            };

            async function getAIResponse(userMessage) {
                const API_KEY = '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';
                const API_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

                const isDeepThinking = document.getElementById('deepThinkingToggle').checked;
                const selectedModel = isDeepThinking ? models.deepseek : models.llama;
                const selectedSystemMessage = isDeepThinking ? deepThinkingSystemMessage : systemMessage;

                // Ambil konten dokumen jika ada
                const docContent = window.documentContent || '';

                // Ambil konteks project jika ada
                const projectContext = window.projectContext ? `
                                Berikut konteks project yang relevan:
                                ${window.projectContext}
                                
                                gunakan informasi ini sebagai acuan utama dalam menjawab pertanyaan guru, fokus pada informasi ini,
                                jangan bahas yang lain kecuali guru membahas hal lainya.jika pertanyaan 
                                tidak terkait dengan konteks project, jawab seperti instruksi awal ya.
                            ` : '';

                // Proses konten dokumen jika ada
                let contextMessage = [];
                if (docContent) {
                    const chunks = docContent.match(/[^.!?]+[.!?]+/g) || [];
                    const contextualized_chunks = chunks.join(' ').substring(0, 2000); // Batasi panjang teks
                    contextMessage = [{
                        role: "system",
                        content: `Document context: ${contextualized_chunks}`
                    }];
                }

                // Tambahkan pesan pengguna ke riwayat percakapan
                conversationHistory.push({
                    role: "user",
                    content: userMessage
                });

                // Batasi riwayat percakapan
                if (conversationHistory.length > MAX_HISTORY * 2) {
                    conversationHistory = conversationHistory.slice(-MAX_HISTORY * 2);
                }

                // Tambahkan log untuk melihat system message yang digunakan
                console.log('Mode:', isDeepThinking ? 'Deep Thinking' : 'Regular');
                console.log('System Messages:');
                console.log('Regular System Message:', systemMessage.content);
                console.log('Deep Thinking System Message:', deepThinkingSystemMessage.content);
                console.log('Selected System Message:', selectedSystemMessage.content);

                try {
                    // Susun pesan yang akan dikirim ke AI
                    const messages = [{
                            role: "system",
                            content: systemMessage.content + projectContext // Gabungkan system message dengan project context
                        },
                        ...contextMessage, // Konteks dokumen yang diupload
                        ...contohDialog, // Contoh dialog
                        ...conversationHistory // Riwayat percakapan
                    ];

                    // Tambahkan indikator visual di UI
                    const loadingEl = document.getElementById('loading');
                    loadingEl.textContent = `Thinking in ${isDeepThinking ? 'Deep' : 'Regular'} mode...`;

                    // Kirim request ke API
                    const response = await fetch(API_ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${API_KEY}`,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            model: selectedModel.name,
                            messages: messages, // Gunakan array messages yang sudah disusun
                            temperature: selectProject.temperature
                        })
                    });

                    // Handle response
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    const aiResponse = data.choices[0].message.content;

                    // Tambahkan respons AI ke riwayat percakapan
                    conversationHistory.push({
                        role: "assistant",
                        content: aiResponse
                    });

                    return aiResponse;

                } catch (error) {
                    console.error('Error:', error);
                    return 'Maaf, terjadi kesalahan saat berkomunikasi dengan AI. Coba lagi nanti atau refresh';
                }
            }


            // tampilkan loading
            function showLoader() {
                document.getElementById('loading').style.display = 'block';
            }

            // sembunyikan loading
            function hideLoader() {
                document.getElementById('loading').style.display = 'none';
            }

            //tampilkan gemini tersedia
            function showTersedia() {
                document.getElementById('tersedia').style.display = 'block';
            }

            // sembunyikan gemini tersedia
            function hideTersedia() {
                document.getElementById('tersedia').style.display = 'none';
            }


            async function saveToDatabase(message, aiResponse) {
                try {
                    const data = {
                        user_id: '<?php echo $_SESSION["userid"]; ?>',
                        pesan: message,
                        respons: aiResponse
                    };

                    console.log('Preparing to send data:', data);

                    const response = await fetch('save_chat.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });

                    const responseText = await response.text();
                    console.log('Raw server response:', responseText);

                    try {
                        const responseData = JSON.parse(responseText);

                        if (!responseData.success) {
                            console.error('Server returned error:', responseData.error);
                        }

                        // Hanya panggil updateCurrentTopic jika responseData.topic ada dan fungsi tersebut masih diperlukan
                        if (responseData.success && responseData.topic && typeof updateCurrentTopic === 'function') {
                            // Cek juga apakah elemen currentTopic ada di DOM
                            if (document.getElementById('currentTopic')) {
                                updateCurrentTopic(responseData.topic);
                            }
                        }
                    } catch (parseError) {
                        console.error('Failed to parse server response:', parseError);
                        console.log('Unparseable response:', responseText);
                    }
                } catch (error) {
                    console.error('Saving to database error:', error);
                }
            }



            function updateCurrentTopic(title) {
                const topicElement = document.getElementById('currentTopic');
                const topicText = topicElement.querySelector('.topic-text');
                topicText.textContent = title;
                topicElement.classList.remove('d-none');
            }

            function fillPrompt(text) {
                document.getElementById('user-input').value = text;
                document.getElementById('user-input').focus();
            }

            // Fungsi untuk memperbarui pesan pertama
            function updateFirstMessage(message) {
                if (!message) return; // Guard clause untuk mencegah error jika message undefined

                const messageElement = document.getElementById('firstMessage');
                if (!messageElement) return; // Guard clause untuk mencegah error jika elemen tidak ditemukan

                const messageText = messageElement.querySelector('.first-message-text');
                if (!messageText) return; // Guard clause untuk mencegah error jika elemen text tidak ditemukan

                // Batasi panjang pesan dan tambahkan ellipsis jika perlu
                const truncatedMessage = message.length > 50 ? message.substring(0, 47) + '...' : message;
                messageText.textContent = truncatedMessage;
                messageElement.classList.remove('d-none');
            }

            // Fungsi untuk mengirim pesan
            async function sendMessage() {
                const userMessage = userInput.value.trim();
                if (userMessage === '') return;

                // Sembunyikan recommendation container saat mulai chat
                const recommendationContainer = document.querySelector('.recommendation-container');
                if (recommendationContainer) {
                    recommendationContainer.classList.add('hide');
                    setTimeout(() => {
                        recommendationContainer.classList.add('d-none');
                    }, 300); // Tunggu animasi fade selesai
                }

                if (isFirstChat) {
                    updateFirstMessage(userMessage);
                    isFirstChat = false;
                }

                // Clear floating docs
                const docsContainer = document.getElementById('floating-docs-container');
                if (docsContainer) {
                    docsContainer.style.opacity = '0';
                    docsContainer.style.visibility = 'hidden';
                    setTimeout(() => {
                        docsContainer.style.display = 'none';
                        docsContainer.innerHTML = '';
                        activeDocuments.clear();
                        window.documentContent = '';
                    }, 300);
                }

                // Add user message
                await addMessage('user', userMessage);

                // Show thinking message with italic, fade-in, and muted text
                const tempMessage = await addMessage('ai', '<em class="text-muted animate__animated animate__fadeIn">Sedang berpikir...</em>', true);

                // Clear input and update UI states
                userInput.value = '';
                hideTersedia();
                showLoader();

                try {
                    // Update conversation with Excel data
                    updateConversationWithExcel();

                    // Get AI response
                    const aiResponse = await getAIResponse(userMessage);

                    // Remove temporary message
                    tempMessage.remove();

                    // Show AI response
                    await addMessage('ai', aiResponse);

                    // Save to database
                    await saveToDatabase(userMessage, aiResponse);
                } catch (error) {
                    console.error('Error:', error);
                    tempMessage.remove();
                    await addMessage('ai', 'Maaf, terjadi kesalahan saat memproses pesan Anda.');
                } finally {
                    hideLoader();
                    showTersedia();
                }
            }

            // Event listener untuk tombol Kirim
            sendButton.addEventListener('click', sendMessage);

            // Event listener untuk tombol Enter
            userInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Mencegah form submit (default behavior)
                    sendMessage();
                }
            });
        </script>

        <script>
            // SCRIPT BUAT KONFIRMASI KALAU SISWA KELUAR DARI HALAMAN KALAU SEDANG ADA CHAT 

            // Tempatkan kode modal ini sebelum tag penutup </body>
            document.body.insertAdjacentHTML('beforeend', `
<div class="modal fade" id="navigationConfirmModal" tabindex="-1" aria-labelledby="navigationConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-body text-center p-4">
        <i class="bi bi-exclamation-circle text-danger" style="font-size: 3rem;"></i>
        <h5 class="mt-3 fw-bold">Keluar dari Percakapan</h5>
        <p class="mb-4">Jika Anda meninggalkan halaman ini, percakapan dengan SAGA tidak akan tersimpan. Yakin ingin melanjutkan?</p>
        <div class="d-flex gap-2 btn-group">
          <button type="button" class="btn border px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
          <button id="confirmNavigationBtn" class="btn btn-danger px-4" style="border-radius: 12px;">Ya, Lanjutkan</button>
        </div>
      </div>
    </div>
  </div>
</div>
`);

            // 2. Tambahkan script untuk menangani navigasi
            document.addEventListener('DOMContentLoaded', function() {
                // Variabel untuk menyimpan URL tujuan
                let targetUrl = '';

                // Cari semua link navigasi (sidebar dan tombol)
                const navLinks = document.querySelectorAll('a:not([target="_blank"]), .nav-link');

                // Tambahkan event listener untuk setiap link
                navLinks.forEach(link => {
                    // Simpan onclick handler asli jika ada
                    const originalOnClick = link.onclick;

                    // Override onclick handler
                    link.onclick = function(e) {
                        // Cek apakah ada percakapan aktif (minimal 1 pesan di chat container)
                        const chatMessages = document.querySelectorAll('.chat-message');

                        if (chatMessages.length > 0) {
                            // Ada percakapan aktif, tampilkan konfirmasi
                            e.preventDefault();

                            // Simpan URL tujuan
                            targetUrl = link.href || '#';

                            // Tampilkan modal konfirmasi
                            const confirmModal = new bootstrap.Modal(document.getElementById('navigationConfirmModal'));
                            confirmModal.show();

                            return false;
                        } else if (originalOnClick) {
                            // Tidak ada percakapan aktif, jalankan handler asli jika ada
                            return originalOnClick.call(this, e);
                        }
                        // Jika tidak ada handler asli dan tidak ada percakapan, lanjutkan navigasi normal
                    };
                });

                // Tambahkan event handler untuk tombol konfirmasi navigasi
                document.getElementById('confirmNavigationBtn').addEventListener('click', function() {
                    // Navigasi ke URL yang disimpan
                    if (targetUrl && targetUrl !== '#') {
                        window.location.href = targetUrl;
                    } else {
                        // Tutup modal jika tidak ada URL valid
                        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('navigationConfirmModal'));
                        confirmModal.hide();
                    }
                });

                // Tambahkan handler untuk tombol-tombol khusus (jika diperlukan)
                // Contoh: tombol di sidebar atau menu hamburger
                const specialButtons = document.querySelectorAll('.sidebar-link, .menu-link');
                specialButtons.forEach(button => {
                    const originalOnClick = button.onclick;

                    button.onclick = function(e) {
                        const chatMessages = document.querySelectorAll('.chat-message');

                        if (chatMessages.length > 0) {
                            e.preventDefault();

                            // Simpan callback atau ID khusus untuk dieksekusi nanti
                            const buttonId = button.id || '';
                            const buttonAction = button.dataset.action || '';

                            // Tampilkan modal
                            const confirmModal = new bootstrap.Modal(document.getElementById('navigationConfirmModal'));
                            confirmModal.show();

                            // Update handler konfirmasi untuk tombol khusus
                            document.getElementById('confirmNavigationBtn').onclick = function() {
                                if (originalOnClick) {
                                    // Eksekusi handler asli
                                    originalOnClick.call(button, e);
                                } else if (buttonAction) {
                                    // Eksekusi tindakan dari data-action
                                    window[buttonAction]();
                                } else {
                                    // Fallback: klik tombol secara programatik
                                    button.click();
                                }
                                confirmModal.hide();
                            };

                            return false;
                        } else if (originalOnClick) {
                            return originalOnClick.call(this, e);
                        }
                    };
                });
            });
        </script>
    </div>

</body>

</html>