<?php
session_start();
require "koneksi.php";

// Pastikan yang mengakses adalah guru
if(!isset($_SESSION['level']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Cek parameter username
if(!isset($_GET['username'])) {
    header("Location: cari.php");
    exit();
}

$username = mysqli_real_escape_string($koneksi, $_GET['username']);
$query = "SELECT * FROM siswa WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);
$siswa = mysqli_fetch_assoc($result);

// Jika siswa tidak ditemukan
if(!$siswa) {
    header("Location: cari.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil <?php echo htmlspecialchars($siswa['nama']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: merriweather;
            background-color: #f5f5f5;
        }
        .color-web {
            background-color: rgb(218, 119, 86);
        }
        .btn-back {
            text-decoration: none;
            color: #666;
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <a href="cari_guru.php" class="btn-back">← Kembali</a>
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow-sm">
                    <div class="card-body">
                    <?php if(isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                        <h3 class="text-center mb-4">Edit Profil Siswa</h3>
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="<?php echo $siswa['foto_profil'] ? $siswa['foto_profil'] : 'assets/pp-siswa.png'; ?>" 
                                    alt="Profile Picture" 
                                    class="rounded-circle" 
                                    style="width: 150px; height: 150px; object-fit: cover; border: 5px solid white;">
                                <button type="button" 
                                        class="btn btn-sm color-web text-white position-absolute bottom-0 end-0"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalGantiFoto">
                                    <img src="assets/kamera.png" alt="Edit" width="20px" style="filter: brightness(0) invert(1);">
                                </button>
                            </div>
                                <h4 class="mt-2"><?php echo htmlspecialchars($siswa['nama']); ?></h4>
                            </div>

                        <form action="update_siswa.php" method="POST">
                            <input type="hidden" name="username" value="<?php echo $siswa['username']; ?>">
                            
                            <!-- Basic Info Section -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Informasi Dasar</h5>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama" 
                                           value="<?php echo htmlspecialchars($siswa['nama']); ?>">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pendidikan Sebelumnya</label>
                                        <input type="text" class="form-control" name="pendidikan_sebelumnya" 
                                               value="<?php echo htmlspecialchars($siswa['pendidikan_sebelumnya']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kelas Saat Ini</label>
                                        <input type="text" class="form-control" name="kelas_saat_ini" 
                                               value="<?php echo htmlspecialchars($siswa['kelas_saat_ini']); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Info Section -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Informasi Akademik</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gaya Belajar</label>
                                        <select class="form-select" name="gaya_belajar">
                                            <option value="">Pilih Gaya Belajar</option>
                                            <option value="Visual" <?php echo $siswa['gaya_belajar'] == 'Visual' ? 'selected' : ''; ?>>Visual</option>
                                            <option value="Auditori" <?php echo $siswa['gaya_belajar'] == 'Auditori' ? 'selected' : ''; ?>>Auditori</option>
                                            <option value="Kinestetik" <?php echo $siswa['gaya_belajar'] == 'Kinestetik' ? 'selected' : ''; ?>>Kinestetik</option>
                                            <option value="Linguistik" <?php echo $siswa['gaya_belajar'] == 'Linguistik' ? 'selected' : ''; ?>>Linguistik</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Hasil IQ</label>
                                        <input type="number" class="form-control" name="hasil_iq" 
                                               value="<?php echo $siswa['hasil_iq']; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kemampuan Literasi</label>
                                        <select class="form-select" name="kemampuan_literasi">
                                            <option value="">Pilih Level</option>
                                            <option value="Baik" <?php echo $siswa['kemampuan_literasi'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                            <option value="Cukup" <?php echo $siswa['kemampuan_literasi'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                                            <option value="Kurang" <?php echo $siswa['kemampuan_literasi'] == 'Kurang' ? 'selected' : ''; ?>>Kurang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kemampuan Berhitung</label>
                                        <select class="form-select" name="kemampuan_berhitung">
                                            <option value="">Pilih Level</option>
                                            <option value="Baik" <?php echo $siswa['kemampuan_berhitung'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                            <option value="Cukup" <?php echo $siswa['kemampuan_berhitung'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                                            <option value="Kurang" <?php echo $siswa['kemampuan_berhitung'] == 'Kurang' ? 'selected' : ''; ?>>Kurang</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Info Section -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Informasi Pribadi</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Minat</label>
                                        <input type="text" class="form-control" name="minat" 
                                               value="<?php echo htmlspecialchars($siswa['minat']); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Hobi</label>
                                        <input type="text" class="form-control" name="hobi" 
                                               value="<?php echo htmlspecialchars($siswa['hobi']); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Health & Social Section -->
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Kesehatan & Sosial</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kesehatan Mental</label>
                                        <select class="form-select" name="kesehatan_mental">
                                            <option value="">Pilih Status</option>
                                            <option value="Baik" <?php echo $siswa['kesehatan_mental'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                            <option value="Cukup" <?php echo $siswa['kesehatan_mental'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                                            <option value="Kurang" <?php echo $siswa['kesehatan_mental'] == 'Kurang' ? 'selected' : ''; ?>>Kurang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Pengembangan Emosional</label>
                                        <select class="form-select" name="pengembangan_emosional">
                                            <option value="">Pilih Status</option>
                                            <option value="Baik" <?php echo $siswa['pengembangan_emosional'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                            <option value="Cukup" <?php echo $siswa['pengembangan_emosional'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                                            <option value="Kurang" <?php echo $siswa['pengembangan_emosional'] == 'Kurang' ? 'selected' : ''; ?>>Kurang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Penyakit Bawaan</label>
                                        <select class="form-select" name="penyakit_bawaan">
                                            <option value="Tidak Ada" <?php echo $siswa['penyakit_bawaan'] == 'Tidak Ada' ? 'selected' : ''; ?>>Tidak Ada</option>
                                            <option value="Ada" <?php echo $siswa['penyakit_bawaan'] == 'Ada' ? 'selected' : ''; ?>>Ada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Kehidupan Sosial</label>
                                        <select class="form-select" name="kehidupan_sosial">
                                            <option value="">Pilih Status</option>
                                            <option value="Baik" <?php echo $siswa['kehidupan_sosial'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                                            <option value="Kurang" <?php echo $siswa['kehidupan_sosial'] == 'Kurang' ? 'selected' : ''; ?>>Kurang</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn color-web text-white px-5">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ganti Foto -->
<div class="modal fade" id="modalGantiFoto" tabindex="-1" aria-labelledby="modalGantiFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGantiFotoLabel">Ganti Foto Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_foto_siswa.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="username" value="<?php echo $siswa['username']; ?>">
                    <div class="mb-3">
                        <label for="foto" class="form-label">Pilih Foto Baru</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                    </div>
                    <div id="preview" class="text-center mt-3" style="display: none;">
                        <img src="" alt="Preview" style="max-width: 200px; max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn color-web text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script untuk preview foto -->
<script>
document.getElementById('foto').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const file = e.target.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        preview.style.display = 'block';
        preview.querySelector('img').src = e.target.result;
    }

    if(file) {
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>