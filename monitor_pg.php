<?php
session_start();
require "koneksi.php";
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Ambil data guru
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);


// Get current selected semester and academic year from URL or set default
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : 1;
$selected_tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : date('Y') . '/' . (date('Y') + 1);

if (!isset($_GET['semester']) && !isset($_GET['tahun_ajaran'])) {
    // Default only if no GET parameters
    $current_month = date('n');
    $selected_semester = ($current_month >= 7 && $current_month <= 12) ? 1 : 2;
    $current_year = date('Y');
    $selected_tahun_ajaran = ($selected_semester == 1) ? $current_year . '/' . ($current_year + 1) : ($current_year - 1) . '/' . $current_year;
}

// Get student ID from URL if provided
$siswa_id = isset($_GET['siswa_id']) ? $_GET['siswa_id'] : null;

// Function to calculate average from aspect values
function calculateAverage($values)
{
    $validValues = array_filter($values, function ($value) {
        return $value !== null;
    });

    if (count($validValues) === 0) {
        return 0;
    }

    return array_sum($validValues) / count($validValues);
}

// First get all students
$query_all_students = "SELECT id, nama, tingkat FROM siswa ORDER BY nama ASC";
$result_all_students = mysqli_query($koneksi, $query_all_students);

$students_data = [];

// Initialize with empty data for all students
while ($student = mysqli_fetch_assoc($result_all_students)) {
    $students_data[$student['id']] = [
        'id' => $student['id'],
        'nama' => $student['nama'],
        'tingkat' => $student['tingkat'],
        'akademik' => 0,
        'ibadah' => 0,
        'pengembangan' => 0,
        'sosial' => 0,
        'kesehatan' => 0,
        'karakter' => 0,
        'overall' => 0
    ];
}

// Then get PG data and update only those students who have it
$query_pg_data = "SELECT pg.*, siswa.nama, siswa.tingkat 
                 FROM pg 
                 JOIN siswa ON pg.siswa_id = siswa.id 
                 WHERE pg.semester = ? AND pg.tahun_ajaran = ?";
$stmt = mysqli_prepare($koneksi, $query_pg_data);
mysqli_stmt_bind_param($stmt, "is", $selected_semester, $selected_tahun_ajaran);
mysqli_stmt_execute($stmt);
$result_pg = mysqli_stmt_get_result($stmt);

// Process data for charts and tables
$aspect_totals = [
    'akademik' => [],
    'ibadah' => [],
    'pengembangan' => [],
    'sosial' => [],
    'kesehatan' => [],
    'karakter' => []
];

while ($row = mysqli_fetch_assoc($result_pg)) {
    // Calculate averages for each aspect
    $akademik = calculateAverage([
        $row['nilai_akademik'],
        $row['keaktifan'],
        $row['pemahaman']
    ]);

    $ibadah = calculateAverage([
        $row['kehadiran_ibadah'],
        $row['kualitas_ibadah'],
        $row['pemahaman_agama']
    ]);

    $pengembangan = calculateAverage([
        $row['minat_bakat'],
        $row['prestasi'],
        $row['keaktifan_ekskul']
    ]);

    $sosial = calculateAverage([
        $row['partisipasi_sosial'],
        $row['empati'],
        $row['kerja_sama']
    ]);

    $kesehatan = calculateAverage([
        $row['kebersihan_diri'],
        $row['aktivitas_fisik'],
        $row['pola_makan']
    ]);

    $karakter = calculateAverage([
        $row['kejujuran'],
        $row['tanggung_jawab'],
        $row['kedisiplinan']
    ]);

    // Calculate overall average
    $overall_average = calculateAverage([
        $akademik,
        $ibadah,
        $pengembangan,
        $sosial,
        $kesehatan,
        $karakter
    ]);

    // Update student data
    if (isset($students_data[$row['siswa_id']])) {
        $students_data[$row['siswa_id']]['akademik'] = $akademik;
        $students_data[$row['siswa_id']]['ibadah'] = $ibadah;
        $students_data[$row['siswa_id']]['pengembangan'] = $pengembangan;
        $students_data[$row['siswa_id']]['sosial'] = $sosial;
        $students_data[$row['siswa_id']]['kesehatan'] = $kesehatan;
        $students_data[$row['siswa_id']]['karakter'] = $karakter;
        $students_data[$row['siswa_id']]['overall'] = $overall_average;
    }

    // Store aspect data for averaging
    $aspect_totals['akademik'][] = $akademik;
    $aspect_totals['ibadah'][] = $ibadah;
    $aspect_totals['pengembangan'][] = $pengembangan;
    $aspect_totals['sosial'][] = $sosial;
    $aspect_totals['kesehatan'][] = $kesehatan;
    $aspect_totals['karakter'][] = $karakter;
}

