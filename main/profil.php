<?php
include 'includes/session_config.php';
include 'koneksi.php';

if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'siswa') {
    header("Location: index.php");
    exit();
}

$userid = $_SESSION['userid'];
// Di bagian awal file, sebelum mengambil data
// Tentukan default semester dan tahun ajaran
$current_month = date('n');
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : (($current_month >= 7 && $current_month <= 12) ? 1 : 2);
$current_year = date('Y');
$selected_tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : (($selected_semester == 1) ? $current_year . '/' . ($current_year + 1) : ($current_year - 1) . '/' . $current_year);

// Query untuk data siswa
$query_siswa = "SELECT s.*, k.nama_kelas AS kelas_saat_ini
               FROM siswa s 
               LEFT JOIN kelas_siswa ks ON s.id = ks.siswa_id 
               LEFT JOIN kelas k ON ks.kelas_id = k.id 
               WHERE s.username = ?";

$stmt_siswa = mysqli_prepare($koneksi, $query_siswa);
mysqli_stmt_bind_param($stmt_siswa, "s", $userid);
mysqli_stmt_execute($stmt_siswa);
$result_siswa = mysqli_stmt_get_result($stmt_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);

if (!$siswa) {
    echo "Data siswa tidak ditemukan untuk username: $userid";
    exit();
}

// Query untuk data pg
$query_pg = "SELECT 
            AVG(nilai_akademik) as nilai_akademik,
            AVG(keaktifan) as keaktifan,
            AVG(pemahaman) as pemahaman,
            AVG(kehadiran_ibadah) as kehadiran_ibadah,
            AVG(kualitas_ibadah) as kualitas_ibadah,
            AVG(pemahaman_agama) as pemahaman_agama,
            AVG(minat_bakat) as minat_bakat,
            AVG(prestasi) as prestasi,
            AVG(keaktifan_ekskul) as keaktifan_ekskul,
            AVG(partisipasi_sosial) as partisipasi_sosial,
            AVG(empati) as empati,
            AVG(kerja_sama) as kerja_sama,
            AVG(kebersihan_diri) as kebersihan_diri,
            AVG(aktivitas_fisik) as aktivitas_fisik,
            AVG(pola_makan) as pola_makan,
            AVG(kejujuran) as kejujuran,
            AVG(tanggung_jawab) as tanggung_jawab,
            AVG(kedisiplinan) as kedisiplinan
            FROM pg 
            WHERE siswa_id = ? AND semester = ? AND tahun_ajaran = ?";

$stmt_pg = mysqli_prepare($koneksi, $query_pg);
mysqli_stmt_bind_param($stmt_pg, "iis", $siswa['id'], $selected_semester, $selected_tahun_ajaran);
mysqli_stmt_execute($stmt_pg);
$result_pg = mysqli_stmt_get_result($stmt_pg);
$pg_data = mysqli_fetch_assoc($result_pg);

// Gabungkan data atau set default
if ($pg_data) {
    $siswa = array_merge($siswa, $pg_data);
} else {
    $pg_fields = [
        'nilai_akademik',
        'keaktifan',
        'pemahaman',
        'kehadiran_ibadah',
        'kualitas_ibadah',
        'pemahaman_agama',
        'minat_bakat',
        'prestasi',
        'keaktifan_ekskul',
        'partisipasi_sosial',
        'empati',
        'kerja_sama',
        'kebersihan_diri',
        'aktivitas_fisik',
        'pola_makan',
        'kejujuran',
        'tanggung_jawab',
        'kedisiplinan'
    ];

    foreach ($pg_fields as $field) {
        $siswa[$field] = 0;
    }
}

// Function to get grade label and class
function getNilaiLabel($value)
{
    if ($value >= 80) return ['Baik', 'text-success'];
    if ($value >= 60) return ['Cukup', 'text-warning'];
    return ['Kurang', 'text-danger'];
}

// Calculate category averages
function calculateCategoryAverage($values)
{
    $validValues = array_filter($values, function ($v) {
        return $v !== null;
    });
    return empty($validValues) ? 0 : round(array_sum($validValues) / count($validValues));
}

// Get category values
$belajar = calculateCategoryAverage([
    $siswa['nilai_akademik'],
    $siswa['keaktifan'],
    $siswa['pemahaman']
]);

$ibadah = calculateCategoryAverage([
    $siswa['kehadiran_ibadah'],
    $siswa['kualitas_ibadah'],
    $siswa['pemahaman_agama']
]);

$pengembangan = calculateCategoryAverage([
    $siswa['minat_bakat'],
    $siswa['prestasi'],
    $siswa['keaktifan_ekskul']
]);

$sosial = calculateCategoryAverage([
    $siswa['partisipasi_sosial'],
    $siswa['empati'],
    $siswa['kerja_sama']
]);

$kesehatan = calculateCategoryAverage([
    $siswa['kebersihan_diri'],
    $siswa['aktivitas_fisik'],
    $siswa['pola_makan']
]);

$karakter = calculateCategoryAverage([
    $siswa['kejujuran'],
    $siswa['tanggung_jawab'],
    $siswa['kedisiplinan']
]);

// Get grade
function getGrade($value)
{
    if ($value >= 80) return ['Baik', 'text-success'];
    if ($value >= 60) return ['Cukup', 'text-warning'];
    return ['Kurang', 'text-danger'];
}
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
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="icon" type="image/png" href="assets/tab.png">
    <link rel="icon" type="image/png" href="assets/tab.png">
    <title>Profil - SMAGAEdu</title>
</head>
<style>
    @media (max-width: 767px) {
        .fab-container {
            z-index: 1030;
            /* Memastikan FAB selalu di atas modal */
        }

        .modal-dialog {
            margin: 0.5rem;
        }

        .modal {
            z-index: 1055;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        .modal-content {
            border-radius: 1rem;
        }

        .speech-bubble {
            width: 280px;
            /* Sedikit lebih besar untuk mobile */
            right: 10px;
        }
    }

    /* Animasi untuk modal */
    .modal.fade .modal-dialog {
        transform: scale(0.95);
        transition: transform 0.2s ease-out;
    }

    .modal.show .modal-dialog {
        transform: scale(1);
    }
</style>
<style>
    /* Tambahkan atau update CSS berikut */
    body {
        font-family: merriweather;
        overflow-x: hidden;
        /* Mencegah scroll horizontal */
        width: 100%;
    }

    .menu-samping {
        padding-left: 35px !important;
        margin-right: 50px !important;
    }

    .container-fluid {
        padding-left: 0;
        padding-right: 0;
        overflow-x: hidden;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }


    /* Override padding default untuk mobile */
    @media (max-width: 767px) {
        .col-utama {
            padding: 0rem !important;
            /* Mengurangi padding di mobile */
            margin: 0 !important;
            /* Reset margin di mobile */
        }

        .row {
            margin-left: 0;
            margin-right: 0;
        }
    }

    .col-utama {
        margin-left: 12rem;
    }




    /* Memastikan mobile nav tetap */
    .mobile-nav {
        position: fixed;
        width: 100%;
        z-index: 1030;
        left: 0;
        top: 0;
    }

    /* buatkan animasi semua col */
    .col-utama,
    .col-sekunder {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }

    .col-utama.show,
    .col-sekunder.show {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('.col-utama, .col-sekunder').forEach(function(el) {
                el.classList.add('show');
            });
        }, 100);
    });
