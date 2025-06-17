<?php
session_start();
require "koneksi.php";

// Cek apakah ada ujian_id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$ujian_id = $_GET['id'];
$user_id = $_SESSION['userid'] ?? null;

// Query untuk mengambil data ujian
$query_ujian = "SELECT u.*, k.nama_kelas as kelas 
                FROM ujian u 
                LEFT JOIN kelas k ON u.kelas_id = k.id 
                WHERE u.id = ?";
$stmt_ujian = $koneksi->prepare($query_ujian);
$stmt_ujian->bind_param("i", $ujian_id);
$stmt_ujian->execute();
$result_ujian = $stmt_ujian->get_result();
$data_ujian = $result_ujian->fetch_assoc();

if (!$data_ujian) {
    die("Ujian tidak ditemukan");
}

// Mengambil semua soal untuk ujian tersebut
$query = "SELECT * FROM bank_soal WHERE ujian_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $ujian_id);
$stmt->execute();
$result = $stmt->get_result();
$soal_array = $result->fetch_all(MYSQLI_ASSOC);

// Query untuk mengambil data deskripsi soal
$query_desc = "SELECT d.id, d.content FROM soal_descriptions d 
              WHERE d.ujian_id = ?";
$stmt_desc = $koneksi->prepare($query_desc);
$stmt_desc->bind_param("i", $ujian_id);
$stmt_desc->execute();
$result_desc = $stmt_desc->get_result();

// Simpan deskripsi dalam array
$descriptions = [];
while ($desc = $result_desc->fetch_assoc()) {
    $descriptions[$desc['id']] = $desc['content'];
}