// Calculate average for each aspect across all students
$aspect_averages = [];
foreach ($aspect_totals as $aspect => $values) {
    $aspect_averages[$aspect] = count($values) > 0 ? array_sum($values) / count($values) : 0;
}

// Sort students by overall score for best and worst performers
$students_by_score = $students_data;
usort($students_by_score, function ($a, $b) {
    return $b['overall'] <=> $a['overall'];
});

// Get top 5 and bottom 5 students
$top_students = array_slice($students_by_score, 0, 5);
$bottom_students = array_slice(array_reverse($students_by_score), 0, 5);

// Get student details if a student ID is provided
$current_student = null;
if ($siswa_id && isset($students_data[$siswa_id])) {
    // Get detailed PG data for this student
    $query_student_pg = "SELECT * FROM pg WHERE siswa_id = ? AND semester = ? AND tahun_ajaran = ?";
    $stmt = mysqli_prepare($koneksi, $query_student_pg);
    mysqli_stmt_bind_param($stmt, "iis", $siswa_id, $selected_semester, $selected_tahun_ajaran);
    mysqli_stmt_execute($stmt);
    $student_pg = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Get student info
    $query_student = "SELECT * FROM siswa WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $query_student);
    mysqli_stmt_bind_param($stmt, "i", $siswa_id);
    mysqli_stmt_execute($stmt);
    $student_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $current_student = [
        'info' => $student_info,
        'pg' => $student_pg
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Progressive Guidance - SMAGAEdu</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <style>
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

        body {
            font-family: Merriweather, serif;
        }

        .col-utama {
            margin-left: 0;
            animation: fadeInUp 0.5s;
            opacity: 1;
        }

        @media (min-width: 768px) {
            .col-utama {
                margin-left: 13rem;
            }
        }

        .color-web {
            background-color: rgb(218, 119, 86);
            transition: background-color 0.3s ease;
        }

        .color-web:hover {
            background-color: rgb(206, 100, 65);
        }

        .card {
            border-radius: 15px;
        }


        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: rgb(206, 100, 65);
        }

        .stat-title {
            font-size: 1rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        .student-table {
            font-size: 0.9rem;
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }

        .dashboard-header {
            background-color: white;
            border-radius: 15px;
            margin-bottom: 1.5rem;
        }

        .ios-icon-bg {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(206, 100, 65, 0.1);
            border-radius: 12px;
            margin-right: 1rem;
        }

        .ios-icon-bg i {
            color: rgb(206, 100, 65);
            font-size: 1.5rem;
        }

        /* .aspect-card {
            padding: 1rem;
            border-radius: 15px;
            margin-bottom: 1rem;
            background-color: white;
            border: whitesmoke solid 1px;
        } */

        .aspect-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .aspect-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: rgb(206, 100, 65);
        }

        .btn-back {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 0.375rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .student-profile {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
        }

        .student-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .student-class {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }

        .progress {
            height: 0.8rem;
            border-radius: 50px;
            margin-bottom: 1rem;
        }

        .progress-bar {
            background-color: rgb(206, 100, 65);
        }

        .aspect-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .aspect-header span:first-child {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .aspect-header span:last-child {
            font-size: 0.9rem;
            font-weight: 700;
            color: rgb(206, 100, 65);
        }
    </style>

    <!-- darkmode -->
    <style>
        .darkmode--activated table,
        .darkmode--activated .table {
            color: #eee !important;
            border-color: #444 !important;
        }

        .darkmode--activated table th,
        .darkmode--activated table td {
            background-color: #333 !important;
            color: #eee !important;
            border-color: #444 !important;
        }

        .darkmode--activated .dataTables_wrapper .dataTables_length,
        .darkmode--activated .dataTables_wrapper .dataTables_filter,
        .darkmode--activated .dataTables_wrapper .dataTables_info,
        .darkmode--activated .dataTables_wrapper .dataTables_processing,
        .darkmode--activated .dataTables_wrapper .dataTables_paginate {
            color: #eee !important;
        }

        /* Tambahkan langsung di bawah styling tabel dan chart dari halaman monitor */
        @media (prefers-color-scheme: dark) {

            .card,
            .table,
            .progress {
                /* Styling untuk dark mode */
            }
        }
    </style>
</head>

<body>
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

    <!-- Main Content -->
    <div class="col p-4 col-utama mt-1 mt-md-0">
        <!-- Filter Header -->
        <div class="dashboard-header d-flex align-items-center shadow-none justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">Progressive Guidance Monitoring</h4>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form method="GET" class="d-flex align-items-center me-2">
                    <div class="me-2">
                        <select name="semester" class="btn btn-sm btn-light border px-3 py-2" style="border-radius: 15px;" onchange="this.form.submit()">
                            <option value="1" <?php echo ($selected_semester == 1) ? 'selected' : ''; ?>>Semester 1</option>
                            <option value="2" <?php echo ($selected_semester == 2) ? 'selected' : ''; ?>>Semester 2</option>
                        </select>
                    </div>
                    <div>
                        <select name="tahun_ajaran" class="btn btn-sm btn-light border px-3 py-2" style="border-radius: 15px;" onchange="this.form.submit()">
                            <i class="bi bi-calendar me-2"></i>
                            <?php
                            $current_year = date('Y');
                            for ($i = $current_year - 5; $i <= $current_year + 5; $i++) {
                                $tahun_option = $i . '/' . ($i + 1);
                                $selected = ($tahun_option == $selected_tahun_ajaran) ? 'selected' : '';
                                echo "<option value='$tahun_option' $selected>$tahun_option</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <?php if (isset($_GET['siswa_id'])): ?>
                        <input type="hidden" name="siswa_id" value="<?php echo $_GET['siswa_id']; ?>">
                    <?php endif; ?>
                </form>
                <button onclick="printContent()" class="btn btn-sm btn-light border px-3 py-2" style="border-radius: 15px;">
                    <i class="bi bi-printer me-2"></i>Cetak
                </button>
            </div>
        </div>

        <!-- Print-specific styles -->
        <style type="text/css" media="print">
            .sidebar,
            .offcanvas,
            .navbar,
            .dashboard-header,
            .btn-back,
            .btn-print,
            form,
            .footer,
            #sidebar,
            .mobile-nav,
            .settings-modal,
            header {
                display: none !important;
            }

            .col-utama {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            body {
                background-color: white;
            }

            @page {
                size: auto;
                margin: 15mm 10mm 15mm 10mm;
            }

            .dashboard-header h4 {
                font-size: 18pt;
                text-align: center;
                margin-bottom: 20px;
            }

            .card {
                break-inside: avoid;
            }

            .card-header {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .progress-bar {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: #da7756 !important;
            }
        </style>

        <script>
            function printContent() {
                window.print();
            }
        </script>

        <?php if ($siswa_id && $current_student): ?>
            <!-- Student Detail View -->
            <div class="mb-4">
                <div class="student-profile">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <img src="<?php
                                        if (!empty($current_student['info']['photo_type'])) {
                                            if ($current_student['info']['photo_type'] === 'avatar') {
                                                echo $current_student['info']['photo_url'];
                                            } else if ($current_student['info']['photo_type'] === 'upload') {
                                                echo 'uploads/profil/' . $current_student['info']['foto_profil'];
                                            }
                                        } else {
                                            echo 'assets/pp.png';
                                        }
                                        ?>"
                                class="rounded-circle border me-3"
                                width="70px" height="70px"
                                style="object-fit: cover;">
                            <div>
                                <h5 class="student-name"><?php echo htmlspecialchars($current_student['info']['nama']); ?></h5>
                                <p class="student-class mb-0">Kelas <?php echo htmlspecialchars($current_student['info']['tingkat']); ?></p>
                            </div>
                        </div>
                        <a href="?semester=<?php echo $selected_semester; ?>&tahun_ajaran=<?php echo $selected_tahun_ajaran; ?>" class="btn btn-back">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <canvas id="studentRadarChart" height="250"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="aspect-progress mb-3">
                                <div class="aspect-header">
                                    <span>Akademik</span>
                                    <span><?php echo number_format($students_data[$siswa_id]['akademik'], 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $students_data[$siswa_id]['akademik']; ?>%"></div>
                                </div>
                            </div>

                            <div class="aspect-progress mb-3">
                                <div class="aspect-header">
                                    <span>Ibadah</span>
                                    <span><?php echo number_format($students_data[$siswa_id]['ibadah'], 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $students_data[$siswa_id]['ibadah']; ?>%"></div>
                                </div>
                            </div>

                            <div class="aspect-progress mb-3">
                                <div class="aspect-header">
                                    <span>Pengembangan Diri</span>
                                    <span><?php echo number_format($students_data[$siswa_id]['pengembangan'], 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $students_data[$siswa_id]['pengembangan']; ?>%"></div>
                                </div>
                            </div>

                            <div class="aspect-progress mb-3">
                                <div class="aspect-header">
                                    <span>Sosial</span>
                                    <span><?php echo number_format($students_data[$siswa_id]['sosial'], 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $students_data[$siswa_id]['sosial']; ?>%"></div>
                                </div>
                            </div>

                            <div class="aspect-progress mb-3">
                                <div class="aspect-header">
                                    <span>Kesehatan</span>
                                    <span><?php echo number_format($students_data[$siswa_id]['kesehatan'], 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $students_data[$siswa_id]['kesehatan']; ?>%"></div>
                                </div>
                            </div>

                            <div class="aspect-progress mb-3">
                                <div class="aspect-header">
                                    <span>Karakter</span>
                                    <span><?php echo number_format($students_data[$siswa_id]['karakter'], 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $students_data[$siswa_id]['karakter']; ?>%"></div>
                                </div>
                            </div>

                            <div class="aspect-progress">
                                <div class="aspect-header">
                                    <span>Nilai Keseluruhan</span>
                                    <span><?php echo number_format($students_data[$siswa_id]['overall'], 1); ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $students_data[$siswa_id]['overall']; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="mb-3">Detail Nilai Per Komponen</h6>

                            <div class="row g-3">
                                <!-- Akademik -->
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-mortarboard-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Nilai Akademik</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['nilai_akademik'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-lightning-charge-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Keaktifan</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['keaktifan'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-book-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Pemahaman</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['pemahaman'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ibadah -->
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-heart-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Kehadiran Ibadah</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['kehadiran_ibadah'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-stars" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Kualitas Ibadah</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['kualitas_ibadah'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-globe" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Pemahaman Agama</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['pemahaman_agama'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pengembangan Diri -->
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-lightbulb-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Minat Bakat</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['minat_bakat'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-award-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Prestasi</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['prestasi'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-person-arms-up" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Keaktifan Ekskul</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['keaktifan_ekskul'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sosial -->
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-people-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Partisipasi Sosial</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['partisipasi_sosial'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-emoji-smile-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Empati</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['empati'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-hand-index-thumb-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Kerja Sama</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['kerja_sama'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kesehatan -->
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-droplet-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Kebersihan Diri</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['kebersihan_diri'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-bicycle" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Aktivitas Fisik</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['aktivitas_fisik'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-apple" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Pola Makan</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['pola_makan'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Karakter -->
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-patch-check-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Kejujuran</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['kejujuran'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-clipboard-check-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Tanggung Jawab</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['tanggung_jawab'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card rounded-4 border-1 h-100" style="position: relative; overflow: hidden;">
                                        <div style="position: absolute; right: -20px; bottom: -70px; opacity: 0.1;">
                                            <i class="bi bi-exclamation-octagon-fill" style="font-size: 140px; color: rgb(218, 119, 86);"></i>
                                        </div>
                                        <div class="card-body p-4">
                                            <div>
                                                <h6 class="mb-2">Kedisiplinan</h6>
                                                <h3 class="m-0 fw-bold">
                                                    <?php echo number_format($current_student['pg']['kedisiplinan'] ?? 0, 1); ?>
                                                    <span class="fs-6 fw-normal text-muted">%</span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Radar Chart for Student Performance
                    const radarCtx = document.getElementById('studentRadarChart').getContext('2d');
                    const radarChart = new Chart(radarCtx, {
                        type: 'radar',
                        data: {
                            labels: ['Akademik', 'Ibadah', 'Pengembangan', 'Sosial', 'Kesehatan', 'Karakter'],
                            datasets: [{
                                label: 'Nilai Siswa',
                                data: [
                                    <?php echo $students_data[$siswa_id]['akademik']; ?>,
                                    <?php echo $students_data[$siswa_id]['ibadah']; ?>,
                                    <?php echo $students_data[$siswa_id]['pengembangan']; ?>,
                                    <?php echo $students_data[$siswa_id]['sosial']; ?>,
                                    <?php echo $students_data[$siswa_id]['kesehatan']; ?>,
                                    <?php echo $students_data[$siswa_id]['karakter']; ?>
                                ],
                                backgroundColor: 'rgba(218, 119, 86, 0.2)',
                                borderColor: 'rgb(218, 119, 86)',
                                borderWidth: 2,
                                pointBackgroundColor: 'rgb(218, 119, 86)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgb(218, 119, 86)'
                            }, {
                                label: 'Rata-rata Kelas',
                                data: [
                                    <?php echo $aspect_averages['akademik']; ?>,
                                    <?php echo $aspect_averages['ibadah']; ?>,
                                    <?php echo $aspect_averages['pengembangan']; ?>,
                                    <?php echo $aspect_averages['sosial']; ?>,
                                    <?php echo $aspect_averages['kesehatan']; ?>,
                                    <?php echo $aspect_averages['karakter']; ?>
                                ],
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgb(54, 162, 235)',
                                borderWidth: 2,
                                pointBackgroundColor: 'rgb(54, 162, 235)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgb(54, 162, 235)'
                            }]
                        },
                        options: {
                            scales: {
                                r: {
                                    angleLines: {
                                        display: true
                                    },
                                    suggestedMin: 0,
                                    suggestedMax: 100
                                }
                            }
                        }
                    });
                </script>
            </div>
        <?php else: ?>
            <!-- Dashboard Overview -->
            <div class="row mb-4">
                <!-- Statistics Overview -->
                <div class="col-md-6 mb-4">
                    <div class="card  shadow-none  h-100">
                        <div class="card-header bg-white py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            <div class="d-flex align-items-center">
                                <div class="ios-icon-bg me-3">
                                    <i class="bi bi-bar-chart-line"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 small fw-bold">Rata-rata Aspek Penilaian</h5>
                                    <p class="text-muted small mb-0" style="font-size: 12px;">Menampilkan aspek dengan nilai rata-rata tertinggi dari semua siswa.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="aspectBarChart" height="150"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card  shadow-none  h-100">
                        <div class="card-header bg-white py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            <div class="d-flex align-items-center">
                                <div class="ios-icon-bg me-3">
                                    <i class="bi bi-eyeglasses"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 small fw-bold">Distribusi Nilai</h5>
                                    <p class="text-muted small mb-0" style="font-size: 12px;">Menunjukkan nilai siswa secara keseluruhan</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="distributionChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Top Performers -->
                <div class="col-md-6 mb-4">
                    <div class="card border shadow-none h-100">
                        <div class="card-header bg-white py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            <div class="d-flex align-items-center">
                                <div class="ios-icon-bg me-3">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 small fw-bold">Siswa dengan Performa Terbaik</h5>
                                    <p class="text-muted small mb-0" style="font-size: 12px;">Menampilkan siswa dengan nilai tertinggi secara keseluruhan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (count($top_students) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th class="text-center">Kelas</th>
                                                <th class="text-center">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($top_students as $student): ?>
                                                <tr>
                                                    <td><?php echo ucwords(htmlspecialchars($student['nama'])); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($student['tingkat']); ?></td>
                                                    <td class="text-center">
                                                        <div class="text-center">
                                                            <!-- <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $student['overall']; ?>%"></div>
                                                            </div> -->
                                                            <span class="fw-bold"><?php echo number_format($student['overall'], 1); ?>%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                                    <p class="mt-3">Belum ada data penilaian untuk periode ini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bottom Performers -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-none border h-100">
                        <div class="card-header bg-white py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            <div class="d-flex align-items-center">
                                <div class="ios-icon-bg me-3">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0 small fw-bold">Siswa yang Membutuhkan Perhatian</h5>
                                    <p class="text-muted small mb-0" style="font-size: 12px;">Menampilkan siswa dengan nilai terendah yang perlu perhatian khusus.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (count($bottom_students) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th class="text-center">Kelas</th>
                                                <th>Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($bottom_students as $student): ?>
                                                <tr>
                                                    <td><?php echo ucwords(htmlspecialchars($student['nama'])); ?></td>
                                                    <td class="text-center"><?php echo htmlspecialchars($student['tingkat']); ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <!-- <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $student['overall']; ?>%"></div>
                                                            </div> -->
                                                            <span class="fw-bold"><?php echo number_format($student['overall'], 1); ?>%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                                    <p class="mt-3">Belum ada data penilaian untuk periode ini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Students Data Table -->
            <div class="card border shadow-none mb-4">
                <div class="card-header bg-white py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <div class="d-flex align-items-center">
                        <div class="ios-icon-bg me-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0 small fw-bold">Data Seluruh Siswa</h5>
                            <p class="text-muted small mb-0" style="font-size: 12px;">Daftar lengkap siswa beserta nilai pada setiap aspek perkembangan.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($students_data) > 0): ?>
                        <div class="table-responsive">
                            <table id="studentsTable" class="table table-hover student-table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Kelas</th>
                                        <th>Akademik</th>
                                        <th>Ibadah</th>
                                        <th>Pengembangan</th>
                                        <th>Sosial</th>
                                        <th>Kesehatan</th>
                                        <th>Karakter</th>
                                        <th>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students_data as $student): ?>
                                        <tr>
                                            <td><?php echo ucwords(htmlspecialchars($student['nama'])); ?></td>
                                            <td><?php echo htmlspecialchars($student['tingkat']); ?></td>
                                            <td><?php echo number_format($student['akademik'], 1); ?>%</td>
                                            <td><?php echo number_format($student['ibadah'], 1); ?>%</td>
                                            <td><?php echo number_format($student['pengembangan'], 1); ?>%</td>
                                            <td><?php echo number_format($student['sosial'], 1); ?>%</td>
                                            <td><?php echo number_format($student['kesehatan'], 1); ?>%</td>
                                            <td><?php echo number_format($student['karakter'], 1); ?>%</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <!-- <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $student['overall']; ?>%"></div>
                                                    </div> -->
                                                    <span class="fw-bold"><?php echo number_format($student['overall'], 1); ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="?siswa_id=<?php echo $student['id']; ?>&semester=<?php echo $selected_semester; ?>&tahun_ajaran=<?php echo $selected_tahun_ajaran; ?>" class="btn btn-sm btn-light border px-3 py-2 d-flex" style="border-radius: 15px;font-size:12px">
                                                    <i class="bi bi-search me-2"></i>Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                            <p class="mt-3">Belum ada data penilaian untuk periode ini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <script>
                // Initialize DataTable with iOS-style UI
                $(document).ready(function() {
                    $('#studentsTable').DataTable({
                        responsive: true,
                        pageLength: 25, // Show 25 entries by default
                        lengthMenu: [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, "Semua"]
                        ], // Add option to show all entries
                        language: {
                            search: "Cari:",
                            lengthMenu: "_MENU_ Data",
                            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            infoEmpty: "Tidak ada data yang tersedia",
                            infoFiltered: "(disaring dari total _MAX_ data)",
                            zeroRecords: "Tidak ada data yang sesuai dengan pencarian",
                            paginate: {
                                first: "Pertama",
                                last: "Terakhir",
                                next: "Selanjutnya",
                                previous: "Sebelumnya"
                            }
                        },
                        order: [
                            [8, 'desc']
                        ], // Sort by total score column
                        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center justify-items-center"l><"d-flex"f>>rtip',
                        drawCallback: function() {
                            // iOS-style pagination buttons
                            $('.paginate_button').css({
                                'border-radius': '8px',
                                'margin': '0 2px',
                                'border': 'none',
                                'transition': 'all 0.2s ease'
                            });

                            // iOS-style for current pagination button
                            $('.paginate_button.current').css({
                                'background-color': 'rgb(218, 119, 86)',
                                'color': 'white',
                                'font-weight': '500',
                                'box-shadow': '0 2px 5px rgba(218, 119, 86, 0.3)'
                            });

                            // Update previous and next buttons with primary color
                            $('.paginate_button.previous, .paginate_button.next').css({
                                'color': 'rgb(218, 119, 86)',
                                'font-weight': '500'
                            });

                            // Update font size for info text (showing X of Y entries)
                            $('.dataTables_info').css({
                                'font-size': '12px'
                            });

                            // iOS-style search input
                            $('input[type="search"]').css({
                                'border-radius': '10px',
                                'border': '1px solid #dee2e6',
                                'padding': '8px 12px',
                                'background-color': '#f8f9fa',
                                'box-shadow': 'none',
                                'transition': 'all 0.3s ease'
                            });

                            // iOS-style select
                            $('.dataTables_length select').css({
                                'border-radius': '10px',
                                'border': '1px solid #dee2e6',
                                'padding': '6px 10px',
                                'background-color': '#f8f9fa',
                                'appearance': 'none',
                                'background-image': 'url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23464a4e\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e")',
                                'background-repeat': 'no-repeat',
                                'background-position': 'right 8px center',
                                'background-size': '12px',
                                'padding-right': '28px'
                            });

                            // Apply horizontal alignment
                            $('.dataTables_info, .dataTables_paginate').addClass('d-flex align-items-center mt-3');
                            $('.dataTables_paginate').addClass('justify-content-end');

                            // Add hover effect for pagination buttons
                            $('.paginate_button.previous, .paginate_button.next').hover(
                                function() {
                                    $(this).css({
                                        'background-color': 'rgba(218, 119, 86, 0.1)',
                                        'color': 'rgb(218, 119, 86)'
                                    });
                                },
                                function() {
                                    $(this).css({
                                        'background-color': 'transparent',
                                        'color': 'rgb(218, 119, 86)'
                                    });
                                }
                            );
                        },
                        initComplete: function() {
                            // Add iOS-style focus effect for search input
                            $('input[type="search"]').on('focus', function() {
                                $(this).css({
                                    'background-color': 'white',
                                    'border-color': 'rgb(218, 119, 86)',
                                    'box-shadow': '0 0 0 3px rgba(218, 119, 86, 0.15)'
                                });
                            }).on('blur', function() {
                                $(this).css({
                                    'background-color': '#f8f9fa',
                                    'border-color': '#dee2e6',
                                    'box-shadow': 'none'
                                });
                            });
                        }
                    });
                });

                // Aspect Bar Chart - Minimalist version
                const aspectCtx = document.getElementById('aspectBarChart').getContext('2d');
                const aspectBarChart = new Chart(aspectCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Akademik', 'Ibadah', 'Pengembangan', 'Sosial', 'Kesehatan', 'Karakter'],
                        datasets: [{
                            label: 'Rata-rata Nilai (%)',
                            data: [
                                <?php echo number_format($aspect_averages['akademik'], 1); ?>,
                                <?php echo number_format($aspect_averages['ibadah'], 1); ?>,
                                <?php echo number_format($aspect_averages['pengembangan'], 1); ?>,
                                <?php echo number_format($aspect_averages['sosial'], 1); ?>,
                                <?php echo number_format($aspect_averages['kesehatan'], 1); ?>,
                                <?php echo number_format($aspect_averages['karakter'], 1); ?>
                            ],
                            backgroundColor: 'rgba(218, 119, 86, 0.7)',
                            borderColor: 'rgba(218, 119, 86, 1)',
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    },
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#333',
                                bodyColor: '#333',
                                borderColor: 'rgba(218, 119, 86, 0.7)',
                                borderWidth: 1,
                                cornerRadius: 4,
                                displayColors: false
                            }
                        }
                    }
                });

                // Create distribution data
                const distributionData = [0, 0, 0, 0, 0]; // <60, 60-70, 70-80, 80-90, 90-100

                <?php
                // Count the distribution of scores
                foreach ($students_data as $student) {
                    $score = $student['overall'];
                    if ($score < 60) echo "distributionData[0]++;";
                    else if ($score < 70) echo "distributionData[1]++;";
                    else if ($score < 80) echo "distributionData[2]++;";
                    else if ($score < 90) echo "distributionData[3]++;";
                    else echo "distributionData[4]++;";
                }
                ?>

                // Distribution Chart
                const distributionCtx = document.getElementById('distributionChart').getContext('2d');
                const distributionChart = new Chart(distributionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['< 60%', '60-70%', '70-80%', '80-90%', '90-100%'],
                        datasets: [{
                            data: distributionData,
                            backgroundColor: [
                                'rgba(218, 119, 86, 0.95)',
                                'rgba(218, 119, 86, 0.8)',
                                'rgba(218, 119, 86, 0.65)',
                                'rgba(218, 119, 86, 0.5)',
                                'rgba(218, 119, 86, 0.35)'
                            ],
                            borderColor: [
                                'rgba(218, 119, 86, 1)',
                                'rgba(218, 119, 86, 1)',
                                'rgba(218, 119, 86, 1)',
                                'rgba(218, 119, 86, 1)',
                                'rgba(218, 119, 86, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 2, // Increase this value to make chart smaller
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        size: 11 // Smaller legend text
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} siswa (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>