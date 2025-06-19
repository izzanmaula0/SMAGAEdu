<?php
session_start();
require "koneksi.php";

$ujian_id = $_GET['ujian_id'];
$siswa_id = $_GET['siswa_id'];

// Get student info
$query_siswa = "SELECT nama FROM siswa WHERE id = '$siswa_id'";
$siswa = mysqli_fetch_assoc(mysqli_query($koneksi, $query_siswa));

// Get exam details
$query_ujian = "SELECT judul, mata_pelajaran FROM ujian WHERE id = '$ujian_id'";
$ujian = mysqli_fetch_assoc(mysqli_query($koneksi, $query_ujian));

// Get questions and answers
$query_jawaban = "
    SELECT 
        bs.id as soal_id,
        bs.pertanyaan,
        bs.jawaban_a,
        bs.jawaban_b,
        bs.jawaban_c,
        bs.jawaban_d,
        UPPER(bs.jawaban_benar) as jawaban_benar,
        UPPER(ju.jawaban) as jawaban_siswa
    FROM bank_soal bs
    LEFT JOIN jawaban_ujian ju ON bs.id = ju.soal_id AND ju.siswa_id = '$siswa_id'
    WHERE bs.ujian_id = '$ujian_id'
    ORDER BY bs.id ASC
";
$result_jawaban = mysqli_query($koneksi, $query_jawaban);

// Ambil data guru
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

?>
<!DOCTYPE html>
<html>

<head>
    <title>Detail Jawaban Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>


    <style>
        .answer-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
        }

        .correct-answer {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .wrong-answer {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .option {
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
        }

        .selected-correct {
            background-color: #d4edda;
        }

        .selected-wrong {
            background-color: #f8d7da;
        }

        .correct-option {
            font-weight: bold;
            color: #28a745;
        }

        body {
            font-family: 'Merriweather', serif;
        }
    </style>
    <style>
        /* Untuk card analisis fixed di sebelah kanan */
        @media (min-width: 992px) {
            .sticky-top {
                position: sticky;
                top: 20px;
                max-height: calc(100vh - 40px);
                overflow-y: auto;
            }
        }

        /* Untuk animasi typing */
        #hasilAnalisis {
            font-family: 'Merriweather', serif;
            line-height: 1.6;
        }

        #hasilAnalisis h1 {
            color: rgb(218, 119, 86);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        #hasilAnalisis h2 {
            color: #333;
            font-size: 1.3rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        #hasilAnalisis h3 {
            color: #555;
            font-size: 1.1rem;
            margin-top: 1.2rem;
            margin-bottom: 0.8rem;
        }

        #hasilAnalisis ul,
        #hasilAnalisis ol {
            margin-bottom: 1rem;
        }

        #hasilAnalisis strong {
            color: rgb(218, 119, 86);
        }
    </style>
</head>