// Tambahkan description_id ke setiap soal dalam $soal_array
foreach ($soal_array as &$soal) {
    // Jika soal memiliki description_id dan deskripsi tersebut ada
    if (!empty($soal['description_id']) && isset($descriptions[$soal['description_id']])) {
        $soal['description'] = $descriptions[$soal['description_id']];
    } else {
        $soal['description'] = null;
    }
}
unset($soal); // Hapus referensi
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Ujian - <?php echo htmlspecialchars($data_ujian['judul']); ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- MathJax for formula rendering -->
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [
                    ['\\(', '\\)']
                ],
                displayMath: [
                    ['$$', '$$']
                ],
                processEscapes: true,
                autoload: {
                    color: [],
                    colorV2: ['color']
                },
                packages: {
                    '[+]': ['noerrors']
                }
            },
            startup: {
                ready: function() {
                    MathJax.startup.defaultReady();
                    // Atur flag global bahwa MathJax siap
                    window.mathJaxReady = true;
                }
            },
            options: {
                enableMenu: false, // Nonaktifkan menu konteks
                processing: {
                    limit: 10 // Batasi operasi per langkah rendering
                }
            }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>


    <style>
        body {
            overflow-y: auto !important;
            background-color: #f8f9fa;
            font-family: merriweather;
        }

        .preview-banner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: rgba(255, 193, 7, 0.9);
            color: #000;
            text-align: center;
            padding: 8px;
            z-index: 1100;
            font-weight: bold;
        }

        .soal-numbers {
            height: calc(100vh - 70px);
            overflow-y: auto;
            padding: 10px;
        }

        .soal-number {
            width: 40px;
            height: 40px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .soal-number:hover {
            transform: scale(1.1);
        }

        .soal-content {
            height: calc(90vh - 70px);
            overflow-y: auto;
            padding: 15px;
        }

        .option-card {
            cursor: pointer;
            transition: all 0.2s;
            padding: 15px !important;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .option-card:hover {
            background-color: #e9ecef;
        }

        .option-card.selected {
            background-color: #da7756;
            color: white;
        }

        .color-web {
            background-color: rgb(218, 119, 86);
            transition: background-color 0.3s ease;
        }

        .color-web:hover {
            background-color: rgb(206, 100, 65);
        }

        .soal-numbers .soal-number[data-status="answered"] {
            background-color: #da7756 !important;
            color: white !important;
        }

        .soal-numbers .soal-number[data-status="marked"] {
            background-color: #dc3545 !important;
            color: white !important;
        }

        .soal-number {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .soal-numbers {
                height: auto;
                max-height: 200px;
                margin-bottom: 1rem;
            }

            .soal-content {
                height: auto;
                margin-bottom: 100px;
                padding: 10px;
            }

            .bottom-navigation {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 15px;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
                margin-left: 0 !important;
                width: 100%;
                z-index: 1000;
                border-radius: 15px;
            }

            .bottom-navigation button {
                padding: 12px;
                min-width: 44px;
                font-size: 20px;
            }

            .bottom-navigation button p {
                display: none;
            }

            .option-card {
                padding: 20px !important;
            }

            .navbar {
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .col-md-3 {
                padding: 0;
            }
        }

        /* Tambahkan styling baru untuk mobile */
        @media (max-width: 768px) {
            #mobileInfoCollapse {
                width: 100%;
                padding: 0;
            }

            .soal-content {
                height: auto;
                padding: 10px 15px;
                margin-bottom: 100px;
            }

            .bottom-navigation {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                z-index: 1000;
                background-color: white;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            }

            .soal-number {
                width: 35px;
                height: 35px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body id="examBody" class="pt-2 mt-3">
    <!-- Preview Banner -->
    <div class="preview-banner">
        <i class=""></i> MODE PREVIEW - Tampilan ujian yang akan dilihat siswa - data ujian diambil pada <?php echo date('d/m/Y H:i:s'); ?>
    </div>

    <nav class="navbar d-md-none" style="background-color: rgba(255, 255, 255, 0.92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0, 0, 0, 0.1); padding: 12px 0;">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex d-md-none align-items-center justify-content-center mx-auto">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock" style="color:rgb(218, 119, 86);"></i>
                        <span id="mobile-countdown" style="font-weight: 600; color: rgb(218, 119, 86); font-size: 15px;">00:00:00</span><span style="font-weight: 600; color: rgb(218, 119, 86); font-size: 15px;"> tersisa</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 collapse d-none d-md-block">
                <div class="soal-numbers">
                    <div class="card mb-3 border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div>
                                    <img src="assets/pp.png" class="rounded-circle border" style="width: 60px; height: 60px; object-fit: cover;">
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold" style="color: #1c1c1e;">Preview Ujian</h6>
                                    <div class="d-flex flex-column">
                                        <span class="badge text-truncate mb-1" style="background: rgba(218, 119, 86, 0.1); color: rgb(218, 119, 86); font-weight: normal; padding: 5px 10px; border-radius: 12px; max-width: 150px;" title="<?php echo $data_ujian['judul']; ?>">
                                            <?php echo $data_ujian['judul']; ?>
                                        </span>
                                        <small class="text-muted d-block" style="font-size: 11px;">
                                            <i class="bi bi-layers-half me-1"></i><?php echo !empty($data_ujian['tingkat']) ? $data_ujian['tingkat'] : 'Semua Tingkat'; ?>
                                        </small>
                                        <small class="text-muted d-block" style="font-size: 11px;">
                                            <i class="bi bi-people me-1"></i><?php echo !empty($data_ujian['kelas']) ? $data_ujian['kelas'] : 'Semua Kelas'; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-4 d-none d-md-flex" style="background: #f2f2f7;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-clock" style="color:rgb(218, 119, 86);"></i>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-secondary">Status:</small>
                                        <span id="countdown" style="font-weight: 600; color: rgb(218, 119, 86); font-size: 15px;">Belum Dimulai</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border" style="border-radius: 16px; background: rgba(255,255,255,0.95);">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-4" style="color: #1c1c1e; font-weight: 600;">Daftar Soal</h6>
                            <div class="d-flex flex-wrap gap-2 justify-content-start">
                                <?php foreach ($soal_array as $index => $soal): ?>
                                    <div class="soal-number rounded-3 border-0 d-flex align-items-center justify-content-center"
                                        data-soal="<?php echo $index; ?>"
                                        data-status="unanswered"
                                        style="background: #f2f2f7;color:black; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                        <?php echo $index + 1; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-4 p-3 rounded-4" style="background: #f2f2f7;">
                                <div class="d-flex gap-3 align-items-center mb-2">
                                    <div class="soal-number rounded-3 border"
                                        style="width:24px; height:24px; background:#f2f2f7;"></div>
                                    <small style="color: #3c3c43;">Belum dijawab</small>
                                </div>
                                <div class="d-flex gap-3 align-items-center mb-2">
                                    <div class="soal-number rounded-3"
                                        style="width:24px; height:24px; background:#da7756;"></div>
                                    <small style="color: #3c3c43;">Sudah dijawab</small>
                                </div>
                                <div class="d-flex gap-3 align-items-center">
                                    <div class="soal-number rounded-3"
                                        style="width:24px; height:24px; background:#dc3545;"></div>
                                    <small style="color: #3c3c43;">Ditandai</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="soal-content">
                    <form id="exam-form">
                        <?php foreach ($soal_array as $index => $soal):
                            $has_description = !empty($soal['description']);
                        ?>
                            <div class="soal-page <?php echo $index === 0 ? '' : 'd-none'; ?>"
                                data-index="<?php echo $index; ?>">

                                <?php if ($has_description): ?>
                                    <div class="card mb-4 border" style="border-radius: 12px;">
                                        <div class="card-body p-4 bg-light" style="border-radius: 12px;">
                                            <h6 class="card-title fw-bold mb-3" style="color: #1c1c1e;">
                                                <i class="bi bi-book me-2" style="color: #da7756;"></i>Cerita/Deskripsi Soal
                                            </h6>
                                            <div class="p-3 bg-white rounded-3 border">
                                                <?php echo nl2br(htmlspecialchars($soal['description'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <h5 class="mb-4">Soal <?php echo $index + 1; ?></h5>

                                <?php if (!empty($soal['gambar_soal'])): ?>
                                    <div class="mb-3">
                                        <img src="<?php echo htmlspecialchars($soal['gambar_soal']); ?>"
                                            alt="Gambar soal <?php echo $index + 1; ?>"
                                            class="img-fluid rounded shadow-sm text-start"
                                            style="max-height: 300px; width: auto; display: block;">
                                    </div>
                                <?php endif; ?>
                                <p class="mb-4 fw-bold" style="font-size:2rem;"><?php echo $soal['pertanyaan']; ?></p>

                                <?php
                                // Dalam loop opsi jawaban
                                $options = [
                                    'a' => $soal['jawaban_a'],
                                    'b' => $soal['jawaban_b'],
                                    'c' => $soal['jawaban_c'],
                                    'd' => $soal['jawaban_d']
                                ];
                                foreach ($options as $key => $value):
                                ?>
                                    <div class="option-card p-3 rounded border mb-3" data-value="<?php echo $key; ?>">
                                        <?php echo strtoupper($key) . ". " . $value; ?>

                                        <?php if (!empty($soal['gambar_jawaban_' . $key])): ?>
                                            <div class="mt-2">
                                                <img src="<?php echo htmlspecialchars($soal['gambar_jawaban_' . $key]); ?>"
                                                    alt="Gambar jawaban <?php echo strtoupper($key); ?>"
                                                    class="img-fluid rounded shadow-sm"
                                                    style="max-height: 150px; width: auto; display: block;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>
                <div class="bottom-navigation d-flex justify-content-between bg-white p-3 border" style="border-radius: 15px;">
                    <button class="btn border-0 border-md-1" id="prev" style="border: 1px solid rgba(0,0,0,0.1) !important;">
                        <i class="bi bi-chevron-left" style="font-size: 24px; color: #007AFF;"></i>
                    </button>
                    <div class="d-flex gap-3">
                        <button class="btn border-0 border-md-1 d-flex flex-column align-items-center" id="mark">
                            <i class="bi bi-bookmark" style="font-size: 24px; color: #FF3B30;"></i>
                            <span class="d-none d-md-block text-muted" id="mark-ket" style="font-size: 12px;">Tandai</span>
                        </button>
                        <button class="btn border-0 border-md-1 d-flex flex-column align-items-center" id="clear">
                            <i class="bi bi-x-circle" style="font-size: 24px; color: #8E8E93;"></i>
                            <span class="d-none d-md-block text-muted" style="font-size: 12px;">Hapus</span>
                        </button>
                        <button class="btn d-md-none border-0 border-md-1 d-flex flex-column align-items-center" data-bs-toggle="collapse" data-bs-target="#mobileInfoCollapse">
                            <i class="bi bi-info-circle" style="font-size: 24px; color: #007AFF;"></i>
                            <span class="d-none d-md-block text-muted" style="font-size: 12px;">Info</span>
                        </button>
                        <button class="btn border-0 border-md-1 d-flex flex-column align-items-center" id="finish">
                            <i class="bi bi-flag" style="font-size: 24px; color: #34C759;"></i>
                            <span class="d-none d-md-block text-muted" style="font-size: 12px;">Selesai</span>
                        </button>
                    </div>
                    <button class="btn border-0 border-md-1" id="next" style="border: 1px solid rgba(0,0,0,0.1) !important;">
                        <i class="bi bi-chevron-right" style="font-size: 24px; color: #007AFF;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSoal = 0;
        const totalSoal = <?php echo count($soal_array); ?>;
        let answers = {};
        const markedQuestions = new Set();

        // Fungsi untuk menampilkan soal
        function showSoal(index) {
            $('.soal-page').addClass('d-none');
            $(`.soal-page[data-index="${index}"]`).removeClass('d-none');
            currentSoal = index;
            updateMarkButtonUI(index);
        }

        // Fungsi untuk memperbarui tampilan tombol mark
        function updateMarkButtonUI(index) {
            const markButton = $('#mark');
            const markKet = $('#mark-ket');
            const markIcon = markButton.find('i');

            if (markedQuestions.has(index)) {
                markButton.addClass('bg-danger');
                markKet.removeClass('text-muted').addClass('text-white');
                markIcon.removeClass('bi-bookmark').addClass('bi-bookmark-fill text-white');
                markKet.text('Ditandai');
            } else {
                markButton.removeClass('bg-danger');
                markKet.removeClass('text-white').addClass('text-muted');
                markIcon.removeClass('bi-bookmark-fill text-white').addClass('bi-bookmark');
                markKet.text('Tandai');
            }
        }

        // Event listener untuk tombol navigasi
        $('#prev').click(() => {
            if (currentSoal > 0) {
                showSoal(currentSoal - 1);
            }
        });

        $('#next').click(() => {
            if (currentSoal < totalSoal - 1) {
                showSoal(currentSoal + 1);
            }
        });

        // Event listener untuk tombol mark
        $('#mark').click(() => {
            if (markedQuestions.has(currentSoal)) {
                markedQuestions.delete(currentSoal);
                $(`.soal-number[data-soal="${currentSoal}"]`).attr('data-status', answers[currentSoal] ? 'answered' : 'unanswered');
            } else {
                markedQuestions.add(currentSoal);
                $(`.soal-number[data-soal="${currentSoal}"]`).attr('data-status', 'marked');
            }
            updateMarkButtonUI(currentSoal);
        });

        // Event listener untuk tombol clear
        $('#clear').click(() => {
            $(`.soal-page[data-index="${currentSoal}"] .option-card`).removeClass('selected');
            if (answers[currentSoal]) {
                delete answers[currentSoal];
                $(`.soal-number[data-soal="${currentSoal}"]`).attr('data-status', markedQuestions.has(currentSoal) ? 'marked' : 'unanswered');
            }
        });

        // Event listener untuk klik pada opsi jawaban
        $('.option-card').click(function() {
            $(this).closest('.soal-page').find('.option-card').removeClass('selected');
            $(this).addClass('selected');

            const soalIndex = $(this).closest('.soal-page').data('index');
            answers[soalIndex] = $(this).data('value');

            $(`.soal-number[data-soal="${soalIndex}"]`).attr('data-status', markedQuestions.has(soalIndex) ? 'marked' : 'answered');
        });

        // Event listener untuk klik pada nomor soal
        $('.soal-number').click(function() {
            const index = $(this).data('soal');
            showSoal(index);
        });

        // Simulasi alert untuk tombol selesai
        $('#finish').click(() => {
            alert('Ini hanya mode preview. Pada ujian sebenarnya, tombol ini akan menyelesaikan ujian.');
        });
    </script>
</body>

</html>