</script>

<body>

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



    <!-- ini isi kontennya -->
    <div class="col pt-0 p-2 p-md-4 col-utama">
        <div class="row g-4">
            <!-- Kolom Kiri (Profil dan Nilai) -->
            <div class="col-md-8 ">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-md-1 border-0 p-0 m-0 shadow-md-sm  rounded-4">
                            <div class="card-body p-4 body-identitas">
                                <div class="d-flex flex-column">
                                    <!-- Profile Settings -->
                                    <div class="d-flex flex-column">
                                        <!-- Header with profile image -->
                                        <div class="text-center mb-4">
                                            <div class="position-relative d-inline-block">
                                                <img src="<?php
                                                            if (!empty($siswa['photo_type'])) {
                                                                if ($siswa['photo_type'] === 'avatar') {
                                                                    echo $siswa['photo_url']; // Tampilkan avatar Dicebear
                                                                } else if ($siswa['photo_type'] === 'upload') {
                                                                    echo 'uploads/profil/' . $siswa['foto_profil']; // Tampilkan foto upload
                                                                }
                                                            } else {
                                                                echo 'assets/pp.png'; // Tampilkan foto default
                                                            }
                                                            ?>"
                                                    class="rounded-circle border shadow-sm"
                                                    width="150px" height="150px"
                                                    style="object-fit: cover;">

                                                <!-- Edit button overlay -->
                                                <button class="btn btn-light btn-sm rounded-pill position-absolute bottom-0 end-0 shadow-sm border d-flex align-items-center"
                                                    style="margin: 4px;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editProfileModal">
                                                    <i class="ti ti-pencil me-1"></i>
                                                    <span style="font-size: 12px;">Edit</span>
                                                </button>
                                            </div>

                                            <h4 class="mt-3 mb-1">
                                                Halo, <?php
                                                        $nama_depan = explode(' ', $siswa['nama'])[0];
                                                        echo ucwords(htmlspecialchars($nama_depan));
                                                        ?>
                                            </h4>
                                            <p class="text-muted  mb-3">
                                                Informasi tentangmu dan preferensimu <br> di berbagai layanan SMAGAEdu
                                            </p>
                                        </div>
                                    </div>


                                    <!-- Profile Info -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-0">
                                            <div class="m-0 p-0">
                                                <span class="bi bi-person-check" style="color:#c56a4d; font-size: 40px;"></span>
                                            </div>
                                            <div>
                                                <h5 class="p-0 m-0 judul" style="font-size: 16px; font-weight:bold;">Identitas Siswa</h5>
                                                <p style="font-size: 14px;" class="p-0 m-0 desc text-muted">Data kamu dikelola oleh Wali Kelas dan Guru BK. <a href="#" data-bs-toggle="modal" data-bs-target="#identitasInfoModal">Info selengkapnya.</a></p>
                                            </div>
                                            <style>
                                                @media screen and (max-width: 768px) {
                                                    .desc {
                                                        font-size: 10px;
                                                    }

                                                }
                                            </style>
                                        </div>
                                        <hr class="mb-3 mt-1">

                                        <!-- Settings List -->
                                        <div class="settings-list">
                                            <!-- Profile Photo & Name Section -->
                                            <div class="settings-section">
                                                <!-- Name Setting -->
                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-person fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">Nama Lengkap</p>
                                                        <span class="text-muted small"><?php echo ucwords(strtolower(htmlspecialchars($siswa['nama']))); ?></span>
                                                    </div>
                                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Terkunci">
                                                        <i class="ti ti-lock"></i>
                                                    </button>
                                                </div>

                                                <!-- Profile Photo Setting -->
                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-camera fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">Foto Profil</p>
                                                        <span class="text-muted small">Gambar profil membantu personalisasi akunmu</span>
                                                    </div>
                                                    <button class="btn btn-light d-flex gap-2 btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editProfileModal">
                                                        <i class="bi bi-pencil"></i>
                                                        <!-- <p class="p-0 m-0">Edit</p> -->
                                                    </button>
                                                </div>

                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-key fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">Password</p>
                                                        <div class="d-flex align-items-center">
                                                            <span id="password" class="text-muted small">•••••••</span>
                                                            <button class="btn btn-link btn-sm p-0 ms-2" onclick="togglePassword()">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Terkunci">
                                                        <i class="ti ti-lock"></i>
                                                    </button>
                                                </div>



                                                <!-- Personal Info Section -->
                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-person-badge fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">NIS</p>
                                                        <span class="text-muted small"><?php echo htmlspecialchars($siswa['nis']); ?></span>
                                                    </div>
                                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Terkunci">
                                                        <i class="ti ti-lock"></i>
                                                    </button>
                                                </div>

                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-mortarboard fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">Kelas/Fase</p>
                                                        <span class="text-muted small"><?php echo htmlspecialchars($siswa['tingkat']); ?></span>
                                                    </div>
                                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Terkunci">
                                                        <i class="ti ti-lock"></i>
                                                    </button>
                                                </div>

                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-calendar fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">Tahun Masuk</p>
                                                        <span class="text-muted small"><?php echo htmlspecialchars($siswa['tahun_masuk']); ?></span>
                                                    </div>
                                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Terkunci">
                                                        <i class="ti ti-lock"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Contact Info Section -->
                                            <div class="settings-section mt-2">
                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-telephone fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">No. Telepon</p>
                                                        <span class="text-muted small"><?php echo htmlspecialchars($siswa['no_hp']); ?></span>
                                                    </div>
                                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Terkunci">
                                                        <i class="ti ti-lock"></i>
                                                    </button>
                                                </div>

                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 mb-2">
                                                    <i class="bi bi-geo-alt fs-4 me-3" style="color:#c56a4d;"></i>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 fw-medium">Alamat</p>
                                                        <span class="text-muted small"><?php echo htmlspecialchars($siswa['alamat']); ?></span>
                                                    </div>
                                                    <button class="btn btn-light btn-sm rounded-3 border shadow-sm"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Terkunci">
                                                        <i class="ti ti-lock"></i>
                                                    </button>
                                                </div>

                                                <div class="settings-item d-flex align-items-center p-3 rounded-3 bg-danger mb-2">
                                                    <i class="bi bi-box-arrow-right fs-4 me-3" style="color:white;"></i>
                                                    <div class="flex-grow-1 text-white">
                                                        <p class="mb-0 fw-medium">Logout</p>
                                                        <span class="small">Keluar dari akun SMAGAEdu</span>
                                                    </div>
                                                    <a href="logout.php" class="btn btn-light btn-sm rounded-3 border shadow-sm">
                                                        <i class="bi bi-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <style>
                                        .settings-item {
                                            background: #fff;
                                            transition: background-color 0.2s;
                                            border: 1px solid #eee;
                                        }

                                        .settings-item:hover {
                                            background: #f8f9fa;
                                            cursor: pointer;
                                        }

                                        @media screen and (max-width: 768px) {
                                            .desc {
                                                font-size: 10px;
                                            }

                                            .settings-item {
                                                padding: 0.75rem !important;
                                            }

                                            .settings-item i {
                                                font-size: 1.25rem !important;
                                            }
                                        }
                                    </style>
                                    <style>
                                        .icon-border {
                                            border: 1px solid #e9ecef;
                                            padding: 10px;
                                            border-radius: 15px;
                                        }

                                        @media screen and (max-width: 768px) {
                                            .body-identitas {
                                                padding: 0 !important;
                                                margin-top: 30px !important;
                                            }

                                        }
                                    </style>
                                    <!-- script buat tampilkan popup -->
                                    <script>
                                        // Initialize tooltips
                                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                                        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                            return new bootstrap.Tooltip(tooltipTriggerEl)
                                        })
                                    </script>
                                    <script>
                                        function toggleUsername() {
                                            const usernameSpan = document.getElementById('username');
                                            const hidden = usernameSpan.textContent === '•••••••';
                                            usernameSpan.textContent = hidden ? '<?php echo $siswa["username"]; ?>' : '•••••••';
                                        }

                                        function togglePassword() {
                                            const passwordSpan = document.getElementById('password');
                                            const hidden = passwordSpan.textContent === '•••••••';
                                            passwordSpan.textContent = hidden ? '<?php echo $siswa["password"]; ?>' : '•••••••';
                                        }
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- grafik chart -->
                <!-- Dropdown and Charts Container -->
                <div class="row p-0 p-md-2 p-md-4">
                    <!-- Setelah judul Progressive Guidance, tambahkan info semester/tahun ajaran -->
                    <div class="d-flex align-items-center mt-4 mt-mb-0 gap-2 mb-0">
                        <div class="m-0 p-0">
                            <span class="bi bi-lightbulb" style="color:#c56a4d; font-size: 40px;"></span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="p-0 m-0 judul" style="font-size: 16px; font-weight:bold;">Progressive Guidance</h5>
                            <p style="font-size: 14px;" class="p-0 m-0 desc text-muted">
                                Penilaian aktifitas kamu pada Semester <?= $selected_semester ?> Tahun Ajaran <?= $selected_tahun_ajaran ?>.
                                <a href="#" data-bs-toggle="modal" data-bs-target="#pgInfoModal">Info selengkapnya.</a>
                            </p>
                        </div>
                        <div class="position-relative d-none d-md-block">
                            <button type="button" class="btn btn-light btn-sm rounded-pill border shadow-sm d-flex" id="filterButton" style="font-size: 12px;">
                                <i class="bi bi-funnel-fill me-1" style="color:#c56a4d;"></i> Filter
                            </button>

                            <!-- Filter Popup -->
                            <div class="filter-popup shadow" id="filterPopup">
                                <div class="filter-popup-body">
                                    <form method="GET" id="popupFilterForm">
                                        <div class="mb-3">
                                            <label class="form-label small">Semester</label>
                                            <select name="semester" class="form-select form-select-sm rounded-3">
                                                <option value="1" <?php echo ($selected_semester == 1) ? 'selected' : ''; ?>>Semester 1</option>
                                                <option value="2" <?php echo ($selected_semester == 2) ? 'selected' : ''; ?>>Semester 2</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small">Tahun Ajaran</label>
                                            <select name="tahun_ajaran" class="form-select form-select-sm rounded-3">
                                                <?php
                                                $current_year = date('Y');
                                                for ($i = $current_year - 5; $i <= $current_year + 1; $i++) {
                                                    $tahun_option = $i . '/' . ($i + 1);
                                                    $selected = ($tahun_option == $selected_tahun_ajaran) ? 'selected' : '';
                                                    echo "<option value='$tahun_option' $selected>$tahun_option</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="d-grid mt-4">
                                            <button type="submit" class="btn btn-sm rounded-3 py-2" style="background-color: rgb(218, 119, 86); color: white;">
                                                <i class="bi bi-search me-2"></i>Tampilkan Data
                                            </button>
                                            <p class="text-muted text-center mt-1" style="font-size: 10px;">Diperlukan mulai ulang halaman</p>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Perbaikan CSS untuk popup -->
                    <style>
                        .filter-popup {
                            display: none;
                            position: absolute;
                            bottom: 100%;
                            /* Posisi di atas tombol */
                            right: 0;
                            width: 280px;
                            background: white;
                            border-radius: 12px;
                            z-index: 1050;
                            overflow: hidden;
                            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                            margin-bottom: 10px;
                            /* Beri jarak dari tombol */
                        }

                        .filter-popup-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            padding: 12px 15px;
                            background-color: rgb(218, 119, 86);
                            color: white;
                        }

                        .filter-popup-body {
                            padding: 15px;
                        }

                        @media (max-width: 767px) {
                            .filter-popup {
                                width: calc(100vw - 20px);
                                max-width: 320px;
                                right: -10px;
                                /* Sedikit penyesuaian untuk mobile */
                            }
                        }

                        /* Tambahkan arrow ke bawah popup */
                        .filter-popup::after {
                            content: '';
                            position: absolute;
                            bottom: -8px;
                            right: 15px;
                            width: 0;
                            height: 0;
                            border-left: 8px solid transparent;
                            border-right: 8px solid transparent;
                            border-top: 8px solid white;
                        }
                    </style>

                    <!-- Perbaikan JavaScript untuk popup -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const filterButton = document.getElementById('filterButton');
                            const filterPopup = document.getElementById('filterPopup');
                            const closeFilterPopup = document.getElementById('closeFilterPopup');

                            // Menambahkan tampilan awal "none" via JavaScript sebagai double-check
                            if (filterPopup) {
                                filterPopup.style.display = 'none';
                            }

                            if (filterButton) {
                                filterButton.addEventListener('click', function(event) {
                                    event.stopPropagation();

                                    // Toggle visibility
                                    if (filterPopup.style.display === 'block') {
                                        filterPopup.style.display = 'none';
                                    } else {
                                        filterPopup.style.display = 'block';
                                    }
                                    console.log('Button clicked - popup visibility: ' + filterPopup.style.display);
                                });
                            }

                            if (closeFilterPopup) {
                                closeFilterPopup.addEventListener('click', function(event) {
                                    event.stopPropagation(); // Hentikan event bubbling
                                    filterPopup.style.display = 'none';
                                    console.log('Close clicked');
                                });
                            }

                            if (filterPopup) {
                                // Mencegah popup menutup saat mengklik di dalam popup
                                filterPopup.addEventListener('click', function(event) {
                                    event.stopPropagation();
                                });
                            }

                            // Close popup when clicking outside
                            document.addEventListener('click', function(event) {
                                if (filterPopup && filterPopup.style.display === 'block') {
                                    filterPopup.style.display = 'none';
                                    console.log('Clicked outside - closing popup');
                                }
                            });

                            // Debug - tampilkan elemen popup di console
                            console.log('Filter Button:', filterButton);
                            console.log('Filter Popup:', filterPopup);
                        });
                    </script>
                    <hr class="mb-3 mt-1">

                    <div class="col-12">
                        <div class="border rounded-4 p-4 shadow-sm">
                            <div style="height: 200px">
                                <canvas id="barChart"></canvas>
                            </div>

                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                                const ctx = document.getElementById('barChart').getContext('2d');
                                const barChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: ['Belajar', 'Ibadah', 'Pengembangan', 'Sosial', 'Kesehatan', 'Karakter'],
                                        datasets: [{
                                            data: [
                                                <?php echo $belajar; ?>,
                                                <?php echo $ibadah; ?>,
                                                <?php echo $pengembangan; ?>,
                                                <?php echo $sosial; ?>,
                                                <?php echo $kesehatan; ?>,
                                                <?php echo $karakter; ?>
                                            ],
                                            backgroundColor: 'rgba(218, 119, 86, 0.2)',
                                            borderColor: 'rgb(218, 119, 86)',
                                            borderWidth: 1,
                                            borderRadius: 8
                                        }]
                                    },
                                    options: {
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 100,
                                                ticks: {
                                                    stepSize: 20
                                                }
                                            }
                                        }
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>

                <!-- Untuk tampilan mobile, tambahkan di bawah judul Progressive Guidance -->
                <div class="d-md-none mt-2 py-2 px-3 mb-3">
                    <form method="GET" id="mobileFilterForm" class="d-flex flex-wrap gap-2">
                        <div class="input-group input-group-sm w-100 mb-2">
                            <select name="semester" class="form-select form-select-sm border-0 bg-light rounded-3">
                                <option value="1" <?php echo ($selected_semester == 1) ? 'selected' : ''; ?>>Semester 1</option>
                                <option value="2" <?php echo ($selected_semester == 2) ? 'selected' : ''; ?>>Semester 2</option>
                            </select>
                        </div>

                        <div class="input-group input-group-sm w-100 mb-2">
                            <select name="tahun_ajaran" class="form-select form-select-sm border-0 bg-light rounded-3">
                                <?php
                                $current_year = date('Y');
                                for ($i = $current_year - 5; $i <= $current_year + 1; $i++) {
                                    $tahun_option = $i . '/' . ($i + 1);
                                    $selected = ($tahun_option == $selected_tahun_ajaran) ? 'selected' : '';
                                    echo "<option value='$tahun_option' $selected>$tahun_option</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-sm w-100 rounded-pill" style="background-color: rgb(218, 119, 86); color: white;">
                            <i class="bi bi-filter"></i> Tampilkan
                        </button>
                    </form>
                </div>

                <!-- Content Grid -->
                <div class="row g-4 p-md-4">
                    <!-- Behavior & Character -->
                    <div class="col-md-4">
                        <div class="border rounded-4 p-4 shadow-sm">
                            <h5 class="mb-4" style="font-size: 14px;"><i class="bi bi-mortarboard-fill text-primary"></i> Pendampingan</h5>
                            <div class="mb-4">
                                <div class="mb-2">
                                    <h6 class="m-0">Belajar</h6>
                                    <?php list($label, $class) = getGrade($belajar); ?>
                                    <span class="p-0 m-0 badge <?= $class ?>"><?= $label ?> (<?= $belajar ?>%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar color-web" style="width: <?= $belajar ?>%"></div>
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 12px;">
                                    <div class="d-flex justify-content-between">
                                        <span>Nilai Akademik</span>
                                        <span><?= round($siswa['nilai_akademik']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Keaktifan</span>
                                        <span><?= round($siswa['keaktifan']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Pemahaman</span>
                                        <span><?= round($siswa['pemahaman']) ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="mb-2">
                                    <h6 class="m-0">Ibadah</h6>
                                    <?php list($label, $class) = getGrade($ibadah); ?>
                                    <span class="badge p-0 m-0 <?= $class ?>"><?= $label ?> (<?= $ibadah ?>%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar color-web" style="width: <?= $ibadah ?>%"></div>
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 12px;">
                                    <div class="d-flex justify-content-between">
                                        <span>Kehadiran Ibadah</span>
                                        <span><?= round($siswa['kehadiran_ibadah']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kualitas Ibadah</span>
                                        <span><?= round($siswa['kualitas_ibadah']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Pemahaman Agama</span>
                                        <span><?= round($siswa['pemahaman_agama']) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-4 p-4 shadow-sm">
                            <h5 class="mb-4" style="font-size: 14px;"><i class="bi bi-graph-up-arrow text-success"></i> Pengembangan</h5>
                            <div class="mb-4">
                                <div class="mb-2">
                                    <h6 class="m-0">Pengembangan Diri</h6>
                                    <?php list($label, $class) = getGrade($pengembangan); ?>
                                    <span class="badge p-0 m-0 <?= $class ?>"><?= $label ?> (<?= $pengembangan ?>%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar color-web" style="width: <?= $pengembangan ?>%"></div>
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 12px;">
                                    <div class="d-flex justify-content-between">
                                        <span>Minat Bakat</span>
                                        <span><?= round($siswa['minat_bakat']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Prestasi</span>
                                        <span><?= round($siswa['prestasi']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Keaktifan Ekstrakurikuler</span>
                                        <span><?= round($siswa['keaktifan_ekskul']) ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="mb-2">
                                    <h6 class="m-0">Sosial</h6>
                                    <?php list($label, $class) = getGrade($sosial); ?>
                                    <span class="badge p-0 m-0 <?= $class ?>"><?= $label ?> (<?= $sosial ?>%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar color-web" style="width: <?= $sosial ?>%"></div>
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 12px;">
                                    <div class="d-flex justify-content-between">
                                        <span>Partisipasi Sosial</span>
                                        <span><?= round($siswa['partisipasi_sosial']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Empati</span>
                                        <span><?= round($siswa['empati']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kerja Sama</span>
                                        <span><?= round($siswa['kerja_sama']) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-4 p-4 shadow-sm">
                            <h5 class="mb-4" style="font-size: 14px;"><i class="bi bi-shield-check text-warning"></i> Kesehatan & Karakter</h5>
                            <div class="mb-4">
                                <div class="mb-2">
                                    <h6 class="m-0">Kesehatan</h6>
                                    <?php list($label, $class) = getGrade($kesehatan); ?>
                                    <span class="badge p-0 m-0 <?= $class ?>"><?= $label ?> (<?= $kesehatan ?>%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar color-web" style="width: <?= $kesehatan ?>%"></div>
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 12px;">
                                    <div class="d-flex justify-content-between">
                                        <span>Kebersihan Diri</span>
                                        <span><?= round($siswa['kebersihan_diri']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Aktivitas Fisik</span>
                                        <span><?= round($siswa['aktivitas_fisik']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Pola Makan</span>
                                        <span><?= round($siswa['pola_makan']) ?>%</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="mb-2">
                                    <h6 class="m-0">Karakter</h6>
                                    <?php list($label, $class) = getGrade($karakter); ?>
                                    <span class="badge p-0 m-0 <?= $class ?>"><?= $label ?> (<?= $karakter ?>%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar color-web" style="width: <?= $karakter ?>%"></div>
                                </div>
                                <div class="mt-2 text-muted small" style="font-size: 12px;">
                                    <div class="d-flex justify-content-between">
                                        <span>Kejujuran</span>
                                        <span><?= round($siswa['kejujuran']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Tanggung Jawab</span>
                                        <span><?= round($siswa['tanggung_jawab']) ?>%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kedisiplinan</span>
                                        <span><?= round($siswa['kedisiplinan']) ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan (AI Assistant) -->
            <div class="col-md-4 ai-col d-flex d-none d-md-block">
                <div class="sticky-top card rounded-4" style="top: 20px;">
                    <div class="card-header d-flex p-4 pb-3 bg-white rounded-top-4">
                        <span class="bi bi-stars me-2" style="font-size: 30px; color:rgb(218, 119, 86)"></span>
                        <div>
                            <h5 class="mb-0">Analisis Profil SAGA</h5>
                            <p class="text-muted mb-0" style="font-size: 12px;">Integrasi Layanan SAGA AI</p>
                        </div>
                    </div>
                    <!-- AI Assistant Card -->
                    <div class="">
                        <div class="p-3 d-flex justify-content-center align-items-center" style="height: 32rem;">
                            <?php
                            $groq_api_key = 'YOUR_API_KEY';
                            $should_analyze = isset($_POST['start_analysis']) && $_POST['start_analysis'] === 'true';

                            if ($should_analyze) {
                                if (!empty($groq_api_key)) {
                                    // Format data untuk prompt
                                    $categories = [
                                        'Belajar' => [
                                            'Nilai Akademik' => $siswa['nilai_akademik'],
                                            'Keaktifan' => $siswa['keaktifan'],
                                            'Pemahaman' => $siswa['pemahaman']
                                        ],
                                        'Ibadah' => [
                                            'Kehadiran Ibadah' => $siswa['kehadiran_ibadah'],
                                            'Kualitas Ibadah' => $siswa['kualitas_ibadah'],
                                            'Pemahaman Agama' => $siswa['pemahaman_agama']
                                        ],
                                        'Pengembangan' => [
                                            'Minat Bakat' => $siswa['minat_bakat'],
                                            'Prestasi' => $siswa['prestasi'],
                                            'Keaktifan Ekstrakurikuler' => $siswa['keaktifan_ekskul']
                                        ],
                                        'Sosial' => [
                                            'Partisipasi Sosial' => $siswa['partisipasi_sosial'],
                                            'Empati' => $siswa['empati'],
                                            'Kerja Sama' => $siswa['kerja_sama']
                                        ],
                                        'Kesehatan' => [
                                            'Kebersihan Diri' => $siswa['kebersihan_diri'],
                                            'Aktivitas Fisik' => $siswa['aktivitas_fisik'],
                                            'Pola Makan' => $siswa['pola_makan']
                                        ],
                                        'Karakter' => [
                                            'Kejujuran' => $siswa['kejujuran'],
                                            'Tanggung Jawab' => $siswa['tanggung_jawab'],
                                            'Kedisiplinan' => $siswa['kedisiplinan']
                                        ]
                                    ];

                                    $prompt = "Halo {$siswa['nama']}! Aku akan bantu analisis perkembanganmu di SMAGAEdu. Berikut datanya:\n\n";

                                    foreach ($categories as $category => $subjects) {
                                        $prompt .= "- {$category}: ";
                                        $prompt .= implode(', ', array_map(
                                            fn($subject, $value) => "$subject (" . round($value) . "%)",
                                            array_keys($subjects),
                                            $subjects
                                        ));
                                        $prompt .= "\n";
                                    }

                                    $prompt .= "\nBuatkan analisis dengan struktur:
                1. Salam penyemangat menggunakan nama panggilan
                2. 1 kalimat positif tentang prestasi terbaik
                3. 2 area perlu perbaikan dengan bahasa kasual
                4. 2 saran konkret untuk perbaikan
                5. Kalimat penutup motivasi

                Gunakan:
                - Bahasa Indonesia santai
                - 1-2 emoji yang relevan
                - Saran yang konkret
                - Kalimat pendek dan jelas
                - Tagar #BelajarBersamaSMAGAEdu";

                                    $ch = curl_init();
                                    curl_setopt_array($ch, [
                                        CURLOPT_URL => 'https://api.groq.com/openai/v1/chat/completions',
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_POST => true,
                                        CURLOPT_POSTFIELDS => json_encode([
                                            'model' => 'mistral-saba-24b',
                                            'messages' => [['role' => 'user', 'content' => $prompt]],
                                            'temperature' => 0.8,
                                            'max_tokens' => 700
                                        ]),
                                        CURLOPT_HTTPHEADER => [
                                            'Content-Type: application/json',
                                            'Authorization: Bearer ' . $groq_api_key
                                        ]
                                    ]);

                                    $response = curl_exec($ch);
                                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                                    curl_close($ch);

                                    if ($http_code == 200) {
                                        $response_data = json_decode($response, true);
                                        $raw_text = $response_data['choices'][0]['message']['content'];
                                        $full_text = $raw_text;

                                        // Ubah tanda * menjadi tag HTML <strong>
                                        $formatted_text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $raw_text);
                                        $formatted_text = preg_replace('/\*(.*?)\*/', '<strong>$1</strong>', $formatted_text);

                                        // Tambahkan line breaks
                                        $formatted_text = nl2br($formatted_text);

                                        // Tangani keyword-keyword tertentu dengan bold
                                        $formatted_text = preg_replace(
                                            '/(Halo|Hai|Wah|Nilai|Saran|Rekomendasi|Tips|Solusi|Yuk|Ayo|Perhatian|Poin|Kesimpulan)(.*?)(:|!)/',
                                            '<strong>$1$2$3</strong>',
                                            $formatted_text
                                        );

                                        // Simpan formatted_text untuk digunakan
                                        $_SESSION['formatted_ai_response'] = $formatted_text;

                                        // JANGAN gunakan strip_tags - itu menghapus format
                                        // Gunakan characters per character alih-alih words untuk animasi
                                        $chars = preg_split('//u', $formatted_text, -1, PREG_SPLIT_NO_EMPTY);

                            ?>
                                        <div id="aiResponseContainer" style=" overflow-y:scroll;"></div>



                                        <script>
                                            (function() {
                                                const container = document.getElementById('aiResponseContainer');
                                                const markdownText = <?= json_encode($raw_text) ?>;

                                                // Parse markdown ke HTML
                                                container.innerHTML = marked.parse(markdownText);
                                            })();
                                        </script>
                                    <?php
                                    } else {
                                    ?>
                                        <div class='alert alert-warning rounded-3'>
                                            <i class='bi bi-exclamation-triangle me-2'></i>
                                            Asisten sedang offline. Kode error: <?php echo $http_code; ?>
                                        </div>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <div class='alert alert-warning rounded-3'>
                                        <i class='bi bi-exclamation-triangle me-2'></i>
                                        Fitur AI belum dikonfigurasi
                                    </div>
                                <?php
                                }
                            } else {
                                // Initial state with button
                                ?>
                                <div id="initialState" class="text-center p-4">
                                    <div class="mb-4">
                                        <img src="assets/analisis_wide.png" alt="Presentasi Header" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;">
                                    </div>
                                    <h3 class="mb-3 mt-2 fw-bold">Analisis Penilaian Profilmu Bersama SAGA</h3>
                                    <p class="text-muted mb-4">Dapatkan kondisi, evaluasi, dan rekomendasi hasil penilaianmu dengan bantuan analisis SAGA AI</p>
                                    <form method="POST">
                                        <input type="hidden" name="start_analysis" value="true">
                                        <button type="submit" class="btn btn-sm px-4 rounded-3" disabled style="background-color: #da7756; color: white;">
                                            Analisis Sekarang
                                        </button>
                                    </form>
                                    <p style="font-size: 10px;" class="mt-1 text-muted">Diperlukan memuat ulang halaman</p>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>

                    <style>
                        .ai-col {
                            animation: fadeIn 1s ease-in-out;
                        }

                        @keyframes fadeIn {
                            0% {
                                opacity: 0;
                            }

                            100% {
                                opacity: 1;
                            }
                        }

                        #aiResponseContainer {
                            height: 100%;
                            /* pastikan tingginya terdefinisi dengan jelas */
                            max-height: 34rem;
                            /* sesuaikan dengan kebutuhan */
                            overflow-y: auto !important;
                            /* paksa overflow scroll */
                            overflow-x: hidden;
                            position: relative;
                            z-index: 1;
                            /* pastikan z-index sesuai */
                            padding: 1rem;
                            background-color: #fff;
                            /* berikan background */
                            border-radius: 0.5rem;
                            /* opsional untuk estetika */
                            word-wrap: break-word;
                            /* pastikan teks panjang wrap */
                        }

                        #aiResponseContainer strong {
                            color: #da7756;
                            font-weight: 600;
                        }

                        .ai-expand-btn {
                            background-color: #da7756;
                            color: white;
                            border: none;
                            padding: 0.5rem 1rem;
                            border-radius: 0.5rem;
                            font-size: 0.9rem;
                            transition: all 0.3s ease;
                        }

                        .ai-expand-btn:hover {
                            background-color: #c56a4d;
                        }
                    </style>

                </div>
            </div>
        </div>
    </div>





    <!-- Tambahkan sebelum closing body tag -->

    <!-- FAB dengan Speech Bubble -->
    <div class="fab-container d-md-none">
        <!-- Speech Bubble -->
        <div class="speech-bubble border" id="aiBubble">
            <?php if (!isset($_POST['start_analysis'])) { ?>
                <p class="p-0 m-0">Ingin SAGA mengevaluasi hasil pantauanmu di sekolah?</p>
                <p style="font-size: 12px;" class="text-muted mb-2">Kami akan memuat ulang halaman untuk memuat analisis</p>
                <form method="POST" style="display: contents;">
                    <input type="hidden" name="start_analysis" value="true">
                    <button type="submit" class="btn btn-sm w-100 color-web text-white">
                        <i class="bi bi-magic me-2"></i>Mulai Analisis
                    </button>
                </form>
            <?php } else { ?>
                <p class="mb-2">Hasil analisis SAGA sudah siap!</p>
                <button class="btn btn-sm w-100 color-web text-white" data-bs-toggle="modal" data-bs-target="#aiDetailModal" onclick="hideBubble()">
                    Lihat Analisis
                </button>
            <?php } ?>
        </div>

        <!-- FAB Button -->
        <button class="fab-button" id="fabAI">
            <i class="bi bi-stars"></i>
        </button>
    </div>

    <!-- style modal mobile -->


    <!-- Modal for Mobile -->
    <div class="modal fade" id="aiDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #c56a4d;">
                    <h5 class="modal-title">
                        <i class="bi bi-stars me-2"></i>
                        <?php echo !isset($_POST['start_analysis']) ? 'Mulai Analisis SAGA' : 'Analisis Lengkap SAGA'; ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="height: calc(100vh - 200px); overflow-y: auto;">
                    <?php if (!isset($_POST['start_analysis'])) { ?>
                        <!-- Initial State untuk Mobile -->
                        <div class="text-center p-4">
                            <img src="/api/placeholder/200/200" alt="SAGA AI Icon" class="mb-4">
                            <h4 class="mb-3" style="color: #da7756;">Ingin Tahu Perkembanganmu?</h4>
                            <p class="text-muted mb-4">SAGA AI akan menganalisis profilmu dan memberikan saran yang personal untuk kemajuanmu!</p>
                            <form method="POST">
                                <input type="hidden" name="start_analysis" value="true">
                                <button type="submit" class="btn px-4 py-2 rounded-3" style="background-color: #da7756; color: white;">
                                    <i class="bi bi-magic me-2"></i>Mulai Analisis SAGA
                                </button>
                            </form>
                        </div>
                    <?php } else { ?>
                        <div class="p-3">
                            <?php echo isset($full_text) ? nl2br(htmlspecialchars($full_text)) : 'Memuat analisis...'; ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="modal-footer d-flex">
                    <button type="button" class="btn color-web flex-fill text-white" data-bs-dismiss="modal">
                        <?php echo !isset($_POST['start_analysis']) ? 'Tutup' : 'Baik, saya mengerti'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fab-container {
            position: fixed;
            bottom: 6rem;
            right: 2rem;
        }

        .fab-button {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgb(218, 119, 86);
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: white;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fab-button:hover {
            background: rgb(219, 106, 68);
        }

        /* Speech Bubble Styling */
        .speech-bubble {
            position: absolute;
            bottom: 80px;
            right: 0;
            background: white;
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 250px;
            display: none;
            animation: fadeIn 0.3s ease-out;
            font-size: 0.9rem;
        }

        .speech-bubble::after {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 20px;
            border-width: 10px 10px 0;
            border-style: solid;
            border-color: white transparent transparent;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .fab-button {
            animation: pulse 2s infinite;
        }

        /* Show/Hide Speech Bubble */
        .speech-bubble.show {
            display: block;
        }
    </style>

    <script>
        let bubbleTimeout;

        function showBubble() {
            const bubble = document.getElementById('aiBubble');
            bubble.classList.add('show');

            // Auto hide after 5 seconds
            bubbleTimeout = setTimeout(() => {
                hideBubble();
            }, 5000);
        }

        function hideBubble() {
            const bubble = document.getElementById('aiBubble');
            if (bubble) {
                bubble.classList.remove('show');
                clearTimeout(bubbleTimeout);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const fab = document.getElementById('fabAI');

            // Automatically show bubble after 2 seconds
            setTimeout(() => {
                showBubble();
            }, 2000);

            fab.addEventListener('click', function(e) {
                e.stopPropagation();
                const bubble = document.getElementById('aiBubble');

                if (bubble.classList.contains('show')) {
                    hideBubble();
                } else {
                    showBubble();
                }
            });

            // Hide bubble when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.fab-container')) {
                    hideBubble();
                }
            });
        });
    </script>

    <!-- Modal Edit Profile -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-body p-4">
                    <!-- Simplified Tab Navigation -->
                    <ul class="nav nav-tabs d-flex gap-3 mb-4 border-0">
                        <li class="nav-item flex-grow-1">
                            <button class="btn w-100 position-relative tab-btn active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab" aria-controls="upload" aria-selected="true">
                                <i class="bi bi-upload me-2"></i>Upload
                                <div class="tab-indicator"></div>
                            </button>
                        </li>
                        <li class="nav-item flex-grow-1">
                            <button class="btn w-100 position-relative tab-btn" id="avatar-tab" data-bs-toggle="tab" data-bs-target="#avatar" type="button" role="tab" aria-controls="avatar" aria-selected="false">
                                <i class="bi bi-person-bounding-box me-2"></i>Avatar
                                <div class="tab-indicator"></div>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="profileTabContent">
                        <!-- Upload Photo Tab -->
                        <div class="tab-pane fade show active" id="upload">
                            <div class="alert border bg-light mt-3" style="border-radius: 15px;">
                                <div class="d-flex">
                                    <i class="ti ti-progress-alert fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="fw-bold p-0 m-0" style="font-size: 14px;">Gunakan Foto Profil Yang Sopan</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                                            Foto profilmu akan terlihat oleh siapapun, kamu harus bertanggung jawab atas akunmu termasuk foto profil yang kamu gunakan
                                        </p>

                                    </div>
                                </div>
                            </div>
                            <form id="uploadForm">
                                <input type="hidden" name="siswa_id" value="<?php echo $siswa['id']; ?>">
                                <div class="upload-area" id="uploadArea">
                                    <i class="ti ti-photo-plus display-4" style="color: rgb(218, 119, 86);"></i>
                                    <p class="mt-2 mb-1" style="font-size: 12px;">Drag & drop foto di sini atau <br> klik untuk mengupload gambar</p>
                                    <button type="button" class="position-absolute opacity-0" onclick="document.getElementById('photoInput').click()">
                                    </button>
                                </div>
                                <input type="file" class="d-none" id="photoInput" accept="image/*">

                                <!-- Cropper Container -->
                                <div class="cropper-container mt-4" style="display: none;">
                                    <div class="img-container mb-3">
                                        <img id="cropperImage" src="" style="max-width: 100%;">
                                    </div>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-tool" onclick="rotateImage(-90)" title="Putar kiri">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" onclick="rotateImage(90)" title="Putar kanan">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" onclick="resetCropper()" title="Reset">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Avatar Tab -->
                        <div class="tab-pane fade" id="avatar">
                            <!-- Alert untuk sopan -->
                            <div class="alert border bg-light mt-3" style="border-radius: 15px;">
                                <div class="d-flex">
                                    <i class="ti ti-progress-alert fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                                    <div>
                                        <p class="fw-bold p-0 m-0" style="font-size: 14px;">Gunakan Foto Profil Yang Sopan</p>
                                        <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                                            Foto profilmu akan terlihat oleh siapapun, kamu harus bertanggung jawab atas akunmu termasuk foto profil yang kamu gunakan
                                        </p>

                                    </div>
                                </div>
                            </div>

                            <select class="form-select form-select-sm mb-3" id="avatarStyle" onchange="updateAvatars()">
                                <option value="avataaars">Headshot</option>
                                <option value="bottts">Robot</option>
                                <option value="adventurer">Petualang</option>
                                <option value="fun-emoji">Emoji</option>
                                <option value="pixel-art">Pixel Art</option>
                                <option value="initials">Inisial</option>
                                <option value="croodles-neutral">Doodle</option>
                                <option value="icons">Simbol</option>
                                <option value="identicon">Identicon</option>
                                <option value="lorelei">Notion</option>
                                <option value="notionists-neutral">Notion II</option>
                                <option value="notionists">Notion III</option>
                                <option value="thumbs">Jempol</option>
                                <option value="shapes">Bentuk Ruang</option>
                            </select>
                            <div class="avatar-grid" id="avatarGrid"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer btn-group border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light border" style="border-radius: 15px;" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn text-white" id="saveButton" onclick="saveProfilePhoto()" disabled
                        style="background-color: rgb(218, 119, 86); border-radius: 15px;">
                        <i class="bi bi-check2 me-1"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-area:hover {
            border-color: rgb(218, 119, 86);
            background-color: #fff5f2;
        }

        .upload-area.dragover {
            border-color: rgb(218, 119, 86);
            background-color: #fff5f2;
            transform: scale(0.98);
        }

        .btn-tool {
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }

        .btn-tool:hover {
            background: #fff5f2;
            border-color: rgb(218, 119, 86);
            color: rgb(218, 119, 86);
        }

        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .avatar-item {
            aspect-ratio: 1;
            cursor: pointer;
            padding: 0.25rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .avatar-item:hover {
            transform: translateY(-2px);
            border-color: rgb(218, 119, 86);
        }

        .avatar-item.selected {
            border-color: rgb(218, 119, 86);
            background-color: #fff5f2;
        }

        .tab-btn {
            border: 1px solid grey;
            background: transparent;
            padding: 0.5rem;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            background-color: rgb(218, 119, 86);
            color: white;
        }

        .tab-btn.active {
            background-color: rgb(218, 119, 86);
            color: white;
        }

        .tab-indicator {
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: rgb(218, 119, 86);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .tab-btn.active .tab-indicator {
            opacity: 1;
        }

        /* Custom scrollbar for avatar grid */
        .avatar-grid::-webkit-scrollbar {
            width: 6px;
        }

        .avatar-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .avatar-grid::-webkit-scrollbar-thumb {
            background: rgb(218, 119, 86);
            border-radius: 10px;
        }

        .avatar-grid::-webkit-scrollbar-thumb:hover {
            background: rgb(219, 88, 44);
        }
    </style>

    <style>
        .upload-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
        }

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: rgb(218, 119, 86);
            background-color: #f1f3f5;
        }

        .upload-area.dragover {
            border-color: rgb(218, 119, 86);
            background-color: #e9ecef;
        }

        .btn-tool {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .btn-tool:hover {
            background: #e9ecef;
        }

        .avatar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 1rem;
            max-height: 400px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .avatar-item {
            cursor: pointer;
            padding: 0.5rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .avatar-item:hover {
            transform: translateY(-2px);
            border-color: #6c757d;
        }

        .avatar-item.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .avatar-item img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .nav-tabs .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
        }

        .nav-tabs .nav-link.active {
            font-weight: 500;
        }

        /* Cropper customization */
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
    </style>

    <!-- Modal untuk Info Identitas -->
    <div class="modal fade" id="identitasInfoModal" tabindex="-1" aria-labelledby="identitasInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-body p-4">
                    <h4 class="fw-bold mt-4 mb-3" style="font-size: 2rem;">Informasi Identitas Siswa</h4>
                    <p class="mb-4">Data identitas siswa dikelola oleh Wali Kelas, Guru BK, dan Tata Usaha. Semua data identitas siswa bersifat rahasia dan hanya digunakan untuk keperluan pendidikan.</p>
                    <div class="alert border bg-light mb-4" style="border-radius: 15px;">
                        <div class="d-flex">
                            <i class="bi bi-lightbulb fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                            <div>
                                <p class="p-0 m-0 fw-bold" style="font-size: 14px;">Perlu mengubah data?</p>
                                <p class="p-0 m-0 text-muted" style="font-size: 12px;">Silakan hubungi wali kelas atau guru BK jika ada data yang perlu diperbarui.</p>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn w-100" style="background-color: rgb(218, 119, 86); color: white; border-radius:15px;" data-bs-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal untuk progressif guidance -->
    <div class="modal fade" id="pgInfoModal" tabindex="-1" aria-labelledby="pgInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow">
                <div class="modal-body p-4">
                    <h4 class="fw-bold mt-4 mb-3" style="font-size: 2rem;">Progressive Guidance: Pendampingan Holistik Siswa</h4>
                    <p class="mb-4">Progressive Guidance merupakan program pendampingan yang dirancang untuk mendukung perkembangan siswa secara menyeluruh. Program ini mencakup bimbingan dalam berbagai aspek kehidupan siswa:</p>

                    <ul class="text-muted mb-4">
                        <li class="mb-2">
                            <strong>Pendampingan Ibadah</strong>
                            <p class="mb-1 small">Membantu siswa dalam memahami dan melaksanakan ibadah dengan benar serta menanamkan kesadaran spiritual dalam kehidupan sehari-hari.</p>
                        </li>

                        <li class="mb-2">
                            <strong>Pendampingan Karakter</strong>
                            <p class="mb-1 small">Membangun sikap disiplin, tanggung jawab, kejujuran, serta nilai-nilai moral lainnya untuk membentuk kepribadian yang baik.</p>
                        </li>

                        <li class="mb-2">
                            <strong>Pendampingan Sosial Kemasyarakatan</strong>
                            <p class="mb-1 small">Mendorong keterlibatan aktif dalam kehidupan sosial melalui kegiatan kemasyarakatan, kepedulian lingkungan, serta penguatan keterampilan komunikasi dan kerja sama.</p>
                        </li>

                        <li class="mb-2">
                            <strong>Pendampingan Pengembangan Diri (Kewirausahaan)</strong>
                            <p class="mb-1 small">Membekali siswa dengan keterampilan kewirausahaan, kreativitas, serta kemandirian dalam menciptakan peluang di masa depan.</p>
                        </li>

                        <li class="mb-2">
                            <strong>Pendampingan Belajar</strong>
                            <p class="mb-1 small">Mendukung proses pembelajaran dengan teknik yang efektif, membangun kebiasaan belajar yang baik, serta meningkatkan pemahaman akademik secara optimal.</p>
                        </li>
                    </ul>

                    <button type="button" class="btn w-100" style="background-color: rgb(218, 119, 86); color: white; border-radius:15px;" data-bs-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>





    <script>
        let cropper = null;
        let selectedAvatar = null;
        let currentMode = 'upload'; // 'upload' or 'avatar'

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            initializeUpload();
            initializeTabs();
            updateAvatars();
        });

        function initializeTabs() {
            const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', (e) => {
                    currentMode = e.target.id === 'upload-tab' ? 'upload' : 'avatar';
                    updateSaveButton();
                });
            });
        }

        function initializeUpload() {
            const uploadArea = document.getElementById('uploadArea');
            const photoInput = document.getElementById('photoInput');

            // Drag and drop
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                if (e.dataTransfer.files[0]) {
                    handleImageUpload(e.dataTransfer.files[0]);
                }
            });

            // Click upload
            uploadArea.addEventListener('click', () => photoInput.click());
            photoInput.addEventListener('change', (e) => {
                if (e.target.files[0]) {
                    handleImageUpload(e.target.files[0]);
                }
            });

            // Reset on modal hide
            const modal = document.getElementById('editProfileModal');
            modal.addEventListener('hidden.bs.modal', resetModal);
        }

        function handleImageUpload(file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!validTypes.includes(file.type)) {
                showToast('Error', 'Harap upload file gambar yang valid (JPG, PNG)', 'error');
                return;
            }

            if (file.size > maxSize) {
                showToast('Error', 'Ukuran file tidak boleh lebih dari 5MB', 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('uploadArea').style.display = 'none';
                document.querySelector('.cropper-container').style.display = 'block';

                const image = document.getElementById('cropperImage');
                image.src = e.target.result;

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 2,
                    dragMode: 'move',
                    autoCropArea: 1,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                });

                updateSaveButton();
            };
            reader.readAsDataURL(file);
        }

        function updateAvatars() {
            const grid = document.getElementById('avatarGrid');
            const style = document.getElementById('avatarStyle').value;

            // Show loading
            grid.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';

            // Generate avatars
            let avatarsHTML = '';
            for (let i = 0; i < 20; i++) {
                const seed = Math.random().toString(36).substring(7);
                const avatarUrl = `https://api.dicebear.com/7.x/${style}/svg?seed=${seed}`;

                avatarsHTML += `
            <div class="avatar-item" onclick="selectAvatar(this, '${avatarUrl}')">
                <img src="${avatarUrl}" alt="Avatar option ${i+1}">
            </div>
        `;
            }

            grid.innerHTML = avatarsHTML;
        }

        function selectAvatar(element, avatarUrl) {
            document.querySelectorAll('.avatar-item').forEach(item => {
                item.classList.remove('selected');
            });

            element.classList.add('selected');
            selectedAvatar = avatarUrl;
            updateSaveButton();
        }

        function updateSaveButton() {
            const saveButton = document.getElementById('saveButton');
            if (currentMode === 'upload') {
                saveButton.disabled = !cropper;
            } else {
                saveButton.disabled = !selectedAvatar;
            }
        }

        function saveProfilePhoto() {
            const saveButton = document.getElementById('saveButton');
            saveButton.disabled = true;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

            if (currentMode === 'upload') {
                // Save uploaded photo
                cropper.getCroppedCanvas().toBlob((blob) => {
                    const formData = new FormData();
                    formData.append('siswa_id', document.querySelector('input[name="siswa_id"]').value);
                    formData.append('photo_type', 'upload');
                    formData.append('photo', blob, 'profile.jpg');

                    sendToServer(formData);
                }, 'image/jpeg', 0.8);
            } else {
                // Save selected avatar
                const formData = new FormData();
                formData.append('siswa_id', document.querySelector('input[name="siswa_id"]').value);
                formData.append('photo_type', 'avatar');
                formData.append('avatar_url', selectedAvatar);

                sendToServer(formData);
            }
        }

        function sendToServer(formData) {
            fetch('update_foto_siswa.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Sukses', 'Foto profil berhasil diperbarui', 'success');
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Gagal memperbarui foto profil');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', error.message, 'error');
                    const saveButton = document.getElementById('saveButton');
                    saveButton.disabled = false;
                    saveButton.innerHTML = 'Simpan';
                });
        }

        function rotateImage(degree) {
            if (cropper) {
                cropper.rotate(degree);
            }
        }

        function resetCropper() {
            if (cropper) {
                cropper.reset();
            }
        }

        function resetModal() {
            // Reset cropper
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }

            // Reset upload
            document.getElementById('cropperImage').src = '';
            document.getElementById('photoInput').value = '';
            document.getElementById('uploadArea').style.display = 'block';
            document.querySelector('.cropper-container').style.display = 'none';

            // Reset avatar selection
            selectedAvatar = null;
            document.querySelectorAll('.avatar-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Reset tab to upload
            const uploadTab = document.getElementById('upload-tab');
            const tab = new bootstrap.Tab(uploadTab);
            tab.show();

            // Reset save button
            const saveButton = document.getElementById('saveButton');
            saveButton.disabled = true;
            saveButton.innerHTML = 'Simpan';
        }

        function showToast(title, message, type = 'info') {
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            const toastHtml = `
        <div class="toast align-items-center border-0 ${getToastClass(type)}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement, {
                animation: true,
                autohide: true,
                delay: 3000
            });
            toast.show();

            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }

        function getToastClass(type) {
            switch (type) {
                case 'success':
                    return 'bg-success text-white';
                case 'error':
                    return 'bg-danger text-white';
                case 'warning':
                    return 'bg-warning text-dark';
                default:
                    return 'bg-info text-white';
            }
        }
    </script>
</body>

</html>