<body class="bg-light">


    <?php include 'includes/styles.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar for desktop -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Mobile navigation -->
            <?php include 'includes/mobile_nav.php'; ?>

            <!-- Settings Modal -->
            <?php include 'includes/settings_modal.php'; ?>


        </div>
    </div>


    <!-- ini isi kontennya -->
    <div class="container-fluid">
        <div class="row">
            <div class="col p-4 col-utama bg-white">
                <style>
                    .col-utama {
                        margin-left: 13rem;
                        animation: fadeInUp 0.5s;
                        opacity: 1;
                    }

                    .col-kedua {
                        animation: fadeInUp 0.5s;
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
                        .col-utama {
                            margin-left: 0;
                            margin-top: 10px;
                            /* Untuk memberikan space dari fixed navbar mobile */
                        }
                    }
                </style>

                <div class="container">
                    <div class="card border rounded-4">
                        <div class="card-header p-4 pb-0 bg-white rounded-top-4">
                            <div class="d-flex justify-content-between align-items-center pb-3">
                                <div>
                                    <h3 class="mb-0"> <?php echo ucwords(strtolower(htmlspecialchars($siswa['nama']))); ?></h3>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($ujian['judul']); ?> |<?php echo htmlspecialchars($ujian['mata_pelajaran']); ?>
                                    </p>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="detail_hasil_ujian.php?ujian_id=<?php echo $ujian_id; ?>"
                                        class="btn btn-light btn-sm border rounded-pill">
                                        <i class="bi bi-arrow-left me-1"></i>Kembali
                                    </a>
                                    <button onclick="window.print()" class="btn btn-sm rounded-pill"
                                        style="background-color: rgb(218, 119, 86); color: white;">
                                        <i class="bi bi-printer me-1"></i>Cetak
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            $no = 1;
                            while ($jawaban = mysqli_fetch_assoc($result_jawaban)) {
                                $is_correct = $jawaban['jawaban_siswa'] === $jawaban['jawaban_benar'];
                                $status_class = $jawaban['jawaban_siswa'] ? ($is_correct ? 'correct-answer' : 'wrong-answer') : '';
                            ?>
                                <div class="answer-box rounded-4 <?php echo $status_class; ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0" style="color: rgb(218, 119, 86);">Soal <?php echo $no; ?></h5>
                                        <?php if ($jawaban['jawaban_siswa']): ?>
                                            <span class="badge rounded-pill <?php echo $is_correct ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $is_correct ? 'Benar' : 'Salah'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary">Tidak Dijawab</span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="mb-3"><?php echo htmlspecialchars($jawaban['pertanyaan']); ?></p>

                                    <?php
                                    $options = ['A', 'B', 'C', 'D'];
                                    foreach ($options as $option) {
                                        $option_key = 'jawaban_' . strtolower($option);
                                        $is_selected = $jawaban['jawaban_siswa'] === $option;
                                        $is_correct_option = $jawaban['jawaban_benar'] === $option;

                                        $option_class = '';
                                        if ($is_selected && $is_correct) $option_class = 'selected-correct';
                                        else if ($is_selected) $option_class = 'selected-wrong';
                                        else if ($is_correct_option) $option_class = 'correct-option';
                                    ?>
                                        <div class="option rounded-3 <?php echo $option_class; ?>">
                                            <?php echo $option . '. ' . htmlspecialchars($jawaban[$option_key]); ?>
                                            <?php if ($is_selected): ?>
                                                <span class="badge rounded-pill text-black ms-2" style="font-size: 0.75rem; background-color: rgba(0,0,0,0.1);">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Dipilih Siswa
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php
                                $no++;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom baru untuk analisis AI (col-lg-4) -->
            <div class="col-lg-4 col-kedua bg-white">
                <div class="card border rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-header d-flex p-4 pb-3 bg-white rounded-top-4">
                        <span class="bi bi-stars me-2" style="font-size: 30px; color:rgb(218, 119, 86)"></span>
                        <div>
                            <h5 class="mb-0">Analisis Hasil Ujian Siswa</h5>
                            <p class="text-muted mb-0" style="font-size: 12px;">Integrasi Layanan SAGA AI</p>
                        </div>
                    </div>
                    <div class="p-4 flex-grow-1 overflow-auto" id="analisisContent">
                        <div id="konfirmasiAnalisis" class="text-center py-5 px-2">
                            <div class="mb-4">
                                <img src="assets/analisis_pribadi.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;">
                            </div>
                            <h3 class="mb-3 fw-bold">Analisis Ujian Siswa Bersama SAGA</h3>
                            <p class="text-muted mb-4">Dapatkan kondisi, evaluasi, dan rekomendasi hasil ujian siswa Anda dengan bantuan analisis SAGA AI</p>
                            <button id="startAnalisis" class="btn btn-sm px-4 py-2 rounded-4" style="background-color: rgb(218, 119, 86); color: white;">
                                Analisis Sekarang
                            </button>
                        </div>
                        <div id="loadingAnalisis" class="text-center py-5 d-none">
                            <img src="assets/ai_loading.gif" width="80px" alt="">
                            <p class="mt-3 text-muted" style="font-size:12px;">Sedang menganalisis data ujian, sebentar lagi</p>
                        </div>
                        <div id="hasilAnalisis" class="d-none">
                            <!-- Hasil analisis akan muncul di sini dengan efek typing -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .answer-box {
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .correct-answer {
            background-color: rgba(52, 199, 89, 0.1);
            border-color: rgba(52, 199, 89, 0.2);
        }

        .wrong-answer {
            background-color: rgba(255, 59, 48, 0.1);
            border-color: rgba(255, 59, 48, 0.2);
        }

        .option {
            padding: 12px;
            margin: 8px 0;
            background: #f8f9fa;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .option:hover {
            transform: translateX(5px);
        }

        .selected-correct {
            background-color: rgba(52, 199, 89, 0.1);
            border-color: rgba(52, 199, 89, 0.2);
        }

        .selected-wrong {
            background-color: rgba(255, 59, 48, 0.1);
            border-color: rgba(255, 59, 48, 0.2);
        }

        .correct-option {
            color: rgb(52, 199, 89);
        }
    </style>
    </div>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <!-- Tambahkan di bagian bawah file, sebelum closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startAnalisisBtn = document.getElementById('startAnalisis');
            const konfirmasiAnalisis = document.getElementById('konfirmasiAnalisis');
            const loadingAnalisis = document.getElementById('loadingAnalisis');
            const hasilAnalisis = document.getElementById('hasilAnalisis');

            // API Key Groq - ganti dengan API key Anda
            const GROQ_API_KEY = 'YOUR_API_KEY'; // Ganti dengan API key Groq Anda

            // Ambil data jawaban dari PHP untuk dikirim ke API
            let jawabanData = [];
            <?php
            mysqli_data_seek($result_jawaban, 0); // Reset pointer hasil query
            while ($jawaban = mysqli_fetch_assoc($result_jawaban)) {
                echo "jawabanData.push({
                    soal_id: " . json_encode($jawaban['soal_id']) . ",
                    pertanyaan: " . json_encode($jawaban['pertanyaan']) . ",
                    jawaban_a: " . json_encode($jawaban['jawaban_a']) . ",
                    jawaban_b: " . json_encode($jawaban['jawaban_b']) . ",
                    jawaban_c: " . json_encode($jawaban['jawaban_c']) . ",
                    jawaban_d: " . json_encode($jawaban['jawaban_d']) . ",
                    jawaban_benar: " . json_encode($jawaban['jawaban_benar']) . ",
                    jawaban_siswa: " . json_encode($jawaban['jawaban_siswa']) . "
                });\n";
            }
            ?>

            // Informasi siswa dan ujian
            const siswaInfo = {
                nama: '<?php echo addslashes($siswa['nama']); ?>',
                id: '<?php echo $siswa_id; ?>'
            };

            const ujianInfo = {
                judul: '<?php echo addslashes($ujian['judul']); ?>',
                mata_pelajaran: '<?php echo addslashes($ujian['mata_pelajaran']); ?>',
                id: '<?php echo $ujian_id; ?>'
            };

            // Saat tombol "Analisis Sekarang" diklik
            startAnalisisBtn.addEventListener('click', function() {
                konfirmasiAnalisis.classList.add('d-none');
                loadingAnalisis.classList.remove('d-none');

                // Panggil API Groq untuk analisis
                getAIAnalysis(jawabanData, siswaInfo, ujianInfo)
                    .then(analysisResult => {
                        loadingAnalisis.classList.add('d-none');
                        hasilAnalisis.classList.remove('d-none');

                        // Animasi typing untuk hasil analisis
                        typeText(hasilAnalisis, analysisResult);
                    })
                    .catch(error => {
                        console.error('Error saat menganalisis data:', error);
                        loadingAnalisis.classList.add('d-none');
                        hasilAnalisis.classList.remove('d-none');
                        hasilAnalisis.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat melakukan analisis. Silakan coba lagi.</div>';
                    });
            });

            // Fungsi untuk memanggil API Groq dan mendapatkan analisis
            async function getAIAnalysis(jawabanData, siswaInfo, ujianInfo) {
                // Hitung statistik dasar
                let benar = 0;
                let salah = 0;
                let tidakDijawab = 0;

                jawabanData.forEach(item => {
                    if (!item.jawaban_siswa) {
                        tidakDijawab++;
                    } else if (item.jawaban_benar === item.jawaban_siswa) {
                        benar++;
                    } else {
                        salah++;
                    }
                });

                const totalSoal = jawabanData.length;
                const persentaseBenar = Math.round((benar / totalSoal) * 100);

                // Buat prompt untuk model AI
                const prompt = `
Kamu adalah asisten AI pendidikan yang bernama SAGA AI. Analisis hasil ujian berikut dan berikan evaluasi terperinci dengan format markdown.

Informasi Ujian:
- Nama Siswa: ${siswaInfo.nama}
- Mata Pelajaran: ${ujianInfo.mata_pelajaran}
- Judul Ujian: ${ujianInfo.judul}
- Total Soal: ${totalSoal}
- Jawaban Benar: ${benar} (${persentaseBenar}%)
- Jawaban Salah: ${salah}
- Tidak Dijawab: ${tidakDijawab}

Detail Jawaban Siswa:
${jawabanData.map((item, index) => `
Soal ${index+1}: ${item.pertanyaan}
A. ${item.jawaban_a}
B. ${item.jawaban_b}
C. ${item.jawaban_c}
D. ${item.jawaban_d}
Jawaban Benar: ${item.jawaban_benar}
Jawaban Siswa: ${item.jawaban_siswa || 'Tidak dijawab'}
Status: ${!item.jawaban_siswa ? 'Tidak Dijawab' : (item.jawaban_benar === item.jawaban_siswa ? 'Benar' : 'Salah')}
`).join('\n')}

Berdasarkan data di atas, berikan analisis dengan format markdown yang mencakup:
1. Analisis materi yang sudah dipahami siswa (berdasarkan jawaban benar)
2. Analisis materi yang belum dipahami siswa (berdasarkan jawaban salah dan tidak dijawab)
3. Rekomendasi untuk meningkatkan pemahaman siswa

Format output yang diinginkan:
# Hasil Analisis

Rangkuman singkat hasil ujian siswa...

## Analisis Pemahaman Materi

### Materi yang Dipahami
- Poin 1
- Poin 2

### Materi yang Belum Dipahami
- Poin 1
- Poin 2

## Rekomendasi
1. Rekomendasi 1
2. Rekomendasi 2

Gunakan bahasa yang mudah di pahami, konkret, dan spesifik. Berdasarkan jawaban benar dan salah, identifikasi pola pemahaman siswa.
`;

                try {
                    // Panggil API Groq
                    const response = await fetch('https://api.groq.com/openai/v1/chat/completions', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${GROQ_API_KEY}`,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            model: "gemma2-9b-it",
                            messages: [{
                                    role: "system",
                                    content: "Kamu adalah SAGA AI, asisten pendidikan profesional yang membantu menganalisis hasil ujian siswa. Gunakan format markdown dalam responsmu."
                                },
                                {
                                    role: "user",
                                    content: prompt
                                }
                            ],
                            temperature: 0.7,
                            max_tokens: 1500
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }

                    const data = await response.json();
                    return data.choices[0].message.content;
                } catch (error) {
                    console.error("Error calling Groq API:", error);
                    // Fallback ke analisis sederhana jika API gagal
                    return generateFallbackAnalysis(jawabanData, siswaInfo, ujianInfo, benar, salah, tidakDijawab, persentaseBenar);
                }
            }

            // Fungsi fallback jika API gagal
            function generateFallbackAnalysis(jawabanData, siswaInfo, ujianInfo, benar, salah, tidakDijawab, persentaseBenar) {
                const totalSoal = jawabanData.length;

                let hasil = `# Hasil Analisis\n\n`;
                hasil += `Siswa **${siswaInfo.nama}** telah menyelesaikan ujian **${ujianInfo.judul}** dengan hasil:\n\n`;
                hasil += `- **Soal yang dijawab benar:** ${benar} (${persentaseBenar}%)\n`;
                hasil += `- **Soal yang dijawab salah:** ${salah}\n`;
                hasil += `- **Soal yang tidak dijawab:** ${tidakDijawab}\n\n`;

                // Analisis pemahaman materi
                hasil += `## Analisis Pemahaman Materi\n\n`;

                // Bagian 1: Materi yang dipahami
                hasil += `### Materi yang Dipahami\n\n`;
                if (persentaseBenar >= 70) {
                    hasil += `Siswa menunjukkan pemahaman yang baik pada bagian-bagian berikut:\n\n`;
                    hasil += `- Penerapan konsep dasar mata pelajaran ${ujianInfo.mata_pelajaran}\n`;
                    hasil += `- Kemampuan menjawab pertanyaan pemahaman dengan tepat\n\n`;
                } else if (persentaseBenar >= 50) {
                    hasil += `Siswa menunjukkan pemahaman cukup pada beberapa konsep, namun masih perlu penguatan:\n\n`;
                    hasil += `- Konsep dasar ${ujianInfo.mata_pelajaran}\n`;
                    hasil += `- Kemampuan mengingat materi yang telah diajarkan\n\n`;
                } else {
                    hasil += `Siswa masih menunjukkan pemahaman dasar yang minimal pada materi ujian:\n\n`;
                    hasil += `- Beberapa konsep dasar dapat dipahami\n`;
                    hasil += `- Kemampuan mengenali pilihan jawaban yang benar\n\n`;
                }

                // Bagian 2: Materi yang belum dipahami
                hasil += `### Materi yang Belum Dipahami\n\n`;
                if (persentaseBenar < 70) {
                    hasil += `Siswa perlu meningkatkan pemahaman pada bagian-bagian berikut:\n\n`;
                    hasil += `- Penerapan konsep lanjutan dalam mata pelajaran ${ujianInfo.mata_pelajaran}\n`;
                    hasil += `- Kemampuan menganalisis dan memecahkan masalah\n`;
                    hasil += `- Pemahaman mendalam tentang hubungan antar konsep\n\n`;
                } else {
                    hasil += `Meskipun secara umum pemahaman baik, namun masih ada beberapa area yang perlu ditingkatkan:\n\n`;
                    hasil += `- Beberapa konsep khusus yang memerlukan pendalaman\n`;
                    hasil += `- Konsistensi dalam penerapan konsep di berbagai konteks\n\n`;
                }

                // Bagian 3: Rekomendasi
                hasil += `## Rekomendasi\n\n`;
                if (persentaseBenar >= 70) {
                    hasil += `Berdasarkan hasil analisis, berikut rekomendasi untuk siswa:\n\n`;
                    hasil += `1. **Lanjutkan ke materi selanjutnya** dengan tetap memperkuat konsep yang sudah dipahami\n`;
                    hasil += `2. **Ikuti pembelajaran pengayaan** untuk memperdalam pemahaman\n`;
                    hasil += `3. **Berlatih soal-soal dengan tingkat kesulitan lebih tinggi** untuk meningkatkan kemampuan analisis\n`;
                } else if (persentaseBenar >= 50) {
                    hasil += `Berdasarkan hasil analisis, berikut rekomendasi untuk siswa:\n\n`;
                    hasil += `1. **Ulangi pembelajaran** pada bagian-bagian yang masih belum dipahami\n`;
                    hasil += `2. **Ikuti sesi diskusi** untuk memperjelas konsep yang masih membingungkan\n`;
                    hasil += `3. **Berlatih soal-soal serupa** untuk meningkatkan pemahaman\n`;
                } else {
                    hasil += `Berdasarkan hasil analisis, berikut rekomendasi untuk siswa:\n\n`;
                    hasil += `1. **Ikuti pembelajaran remedial** untuk memperkuat pemahaman dasar\n`;
                    hasil += `2. **Diskusikan kesulitan** dengan guru secara personal\n`;
                    hasil += `3. **Dapatkan pendampingan belajar** untuk memahami konsep-konsep kunci\n`;
                    hasil += `4. **Berlatih soal-soal dasar** secara intensif untuk membangun pemahaman\n`;
                }

                return hasil;
            }

            // Fungsi untuk animasi typing teks
            function typeText(element, text) {
                const htmlText = marked.parse(text);
                element.innerHTML = ''; // Kosongkan elemen terlebih dahulu

                let i = 0;
                const speed = 7; // kecepatan typing

                function typing() {
                    if (i < htmlText.length) {
                        element.innerHTML = htmlText.substring(0, i + 1);
                        i++;
                        setTimeout(typing, speed);
                    }
                }

                typing();
            }
        });
    </script>
</body>

</html>