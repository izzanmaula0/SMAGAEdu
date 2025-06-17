<?php
session_start();
require "koneksi.php";

// Cek apakah user yang login adalah kepala sekolah
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Cek apakah parameter username ada
if (!isset($_GET['username'])) {
    header("Location: guru_admin.php");
    exit();
}

$username = mysqli_real_escape_string($koneksi, $_GET['username']);

// Ambil data guru
$query = "SELECT * FROM guru WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Jika guru tidak ditemukan
if (!$guru) {
    header("Location: guru_admin.php");
    exit();
}

// Jika form update disubmit
if (isset($_POST['update_guru'])) {
    $namaLengkap = mysqli_real_escape_string($koneksi, $_POST['namaLengkap']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);

    // Update password jika diisi
    $password_update = "";
    if (!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($koneksi, $_POST['password']);
        $password_update = ", password = '$password'";
    }

    $update_query = "UPDATE guru SET 
                     namaLengkap = '$namaLengkap',
                     jabatan = '$jabatan'
                     $password_update
                     WHERE username = '$username'";

    if (mysqli_query($koneksi, $update_query)) {
        $success = "Data guru berhasil diperbarui!";

        // Refresh data guru
        $result = mysqli_query($koneksi, $query);
        $guru = mysqli_fetch_assoc($result);
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}

// Jika form informasi tambahan disubmit
if (isset($_POST['update_info'])) {
    // Ambil data dari form
    $pendidikan_s1 = mysqli_real_escape_string($koneksi, $_POST['pendidikan_s1']);
    $pendidikan_s2 = mysqli_real_escape_string($koneksi, $_POST['pendidikan_s2']);
    $pendidikan_lainnya = mysqli_real_escape_string($koneksi, $_POST['pendidikan_lainnya']);

    $sertifikasi1 = mysqli_real_escape_string($koneksi, $_POST['sertifikasi1']);
    $sertifikasi2 = mysqli_real_escape_string($koneksi, $_POST['sertifikasi2']);
    $sertifikasi3 = mysqli_real_escape_string($koneksi, $_POST['sertifikasi3']);

    $publikasi1 = mysqli_real_escape_string($koneksi, $_POST['publikasi1']);
    $publikasi2 = mysqli_real_escape_string($koneksi, $_POST['publikasi2']);
    $publikasi3 = mysqli_real_escape_string($koneksi, $_POST['publikasi3']);

    $proyek1 = mysqli_real_escape_string($koneksi, $_POST['proyek1']);
    $proyek2 = mysqli_real_escape_string($koneksi, $_POST['proyek2']);
    $proyek3 = mysqli_real_escape_string($koneksi, $_POST['proyek3']);

    // Update query
    $update_info_query = "UPDATE guru SET 
                    pendidikan_s1 = '$pendidikan_s1',
                    pendidikan_s2 = '$pendidikan_s2',
                    pendidikan_lainnya = '$pendidikan_lainnya',
                    sertifikasi1 = '$sertifikasi1',
                    sertifikasi2 = '$sertifikasi2',
                    sertifikasi3 = '$sertifikasi3',
                    publikasi1 = '$publikasi1',
                    publikasi2 = '$publikasi2',
                    publikasi3 = '$publikasi3',
                    proyek1 = '$proyek1',
                    proyek2 = '$proyek2',
                    proyek3 = '$proyek3'
                    WHERE username = '$username'";

    if (mysqli_query($koneksi, $update_info_query)) {
        $success = "Informasi tambahan berhasil diperbarui!";

        // Refresh data guru
        $result = mysqli_query($koneksi, $query);
        $guru = mysqli_fetch_assoc($result);
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}

// Ambil data kelas yang dimiliki guru
$query_kelas = "SELECT * FROM kelas WHERE guru_id = '$username'";
$result_kelas = mysqli_query($koneksi, $query_kelas);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Edit Guru - SMAGAEdu</title>
    <style>
        body {
            font-family: 'Merriweather', serif;
        }

        .color-web {
            background-color: rgb(218, 119, 86);
        }

        .btn-primary {
            background-color: rgb(218, 119, 86);
            border-color: rgb(218, 119, 86);
        }

        .btn-primary:hover {
            background-color: rgb(190, 100, 70);
            border-color: rgb(190, 100, 70);
        }

        .profile-img {
            width: 150px;
            height: 150px;
            position: relative;
            top: -4rem;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-bg {
            height: 200px;
            background-position: center;
            background-size: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            position: relative;
        }

        .profile-bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

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

        .img-container {
            max-height: 400px;
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
        </div>
    </div>

    <!-- animasi modal -->
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


    <!-- Main Content -->
    <div class="col col-inti p-0 p-md-3">
        <style>
            .col-inti {
                margin-left: 0;
                padding: 1rem;
                max-width: 100%;
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

            @media (min-width: 768px) {
                .col-inti {
                    margin-left: 13rem;
                    margin-top: 0;
                    padding: 2rem;
                }
            }

            @media screen and (max-width: 768px) {
                .col-inti {
                    margin-left: 0.5rem;
                    margin-right: 0.5rem;
                    padding: 1rem;
                }
            }
        </style>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Edit Guru</h2>
                <a href="guru_admin.php" class="btn btn-white border" style="border-radius: 15px;">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger animate-fade-in">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success animate-fade-in">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Profil dan Foto -->
                <div class="col-md-4 mb-4">
                    <div class="card animate-fade-in border shadow-none" style="border-top-left-radius: 20px;border-top-right-radius:20px; overflow: hidden;">
                        <div class="profile-bg" style="background-image: url('<?php echo !empty($guru['foto_latarbelakang']) ? 'uploads/background/' . $guru['foto_latarbelakang'] : 'assets/bg-profil.png'; ?>'); height: 180px;">
                            <div class="profile-bg-overlay" style="background: rgba(0,0,0,0.3);"></div>
                        </div>
                        <div class="card-body text-center pt-0" style="padding-bottom: 1.5rem;">
                            <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                alt="<?php echo htmlspecialchars($guru['namaLengkap']); ?>"
                                class="profile-img mt-n53">
                            <h4 style="font-weight: 600;"><?php echo htmlspecialchars($guru['namaLengkap']); ?></h4>
                            <p class="text-muted" style="font-size: 0.95rem;"><?php echo htmlspecialchars($guru['jabatan'] ?: 'Belum diatur'); ?></p>

                            <div class="d-flex justify-content-center mt-3 gap-2">
                                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editFotoModal" style="border-radius: 15px; padding: 0.5rem 1rem;">
                                    <i class="bi bi-camera"></i> Ubah Foto
                                </button>
                                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editBgModal" style="border-radius: 15px; padding: 0.5rem 1rem;">
                                    <i class="bi bi-image"></i> Ubah Background
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info Kelas -->
                    <div class="card mt-4 animate-fade-in border shadow-none" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header bg-white d-flex align-items-center" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1.25rem;">
                            <div class="me-2" style="width: 32px; height: 32px; background-color: rgba(218,119,86,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-journal-bookmark-fill" style="color: rgb(218,119,86); font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1" style="font-weight: 700; font-size: 1.05rem;">Kelas yang Dikelola</h5>
                                <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.2;">Daftar kelas yang dikelola oleh guru</p>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 1.25rem;">
                            <?php if (mysqli_num_rows($result_kelas) > 0): ?>
                                <div class="row g-3">
                                    <?php while ($kelas = mysqli_fetch_assoc($result_kelas)):
                                        // Hitung jumlah siswa di kelas
                                        $kelas_id = $kelas['id'];
                                        $query_siswa = "SELECT COUNT(*) as total_siswa FROM kelas_siswa WHERE kelas_id = '$kelas_id'";
                                        $result_siswa = mysqli_query($koneksi, $query_siswa);
                                        $siswa_count = mysqli_fetch_assoc($result_siswa);
                                        $total_siswa = $siswa_count['total_siswa'];

                                        // Tentukan background fallback berdasarkan mata pelajaran jika tidak ada background_image
                                        $bg_colors = [
                                            'Matematika' => 'linear-gradient(135deg, #3498db, #2980b9)',
                                            'Bahasa Indonesia' => 'linear-gradient(135deg, #e74c3c, #c0392b)',
                                            'Bahasa Inggris' => 'linear-gradient(135deg, #2ecc71, #27ae60)',
                                            'IPA' => 'linear-gradient(135deg, #9b59b6, #8e44ad)',
                                            'IPS' => 'linear-gradient(135deg, #f1c40f, #f39c12)',
                                            'Fisika' => 'linear-gradient(135deg, #1abc9c, #16a085)',
                                            'Biologi' => 'linear-gradient(135deg, #2ecc71, #27ae60)',
                                            'Kimia' => 'linear-gradient(135deg, #e67e22, #d35400)',
                                            'Sejarah' => 'linear-gradient(135deg, #95a5a6, #7f8c8d)',
                                        ];

                                        $default_bg = 'linear-gradient(135deg, #da7756, #c05f3e)';
                                        $bg_style = isset($bg_colors[$kelas['mata_pelajaran']]) ? $bg_colors[$kelas['mata_pelajaran']] : $default_bg;

                                        // Gunakan background image jika tersedia
                                        $bg_image = '';
                                        if (!empty($kelas['background_image'])) {
                                            $bg_image = 'background-image: url(\'uploads/kelas/' . $kelas['background_image'] . '\'); background-size: cover; background-position: center;';
                                        } else {
                                            $bg_image = 'background: ' . $bg_style . ';';
                                        }
                                    ?>
                                        <div class="col-12">
                                            <div class="card border shadow-none mb-3" style="border-radius: 10px; overflow: hidden;">
                                                <div class="card-header p-0 border-0" style="height: 60px; <?php echo $bg_image; ?>">
                                                    <div class="p-3 position-relative" style="height: 100%; display: flex; align-items: center; background: rgba(0,0,0,0.3);">
                                                        <h6 class="mb-0 text-white" style="font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                                <i class="bi bi-book"></i> Mata Pelajaran
                                                            </span>
                                                            <div class="fw-bold" style="font-size: 0.85rem;">
                                                                <?php echo htmlspecialchars($kelas['mata_pelajaran']); ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                                <i class="bi bi-people"></i> Total Siswa
                                                            </span>
                                                            <div class="fw-bold" style="font-size: 0.85rem;">
                                                                <?php echo $total_siswa; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-grid mt-2">
                                                        <a href="kelas_guru.php?id=<?php echo $kelas['id']; ?>" class="btn bg-light" style="font-size: 0.75rem; border-radius:10px;">
                                                            <i class=" "></i> Lihat Kelas
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4" style="color: #8E8E93;">
                                    <div style="background-color: #F2F2F7; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                        <i class="bi bi-book" style="font-size: 1.5rem; color: #8E8E93;"></i>
                                    </div>
                                    <p style="font-size: 0.95rem; font-weight: 500;">Belum ada kelas yang dikelola</p>
                                    <p style="font-size: 0.85rem;">Kelas yang diajar akan muncul di sini</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form Edit Data -->
                <div class="col-md-8">
                    <div class="card shadow-none border animate-fade-in">
                        <div class="card-header bg-white d-flex align-items-center" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1.25rem; border-top-left-radius: 15px; border-top-right-radius:15px;">
                            <div class="me-2" style="width: 32px; height: 32px; background-color: rgba(218,119,86,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-vcard" style="color: rgb(218,119,86); font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1" style="font-weight: 700; font-size: 1.05rem;">Edit Data Guru</h5>
                                <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.2;">Ubah informasi profil guru</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="mb-4">
                                    <label for="username" class="form-label small">Username</label>
                                    <div class="bg-light p-3 rounded-3 position-relative"
                                        id="username-container"
                                        style="cursor: not-allowed;">
                                        <input type="text" class="form-control-plaintext p-0 m-0 border-0 bg-transparent" style="cursor: not-allowed;" id="username" value="<?php echo htmlspecialchars($guru['username']); ?>" readonly>
                                    </div>
                                    <div class="form-text" style="font-size: 12px;">Username tidak dapat diubah, Anda dapat mengubah melalui database.</div>
                                </div>

                                <!-- iOS Style Modal for Username -->
                                <div class="modal fade" id="usernameModal" tabindex="-1" aria-labelledby="usernameModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 320px;">
                                        <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
                                            <div class="modal-body text-center p-4">
                                                <div class="mb-3">
                                                    <div style="width: 50px; height: 50px; background-color: #ff3b30; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                                        <i class="bi bi-x-lg text-white fs-4"></i>
                                                    </div>
                                                </div>
                                                <h5 class="mb-2" style="font-weight: 600;">Perubahan Ditolak</h5>
                                                <p class="text-muted mb-0" style="font-size: 0.9rem;">Username tidak dapat diubah melalui halaman ini, silahkan hubungi administrator database.</p>
                                            </div>
                                            <div class="modal-footer d-flex justify-content-center p-0" style="border-top: 1px solid #dee2e6;">
                                                <button type="button" class="btn btn-link text-primary w-100 p-3 m-0" data-bs-dismiss="modal" style="font-weight: 600; text-decoration: none;">Mengerti</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.getElementById('username-container').addEventListener('click', function() {
                                        var usernameModal = new bootstrap.Modal(document.getElementById('usernameModal'));
                                    });
                                </script>

                                <div class="mb-4">
                                    <label for="password" class="form-label small">Password Baru</label>
                                    <input type="password" class="form-control form-control-lg border-0 bg-light" style="border-radius:10px" id="password" name="password" placeholder="Masukkan password baru">
                                    <div class="form-text" style="font-size: 12px;">Biarkan kosong jika tidak ingin mengubah password.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="namaLengkap" class="form-label small">Nama Lengkap</label>
                                    <input type="text" class="form-control form-control-lg border-0 bg-light" style="border-radius:10px" id="namaLengkap" name="namaLengkap" value="<?php echo htmlspecialchars($guru['namaLengkap']); ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label for="jabatan" class="form-label small">Jabatan</label>
                                    <select class="form-select form-select-lg border-0 bg-light" style="border-radius:10px" id="jabatan" name="jabatan">
                                        <option value="">Pilih Jabatan</option>
                                        <?php
                                        $jabatan_options = [
                                            'Kepala Sekolah',
                                            'WAKA Kurikulum',
                                            'WAKA Kesiswaan',
                                            'Koordinator Keagamaan',
                                            'Koordinator Progresive Cultural School',
                                            'Pengelola Lab Sekolah',
                                            'Bendahara',
                                            'Bag. Kurikulum',
                                            'Bag. Kesiswaan',
                                            'Kepala Tata Usaha',
                                            'Wali Kelas',
                                            'Staf TU',
                                            'Operator',
                                            'Guru Mapel',
                                            'Guru Bimbingan Konseling'
                                        ];
                                        foreach ($jabatan_options as $option) {
                                            $selected = ($guru['jabatan'] == $option) ? 'selected' : '';
                                            echo "<option value=\"$option\" $selected>$option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="d-grid gap-2 mt-5">
                                    <button type="submit" name="update_guru" class="btn btn-primary btn-lg" style="border-radius:15px">
                                        <i class=""></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="card mt-4 animate-fade-in border shadow-none" style="border-radius: 15px;">
                        <div class="card-header bg-white d-flex align-items-center" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1.25rem; border-top-left-radius: 15px; border-top-right-radius:15px;">
                            <div class="me-2" style="width: 32px; height: 32px; background-color: rgba(218,119,86,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-info-circle" style="color: rgb(218,119,86); font-size: 1.1rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1" style="font-weight: 700; font-size: 1.05rem;">Informasi Tambahan</h5>
                                <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.2;">Lengkapi informasi riwayat pendidikan dan prestasi</p>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 1.25rem;">
                            <form action="" method="POST">
                                <input type="hidden" name="username" value="<?php echo $username; ?>">

                                <!-- Riwayat Pendidikan -->
                                <div class="mb-4">
                                    <label class="form-label text-secondary" style="font-size: 0.85rem; font-weight: 600;">RIWAYAT PENDIDIKAN</label>

                                    <div class="mb-3">
                                        <label for="pendidikan_s1" class="form-label text-muted small">Pendidikan S1</label>
                                        <input type="text" class="form-control form-control-lg border-0 bg-light" style="border-radius:10px" id="pendidikan_s1" name="pendidikan_s1" value="<?php echo htmlspecialchars($guru['pendidikan_s1']); ?>" placeholder="Masukkan pendidikan S1">
                                    </div>

                                    <div class="mb-3">
                                        <label for="pendidikan_s2" class="form-label text-muted small">Pendidikan S2</label>
                                        <input type="text" class="form-control form-control-lg border-0 bg-light" style="border-radius:10px" id="pendidikan_s2" name="pendidikan_s2" value="<?php echo htmlspecialchars($guru['pendidikan_s2']); ?>" placeholder="Masukkan pendidikan S2">
                                    </div>

                                    <div class="mb-3">
                                        <label for="pendidikan_lainnya" class="form-label text-muted small">Pendidikan Lainnya</label>
                                        <input type="text" class="form-control form-control-lg border-0 bg-light" style="border-radius:10px" id="pendidikan_lainnya" name="pendidikan_lainnya" value="<?php echo htmlspecialchars($guru['pendidikan_lainnya']); ?>" placeholder="Masukkan pendidikan lainnya">
                                    </div>
                                </div>

                                <!-- Sertifikasi -->
                                <div class="mb-4">
                                    <label class="form-label text-secondary" style="font-size: 0.85rem; font-weight: 600;">SERTIFIKASI</label>
                                    <div class="bg-light p-3" style="border-radius:15px;">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white mb-2" style="border-radius:10px" name="sertifikasi1" placeholder="Sertifikasi 1" value="<?php echo htmlspecialchars($guru['sertifikasi1']); ?>">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white mb-2" style="border-radius:10px" name="sertifikasi2" placeholder="Sertifikasi 2" value="<?php echo htmlspecialchars($guru['sertifikasi2']); ?>">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white" style="border-radius:10px" name="sertifikasi3" placeholder="Sertifikasi 3" value="<?php echo htmlspecialchars($guru['sertifikasi3']); ?>">
                                    </div>
                                </div>

                                <!-- Publikasi -->
                                <div class="mb-4">
                                    <label class="form-label text-secondary" style="font-size: 0.85rem; font-weight: 600;">PUBLIKASI</label>
                                    <div class="bg-light p-3" style="border-radius:15px;">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white mb-2" style="border-radius:10px" name="publikasi1" placeholder="Publikasi 1" value="<?php echo htmlspecialchars($guru['publikasi1']); ?>">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white mb-2" style="border-radius:10px" name="publikasi2" placeholder="Publikasi 2" value="<?php echo htmlspecialchars($guru['publikasi2']); ?>">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white" style="border-radius:10px" name="publikasi3" placeholder="Publikasi 3" value="<?php echo htmlspecialchars($guru['publikasi3']); ?>">
                                    </div>
                                </div>

                                <!-- Proyek -->
                                <div class="mb-4">
                                    <label class="form-label text-secondary" style="font-size: 0.85rem; font-weight: 600;">PROYEK</label>
                                    <div class="bg-light p-3" style="border-radius:15px;">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white mb-2" style="border-radius:10px" name="proyek1" placeholder="Proyek 1" value="<?php echo htmlspecialchars($guru['proyek1']); ?>">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white mb-2" style="border-radius:10px" name="proyek2" placeholder="Proyek 2" value="<?php echo htmlspecialchars($guru['proyek2']); ?>">
                                        <input type="text" class="form-control form-control-lg border-0 bg-white" style="border-radius:10px" name="proyek3" placeholder="Proyek 3" value="<?php echo htmlspecialchars($guru['proyek3']); ?>">
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" name="update_info" class="btn btn-primary btn-lg" style="border-radius:15px">
                                        <i class=""></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Foto Profil -->
    <div class="modal fade" id="editFotoModal" tabindex="-1" aria-labelledby="editFotoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFotoModalLabel">Ubah Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="file" class="form-control mb-3" id="input-foto-profil" accept="image/*">

                    <div id="foto-container" class="d-none">
                        <div class="img-container rounded overflow-hidden mb-3">
                            <img id="crop-image-profil" src="" alt="Gambar untuk di-crop" style="max-width: 100%;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="crop-submit-profil" class="btn btn-primary d-none">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Foto Background -->
    <div class="modal fade" id="editBgModal" tabindex="-1" aria-labelledby="editBgModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBgModalLabel">Ubah Foto Background</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="file" class="form-control mb-3" id="input-foto-bg" accept="image/*">

                    <div id="bg-container" class="d-none">
                        <div class="img-container rounded overflow-hidden mb-3">
                            <img id="crop-image-bg" src="" alt="Gambar untuk di-crop" style="max-width: 100%;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="crop-submit-bg" class="btn btn-primary d-none">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Form tersembunyi untuk submit hasil crop -->
    <form id="crop-form" action="update_foto_guru.php" method="POST" class="d-none">
        <input type="hidden" name="username" value="<?php echo $username; ?>">
        <input type="hidden" name="cropped_image" id="cropped-image-input">
        <input type="hidden" name="image_type" id="image-type-input">
    </form>

    <script>
        let cropper = null;
        let currentImageType = null;

        // Fungsi untuk menangani file yang dipilih untuk foto profil
        document.getElementById('input-foto-profil').addEventListener('change', function(e) {
            handleImageSelect(e, 'profil', 'crop-image-profil', 'foto-container', 'crop-submit-profil');
        });

        // Fungsi untuk menangani file yang dipilih untuk foto background
        document.getElementById('input-foto-bg').addEventListener('change', function(e) {
            handleImageSelect(e, 'latar', 'crop-image-bg', 'bg-container', 'crop-submit-bg');
        });

        // Fungsi umum untuk menangani image select
        function handleImageSelect(e, imageType, imageId, containerId, submitBtnId) {
            const file = e.target.files[0];
            if (file) {
                currentImageType = imageType;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const cropImage = document.getElementById(imageId);
                    cropImage.src = e.target.result;

                    // Tampilkan container cropper
                    document.getElementById(containerId).classList.remove('d-none');
                    document.getElementById(submitBtnId).classList.remove('d-none');

                    // Inisialisasi cropper
                    if (cropper) {
                        cropper.destroy();
                    }

                    // Konfigurasi berbeda untuk profil dan latar belakang
                    const cropperOptions = imageType === 'profil' ? {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        cropBoxResizable: true,
                        minCropBoxWidth: 200,
                        minCropBoxHeight: 200
                    } : {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        dragMode: 'move'
                    };

                    cropper = new Cropper(cropImage, cropperOptions);
                };
                reader.readAsDataURL(file);
            }
        }

        // Event listener untuk tombol simpan foto profil
        document.getElementById('crop-submit-profil').addEventListener('click', function() {
            submitCroppedImage();
        });

        // Event listener untuk tombol simpan foto background
        document.getElementById('crop-submit-bg').addEventListener('click', function() {
            submitCroppedImage();
        });

        // Fungsi untuk submit cropped image
        function submitCroppedImage() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 2048,
                    maxHeight: 2048,
                    fillColor: '#fff'
                });

                const croppedImageData = canvas.toDataURL('image/jpeg');
                document.getElementById('cropped-image-input').value = croppedImageData;
                document.getElementById('image-type-input').value = currentImageType;

                // Submit form
                document.getElementById('crop-form').submit();
            }
        }

        // Auto-hide alerts after 5 seconds
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 1s';
                    setTimeout(() => {
                        alert.remove();
                    }, 1000);
                });
            }, 5000);
        });
    </script>
</body>

</html>