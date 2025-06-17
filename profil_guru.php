<?php
session_start();
require "koneksi.php";


if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'guru') {
    header("Location: index.php");
    exit();
}

// Cek apakah ada parameter username
if (isset($_GET['username'])) {
    $username = mysqli_real_escape_string($koneksi, $_GET['username']);
} else {
    // Jika tidak ada parameter, gunakan username dari session
    $username = $_SESSION['userid'];
}

// Ambil data guru berdasarkan username
$query = "SELECT * FROM guru WHERE username = '$username'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);

// Jika guru tidak ditemukan, redirect ke halaman sebelumnya
if (!$guru) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

    <!-- darkmode -->
    <script>
        (function() {
            // Periksa localStorage untuk status dark mode
            if (localStorage.getItem('darkmode') === 'true') {
                // Jika dark mode aktif, segera tambahkan class
                document.documentElement.classList.add('darkmode-preload');
            }
        })();
    </script>
    <?php include 'darkmode_preload.php'; ?>   
    <title>Profil - SMAGAEdu</title>
</head>
<style>
    body {
        font-family: merriweather;
    }

    .color-web {
        background-color: rgb(218, 119, 86);
    }

    .btn {
        transition: background-color 0.3s ease;
        border: 0;
        border-radius: 5px;
    }

    .btn:hover {
        background-color: rgb(219, 106, 68);
        color: white;
    }

    .btn label:active {
        transform: scale(0.98);
        opacity: 0.9;
    }

    .btn label:hover {
        background: #eaeaeb !important;
    }

    #cancel-crop:hover {
        background: #ffebea !important;
    }

    #crop-submit:hover {
        opacity: 0.9;
    }

    .btn:focus,
    .btn:active {
        box-shadow: none !important;
    }

    .modal-dialog-scrollable .modal-content {
        max-height: 90vh;
        /* Tinggi maksimum 90% dari viewport height */
        overflow-y: auto;
        /* Memastikan scroll berfungsi */
    }

    .modal-dialog-scrollable {
        height: 90vh;
        /* Tinggi dialog 90% dari viewport height */
        margin-top: auto;
        margin-bottom: auto;
    }
</style>

