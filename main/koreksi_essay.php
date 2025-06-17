<?php
session_start();
require "koneksi.php";

// Validasi parameter
if (!isset($_GET['ujian_id']) || !isset($_GET['siswa_id'])) {
    die("Parameter tidak lengkap");
}

$ujian_id = intval($_GET['ujian_id']);
$siswa_id = intval($_GET['siswa_id']);

// Ambil informasi ujian
$query_ujian = "SELECT * FROM ujian WHERE id = '$ujian_id'";
$result_ujian = mysqli_query($koneksi, $query_ujian);
$ujian = mysqli_fetch_assoc($result_ujian);

// Ambil informasi siswa
$query_siswa = "SELECT * FROM siswa WHERE id = '$siswa_id'";
$result_siswa = mysqli_query($koneksi, $query_siswa);
$siswa = mysqli_fetch_assoc($result_siswa);

// Ambil soal essay
$query_soal_essay = "SELECT * FROM bank_soal WHERE ujian_id = '$ujian_id' AND jenis_soal = 'uraian'";
$result_soal_essay = mysqli_query($koneksi, $query_soal_essay);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Koreksi Essay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .essay-card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .jawaban-siswa {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-white pb-0">
                    <h3 class="card-title">Koreksi Essay</h3>
                    <p class="text-muted">
                        Ujian: <?php echo htmlspecialchars($ujian['judul']); ?> | 
                        Siswa: <?php echo htmlspecialchars($siswa['nama']); ?>
                    </p>
                </div>
                <div class="card-body">
                    <form action="simpan_koreksi_essay.php" method="POST">
                        <input type="hidden" name="ujian_id" value="<?php echo $ujian_id; ?>">
                        <input type="hidden" name="siswa_id" value="<?php echo $siswa_id; ?>">

                        <?php while($soal = mysqli_fetch_assoc($result_soal_essay)): ?>
                        <div class="card essay-card">
                            <div class="card-body">
                                <h5 class="card-title">Soal</h5>
                                <p><?php echo htmlspecialchars($soal['pertanyaan']); ?></p>
                                
                                <div class="jawaban-siswa mb-3">
                                    <h6>Jawaban Siswa</h6>
                                    <textarea class="form-control" rows="4" readonly>Contoh jawaban siswa</textarea>
                                </div>

                                <div class="scoring">
                                    <label class="form-label">Nilai</label>
                                    <input type="number" 
                                           name="nilai_soal[<?php echo $soal['id']; ?>]" 
                                           class="form-control" 
                                           min="0" 
                                           max="100" 
                                           required>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Simpan Koreksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>