<!-- animasi modal -->
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

    <!-- ini isi kontennya -->
    <div class="col col-inti p-0 p-md-3 pb-md-5">
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
        <div style="
                        background-image: url('<?php echo !empty($guru['foto_latarbelakang']) ? 'uploads/background/' . $guru['foto_latarbelakang'] : 'assets/bg-profil.png'; ?>'); 
                        height: 300px; 
                        padding-top: 200px; 
                        margin-top: 15px; 
                        background-position: center; 
                        background-size: cover;
                        position: relative;"
            class="rounded text-white shadow-lg latar-belakang section animate-fade-in">
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 0;" class="rounded"></div>
            <div class="ps-3" style="position: relative; z-index: 2;"></div>
        </div>
        <style>
            @media (max-width: 768px) {
                .latar-belakang {
                    height: 200px;
                    padding-top: 150px;
                }
            }
        </style>
        <div style="text-align: center;" class="section animate-fade-in">
            <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>" alt="" width="200px" class="rounded-circle" style="background-color: white; margin-top: -150px; z-index: 10; position: relative; border: 3px solid white;">
        </div>
        <div class="text-center mt-1 section animate-fade-in">
            <h3 class="p-0 m-1"><?php echo htmlspecialchars($guru['namaLengkap']); ?></h3>
            <p class="p-0 m-0"><?php echo htmlspecialchars($guru['jabatan']); ?></p>
        </div>
        <div class="mt-2 text-center d-flex flex-column flex-md-row flex-wrap justify-content-center gap-2 section animate-fade-in">
            <button class="btn border d-flex align-items-center justify-content-center gap-2 px-3 py-2 hover-effect" style="font-size: 14px; min-width: 160px;" data-bs-toggle="modal" data-bs-target="#gantinama">
                <i class="bi bi-pencil-square"></i>
                <span>Edit Nama</span>
            </button>
            <button class="btn border d-flex align-items-center justify-content-center gap-2 px-3 py-2 hover-effect" style="font-size: 14px; min-width: 160px;" data-bs-toggle="modal" data-bs-target="#gantifoto">
                <i class="bi bi-image"></i>
                <span>Edit Foto</span>
            </button>
            <button class="btn border d-flex align-items-center justify-content-center gap-2 px-3 py-2 hover-effect" style="font-size: 14px; min-width: 160px;" data-bs-toggle="modal" data-bs-target="#gantilebih">
                <i class="bi bi-asterisk"></i>
                <span>Edit Profil</span>
            </button>
        </div>

        <style>
            .hover-effect {
                transition: all 0.3s ease;
                border: 1px solid #ddd;
                background: white;
                border-radius: 14px;
            }

            .hover-effect:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                background: rgb(218, 119, 86);
                color: white;
                border-color: transparent;
            }
        </style>
        <div class="col d-flex justify-content-center mt-2 ">
            <div class="row ">
                <!-- pendidikan sekolah sebelum dan saat ini -->
                <div class=" section animate-fade-in">
                    <div class="d-flex gap-3 flex-column flex-md-row">
                        <!-- pendidikan sebelumnya -->
                        <?php if (!empty($guru['pendidikan_s1'])): ?>
                            <div class="border p-3 flex-fill" style="border-radius: 14px;">
                                <img src="assets/lulusan.png" alt="" width="35px" height="35px" class="rounded">
                                <div>
                                    <p style="font-size: 12px; font-weight: bold;" class="p-0 m-0 pt-1">Lulusan</h6>
                                    <p class="p-0 m-0"><?php echo htmlspecialchars($guru['pendidikan_s1']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($guru['jabatan'])): ?>
                            <div class="border p-3 flex-fill" style="border-radius: 14px;">
                                <img src="assets/jabatan.png" alt="" width="35px" height="35px" class="rounded">
                                <div>
                                    <p style="font-size: 12px; font-weight: bold;" class="p-0 m-0 pt-1">Jabatan Saat Ini</h6>
                                    <p class="p-0 m-0"><?php echo htmlspecialchars($guru['jabatan']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- sertifikasi -->
                <?php if (!empty($guru['sertifikasi1']) || !empty($guru['sertifikasi2']) || !empty($guru['sertifikasi3'])): ?>
                    <div class="d-flex mt-3">
                        <div class="border p-3 flex-fill" style="border-radius: 14px;">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="assets/sertifikat.png" alt="" width="35px" height="35px" class="rounded">
                                <div>
                                    <p style="font-size: 12px; font-weight: bold;" class="p-0 m-0">Sertifikasi</h6>
                                    <p style="font-size: 12px;" class="text-muted p-0 m-0">Sertifikat yang dimiliki</p>
                                </div>
                            </div>
                            <div class="p-3">
                                <?php if (!empty($guru['sertifikasi1'])): ?>
                                    <li><?php echo htmlspecialchars($guru['sertifikasi1']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($guru['sertifikasi2'])): ?>
                                    <li><?php echo htmlspecialchars($guru['sertifikasi2']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($guru['sertifikasi3'])): ?>
                                    <li><?php echo htmlspecialchars($guru['sertifikasi3']); ?></li>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- riwayat publikasi -->
                <?php if (!empty($guru['publikasi1']) || !empty($guru['publikasi2']) || !empty($guru['publikasi3'])): ?>
                    <div class="d-flex mt-3">
                        <div class="border p-3 flex-fill" style="border-radius: 14px;">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="assets/publikasi.png" alt="" width="35px" height="35px" class="rounded">
                                <div>
                                    <p style="font-size: 12px; font-weight: bold;" class="p-0 m-0">Publikasi</h6>
                                    <p style="font-size: 12px;" class="text-muted p-0 m-0">Riwayat penulisan yang telah di publikasi</p>
                                </div>
                            </div>
                            <div class="p-3">
                                <?php if (!empty($guru['publikasi1'])): ?>
                                    <li><?php echo htmlspecialchars($guru['publikasi1']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($guru['publikasi2'])): ?>
                                    <li><?php echo htmlspecialchars($guru['publikasi2']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($guru['publikasi3'])): ?>
                                    <li><?php echo htmlspecialchars($guru['publikasi3']); ?></li>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- riwayat proyek -->
                <?php if (!empty($guru['proyek1']) || !empty($guru['proyek2']) || !empty($guru['proyek3'])): ?>
                    <div class="d-flex mt-3">
                        <div class="border p-3 flex-fill" style="border-radius: 14px;">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="assets/proyek.png" alt="" width="35px" height="35px" class="rounded">
                                <div>
                                    <p style="font-size: 12px; font-weight: bold;" class="p-0 m-0">Riwayat Proyek</h6>
                                    <p style="font-size: 12px;" class="text-muted p-0 m-0">Riwayat proyek pendidikan yang telah di ikuti</p>
                                </div>
                            </div>
                            <div class="p-3">
                                <?php if (!empty($guru['proyek1'])): ?>
                                    <li><?php echo htmlspecialchars($guru['proyek1']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($guru['proyek2'])): ?>
                                    <li><?php echo htmlspecialchars($guru['proyek2']); ?></li>
                                <?php endif; ?>
                                <?php if (!empty($guru['proyek3'])): ?>
                                    <li><?php echo htmlspecialchars($guru['proyek3']); ?></li>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>


            </div>

        </div>

    </div>





    <!-- modal untuk ganti nama -->
    <div class="modal fade" id="gantinama" tabindex="-1" aria-labelledby="modalgantinamalabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5" id="modalgantinamalabel" style="font-weight: 600;">Edit Profil</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="update_profil_guru.php" method="POST">
                    <div class="modal-body px-4">
                        <div class="form-group">
                            <label for="floatingInputValue" class="form-label text-muted mb-2" style="font-size: 0.9rem;">Nama dan Gelar</label>
                            <input type="text"
                                name="nama"
                                class="form-control"
                                id="floatingInputValue"
                                value="<?php echo htmlspecialchars($guru['namaLengkap']); ?>"
                                required
                                style="border-radius: 10px; 
                                          padding: 12px;
                                          border: 1px solid #e1e1e1;
                                          background-color: #f8f9fa;
                                          transition: all 0.2s ease;">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="submit"
                            name="update_nama"
                            class="btn color-web w-100"
                            style="color: white;
                                       border-radius: 12px;
                                       padding: 12px;
                                       font-weight: 500;
                                       transition: all 0.2s ease;">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .modal-content {
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            box-shadow: none;
            border-color: rgb(218, 119, 86);
            background-color: #fff;
        }

        .btn:active {
            transform: scale(0.98);
        }
    </style>

    <!-- Modal ganti foto dengan iOS style -->
    <div class="modal fade rounded" id="gantifoto" tabindex="-1" aria-labelledby="modalgantifoto" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 8px 30px rgba(0,0,0,0.12);">
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5 fw-semibold" id="modalgantifoto">Edit Foto Anda</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <div class="d-flex gap-3 mb-4">
                        <!-- Button untuk profil dengan iOS style -->
                        <div class="btn w-100 p-0 position-relative" style="border: none;">
                            <input type="file" id="input-foto-profil" class="d-none" accept="image/*">
                            <label for="input-foto-profil"
                                class="w-100 py-4 px-3 rounded-4 d-flex flex-column align-items-center justify-content-center"
                                style="background: #f5f5f7; cursor: pointer; transition: all 0.2s ease;">
                                <img src="assets/profil_fill.png" width="40px" alt="" class="mb-2">
                                <p class="mb-0" style="font-size: 13px; color: #1c1c1e;">Ubah Foto</p>
                            </label>
                        </div>

                        <!-- Button untuk latar belakang dengan iOS style -->
                        <div class="btn w-100 p-0 position-relative" style="border: none;">
                            <input type="file" id="input-foto-latar" class="d-none" accept="image/*">
                            <label for="input-foto-latar"
                                class="w-100 py-4 px-3 rounded-4 d-flex flex-column align-items-center justify-content-center"
                                style="background: #f5f5f7; cursor: pointer; transition: all 0.2s ease;">
                                <img src="assets/background.png" width="40px" alt="" class="mb-2">
                                <p class="mb-0" style="font-size: 13px; color: #1c1c1e;">Ubah Latar</p>
                            </label>
                        </div>
                    </div>

                    <!-- Container untuk cropper dengan iOS style -->
                    <div id="cropper-container" class="d-none">
                        <div class="img-container rounded-4 overflow-hidden mb-3">
                            <img id="crop-image" src="" alt="Gambar untuk di-crop" style="max-width: 100%;">
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="button"
                                id="cancel-crop"
                                class="btn flex-grow-1 py-3 rounded-4"
                                style="background: #f5f5f7; color: #ff3b30; font-weight: 500; border: none;">
                                Batal
                            </button>
                            <button type="button"
                                id="crop-submit"
                                class="btn flex-grow-1 py-3 rounded-4"
                                style="background: rgb(218, 119, 86); color: white; font-weight: 500; border: none;">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Form tersembunyi untuk submit hasil crop -->
    <form id="crop-form" action="update_profil_guru.php" method="POST" class="d-none">
        <input type="hidden" name="cropped_image" id="cropped-image-input">
        <input type="hidden" name="image_type" id="image-type-input">
    </form>

    <script>
        let cropper = null;
        let currentImageType = null;

        // Fungsi untuk menangani file yang dipilih
        function handleImageSelect(e, imageType) {
            const file = e.target.files[0];
            if (file) {
                currentImageType = imageType;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const cropImage = document.getElementById('crop-image');
                    cropImage.src = e.target.result;

                    // Tampilkan container cropper
                    document.getElementById('cropper-container').classList.remove('d-none');

                    // Inisialisasi cropper
                    if (cropper) {
                        cropper.destroy();
                    }

                    // Konfigurasi berbeda untuk profil dan latar belakang
                    const cropperOptions = imageType === 'profil' ? {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        cropBoxResizable: false,
                        cropBoxMovable: false,
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

        // Event listener untuk input file
        document.getElementById('input-foto-profil').addEventListener('change', (e) => handleImageSelect(e, 'profil'));
        document.getElementById('input-foto-latar').addEventListener('change', (e) => handleImageSelect(e, 'latar'));

        // Event listener untuk tombol batal
        document.getElementById('cancel-crop').addEventListener('click', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            document.getElementById('cropper-container').classList.add('d-none');
            document.getElementById('input-foto-profil').value = '';
            document.getElementById('input-foto-latar').value = '';
        });

        // Event listener untuk tombol simpan
        document.getElementById('crop-submit').addEventListener('click', function() {
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
        });

        // Reset cropper saat modal ditutup
        document.getElementById('gantifoto').addEventListener('hidden.bs.modal', function() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            document.getElementById('cropper-container').classList.add('d-none');
            document.getElementById('input-foto-profil').value = '';
            document.getElementById('input-foto-latar').value = '';
        });
    </script>

    <style>
        .img-container {
            max-height: 400px;
        }

        .img-container img {
            max-width: 100%;
            max-height: 100%;
        }
    </style>


    <!-- Modal edit rekam with iOS style -->
    <div class="modal fade" id="gantilebih" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalgantinamalabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 8px 30px rgba(0,0,0,0.12);">
                <div class="modal-header border-0">
                    <h1 class="modal-title fs-5 fw-semibold" id="modalgantinamalabel">Edit Profil</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="update_profil_guru.php" method="POST">
                    <div class="modal-body px-4">
                        <!-- Sections with iOS style -->
                        <div class="form-sections">
                            <!-- Riwayat Pendidikan -->
                            <div class="section mb-4">
                                <label class="form-label fw-medium mb-3" style="font-size: 15px;">Riwayat Pendidikan</label>
                                <div class="bg-light rounded-4 p-3">
                                    <input type="text" name="pendidikan_s1" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['pendidikan_s1']); ?>"
                                        placeholder="S1 - Contoh: Universitas Muhammadiyah Surakarta"
                                        style="padding: 12px;">
                                    <input type="text" name="pendidikan_s2" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['pendidikan_s2']); ?>"
                                        placeholder="S2 - Contoh: Universitas Gajah Mada"
                                        style="padding: 12px;">
                                    <input type="text" name="pendidikan_lainnya" class="form-control border-0 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['pendidikan_lainnya']); ?>"
                                        placeholder="Pendidikan Lainnya"
                                        style="padding: 12px;">
                                </div>
                            </div>

                            <!-- Jabatan -->
                            <div class="section mb-4">
                                <label class="form-label fw-medium mb-3" style="font-size: 15px;">Jabatan Sekolah</label>
                                <select class="form-select bg-light border-0 rounded-4" name="jabatan" style="padding: 12px;">
                                    <option value="">Pilih Jabatan</option>
                                    <?php
                                    $jabatan_options = [
                                        'Kepala Sekolah',
                                        'Wakil Kepala Sekolah',
                                        'Bag. Kurikulum',
                                        'Bag. Kesiswaan',
                                        'Kepala Tata Usaha',
                                        'Wali Kelas',
                                        'Bag. Ekonomi Bisnis',
                                        'Staf IT',
                                        'Staf TU',
                                        'Guru Mapel',
                                        'Guru BImbingan Konseling'
                                    ];
                                    foreach ($jabatan_options as $option) {
                                        $selected = ($guru['jabatan'] == $option) ? 'selected' : '';
                                        echo "<option value=\"$option\" $selected>$option</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Sertifikasi -->
                            <div class="section mb-4">
                                <label class="form-label fw-medium mb-3" style="font-size: 15px;">Sertifikasi</label>
                                <div class="bg-light rounded-4 p-3">
                                    <input type="text" name="sertifikasi1" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['sertifikasi1']); ?>"
                                        placeholder="Sertifikasi Pertama"
                                        style="padding: 12px;">
                                    <input type="text" name="sertifikasi2" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['sertifikasi2']); ?>"
                                        placeholder="Sertifikasi Kedua"
                                        style="padding: 12px;">
                                    <input type="text" name="sertifikasi3" class="form-control border-0 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['sertifikasi3']); ?>"
                                        placeholder="Sertifikasi Ketiga"
                                        style="padding: 12px;">
                                </div>
                            </div>

                            <!-- Publikasi -->
                            <div class="section mb-4">
                                <label class="form-label fw-medium mb-3" style="font-size: 15px;">Publikasi</label>
                                <div class="bg-light rounded-4 p-3">
                                    <input type="text" name="publikasi1" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['publikasi1']); ?>"
                                        placeholder="Publikasi Pertama"
                                        style="padding: 12px;">
                                    <input type="text" name="publikasi2" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['publikasi2']); ?>"
                                        placeholder="Publikasi Kedua"
                                        style="padding: 12px;">
                                    <input type="text" name="publikasi3" class="form-control border-0 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['publikasi3']); ?>"
                                        placeholder="Publikasi Ketiga"
                                        style="padding: 12px;">
                                </div>
                            </div>

                            <!-- Proyek -->
                            <div class="section mb-4">
                                <label class="form-label fw-medium mb-3" style="font-size: 15px;">Proyek</label>
                                <div class="bg-light rounded-4 p-3">
                                    <input type="text" name="proyek1" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['proyek1']); ?>"
                                        placeholder="Proyek Pertama"
                                        style="padding: 12px;">
                                    <input type="text" name="proyek2" class="form-control border-0 mb-2 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['proyek2']); ?>"
                                        placeholder="Proyek Kedua"
                                        style="padding: 12px;">
                                    <input type="text" name="proyek3" class="form-control border-0 bg-white rounded-3"
                                        value="<?php echo htmlspecialchars($guru['proyek3']); ?>"
                                        placeholder="Proyek Ketiga"
                                        style="padding: 12px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="submit" name="update_info" class="btn color-web w-100"
                            style="color: white; padding: 12px; border-radius: 12px; font-weight: 500;">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* iOS style customizations */
        .form-control:focus {
            box-shadow: none;
            background-color: #fff !important;
        }

        .form-select:focus {
            box-shadow: none;
            border-color: rgb(218, 119, 86);
        }

        .modal .form-control::placeholder {
            color: #999;
            font-size: 14px;
        }

        .modal-content {
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
        }

        .section {
            transition: all 0.3s ease;
        }

        .form-control,
        .form-select {
            transition: all 0.2s ease;
        }

        .form-control:hover,
        .form-select:hover {
            background-color: #f8f9fa;
        }

        .btn:active {
            transform: scale(0.98);
        }
    </style>

</body>

</html>