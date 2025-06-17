<?php
session_start();
require "koneksi.php";

// Cek apakah user yang login adalah kepala sekolah
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Ambil data admin
$userid = $_SESSION['userid'];
$query = "SELECT * FROM guru WHERE username = '$userid'";
$result = mysqli_query($koneksi, $query);
$guru = mysqli_fetch_assoc($result);


// Query untuk mengambil semua data guru
$query_guru = "SELECT g.*, 
              (SELECT COUNT(*) FROM kelas WHERE guru_id = g.username) as jumlah_kelas 
              FROM guru g ORDER BY g.namaLengkap ASC";
$result_guru = mysqli_query($koneksi, $query_guru);

// Jika ada form penambahan guru yang disubmit
if (isset($_POST['tambah_guru'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $namaLengkap = mysqli_real_escape_string($koneksi, $_POST['namaLengkap']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);

    // Cek apakah username sudah ada
    $cek_username = mysqli_query($koneksi, "SELECT * FROM guru WHERE username = '$username'");
    if (mysqli_num_rows($cek_username) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Insert guru baru
        $insert_query = "INSERT INTO guru (username, password, namaLengkap, jabatan) 
                         VALUES ('$username', '$password', '$namaLengkap', '$jabatan')";
        if (mysqli_query($koneksi, $insert_query)) {
            $success = "Guru berhasil ditambahkan!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
        }
    }
}

// Jika ada request hapus guru
if (isset($_GET['hapus'])) {
    $username = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    // Cek apakah guru memiliki kelas
    $cek_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kelas WHERE guru_id = '$username'");
    $data_kelas = mysqli_fetch_assoc($cek_kelas);

    if ($data_kelas['total'] > 0) {
        $error = "Guru tidak dapat dihapus karena masih memiliki kelas!";
    } else {
        // Hapus guru
        $hapus_query = "DELETE FROM guru WHERE username = '$username'";
        if (mysqli_query($koneksi, $hapus_query)) {
            $success = "Guru berhasil dihapus!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Manajemen Guru - SMAGAEdu</title>
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

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }


        .profile-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th {
            font-weight: 600;
            color: #444;
        }

        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 5px;
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
            <div class="d-flex justify-content-between align-items-center mt-2 mb-4">
                <h2 class="mb-0 fw-bold">Guru dan Karyawan</h2>
                <button class="btn btn-white border" style="border-radius:15px;" data-bs-toggle="modal" data-bs-target="#tambahGuruModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Guru
                </button>
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

            <!-- Statistik Guru -->
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="card shadow-none border h-100" style="border-radius: 15px;">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                style="width: 60px; height: 60px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-people-fill" style="font-size: 1.5rem; color: #da7756;"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 fw-bold"><?php echo mysqli_num_rows($result_guru); ?></h3>
                                <p class="text-muted mb-0">Total Guru</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card shadow-none border h-100" style="border-radius: 15px;">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                style="width: 60px; height: 60px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-building" style="font-size: 1.5rem; color: #da7756;"></i>
                            </div>
                            <div>
                                <?php
                                $query_kelas = "SELECT COUNT(*) as total FROM kelas";
                                $result_kelas = mysqli_query($koneksi, $query_kelas);
                                $total_kelas = mysqli_fetch_assoc($result_kelas)['total'];
                                ?>
                                <h3 class="mb-0 fw-bold"><?php echo $total_kelas; ?></h3>
                                <p class="text-muted mb-0">Total Kelas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card shadow-none border h-100" style="border-radius: 15px;">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                style="width: 60px; height: 60px; background-color: rgba(218, 119, 86, 0.1);">
                                <i class="bi bi-person-check-fill" style="font-size: 1.5rem; color: #da7756;"></i>
                            </div>
                            <div>
                                <?php
                                $query_aktif = "SELECT COUNT(DISTINCT guru_id) as total FROM kelas";
                                $result_aktif = mysqli_query($koneksi, $query_aktif);
                                $total_aktif = mysqli_fetch_assoc($result_aktif)['total'];
                                ?>
                                <h3 class="mb-0 fw-bold"><?php echo $total_aktif; ?></h3>
                                <p class="text-muted mb-0">Guru Aktif</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- iOS-style Teacher List Card -->
            <div class="card animate-fade-in shadow-none border" style="border-radius: 20px;">
                <div class="card-body p-0">
                    <!-- Search bar iOS style -->
                    <div class="px-4 pt-4 pb-2">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="searchGuru" class="form-control bg-light border-0" 
                                   placeholder="Cari guru..." style="border-radius: 0 12px 12px 0;">
                        </div>
                    </div>
                    
                    <div class="table-responsive px-2">
                        <table class="table table-borderless align-middle">
                            <thead class="text-muted" style="font-size: 0.85rem; font-weight: 600;">
                                <tr>
                                    <th class="ps-3" style="width: 10%">Foto</th>
                                    <th style="width: 25%">Nama Lengkap</th>
                                    <th style="width: 20%">Username</th>
                                    <th style="width: 15%">Jabatan</th>
                                    <th style="width: 15%">Kelas</th>
                                    <th style="width: 15%"></th>
                                </tr>
                            </thead>
                            <tbody id="guruTableBody">
                                <?php if (mysqli_num_rows($result_guru) > 0): ?>
                                    <?php while ($guru = mysqli_fetch_assoc($result_guru)): ?>
                                        <tr class="guru-item">
                                            <td class="ps-3">
                                                <div style="width: 48px; height: 48px; overflow: hidden; border-radius: 12px;">
                                                    <img src="<?php echo !empty($guru['foto_profil']) ? 'uploads/profil/' . $guru['foto_profil'] : 'assets/pp.png'; ?>"
                                                        alt="<?php echo htmlspecialchars($guru['namaLengkap']); ?>"
                                                        class="w-100 h-100" style="object-fit: cover;">
                                                </div>
                                            </td>
                                            <td class="fw-medium"><?php echo htmlspecialchars($guru['namaLengkap']); ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($guru['username']); ?></td>
                                            <td><span class="text-muted"><?php echo htmlspecialchars($guru['jabatan'] ?: 'Belum diatur'); ?></span></td>
                                            <td>
                                                <span class="badge" style="background-color: rgba(218, 119, 86, 0.15); color: rgb(218, 119, 86); font-weight: 600; padding: 5px 10px; border-radius: 6px;">
                                                    <?php echo $guru['jumlah_kelas']; ?> kelas
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit_guru.php?username=<?php echo $guru['username']; ?>" 
                                                       class="btn btn-sm text-muted" style="background: none;">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button"
                                                           class="btn btn-sm text-danger deleteBtn" 
                                                           style="background: none;"
                                                           data-username="<?php echo $guru['username']; ?>"
                                                           data-name="<?php echo htmlspecialchars($guru['namaLengkap']); ?>"
                                                           data-bs-toggle="modal" 
                                                           data-bs-target="#deleteModal"
                                                           <?php if ($guru['jumlah_kelas'] > 0): ?>disabled<?php endif; ?>>
                                                            <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center py-4">
                                                <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                                                <p class="text-muted mt-2">Tidak ada data guru</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <div class="modal-body px-4 text-center">
                            <div class="mb-4">
                                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="mb-3 fw-bold">Hapus " <strong id="deleteGuruName"></strong> " dari SMAGAEdu?</h5>
                            <p class="text-muted">Anda akan menghapus data guru <strong id="deleteGuruName"></strong> dari database. Pastikan seluruh tindakan Anda telah sesuai</p>
                        </div>
                        <div class="modal-footer border-0 pt-0 btn-group">
                            <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;" data-bs-dismiss="modal">Batal</button>
                            <a href="#" id="deleteGuruLink" class="btn btn-danger" style="border-radius: 12px; padding: 10px 20px;">Hapus</a>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Set data for delete modal
                document.addEventListener('DOMContentLoaded', function() {
                    const deleteModal = document.getElementById('deleteModal');
                    if (deleteModal) {
                        deleteModal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget; // Button that triggered the modal
                            const username = button.getAttribute('data-username');
                            const name = button.getAttribute('data-name');
                            
                            document.getElementById('deleteGuruName').textContent = name;
                            document.getElementById('deleteGuruLink').href = '?hapus=' + username;
                        });
                    }
                });
            </script>
            
            <script>
                // Simple search functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('searchGuru');
                    const rows = document.querySelectorAll('.guru-item');
                    
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(searchTerm) ? '' : 'none';
                        });
                    });
                });
            </script>
        </div>
    </div>

    <!-- Modal Tambah Guru -->
    <div class="modal fade" id="tambahGuruModal" tabindex="-1" aria-labelledby="tambahGuruModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="tambahGuruModalLabel">Tambah Guru Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body px-4">
                        <div class="mb-4">
                            <label for="username" class="form-label fw-medium small mb-2">Username</label>
                            <input type="text" class="form-control bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" 
                                id="username" name="username" required>
                            <small class="text-muted">Username akan digunakan untuk login</small>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium small mb-2">Password</label>
                            <input type="password" class="form-control bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" 
                                id="password" name="password" required>
                        </div>
                        <div class="mb-4">
                            <label for="namaLengkap" class="form-label fw-medium small mb-2">Nama Lengkap</label>
                            <input type="text" class="form-control bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" 
                                id="namaLengkap" name="namaLengkap" required>
                        </div>
                        <div class="mb-4">
                            <label for="jabatan" class="form-label fw-medium small mb-2">Jabatan</label>
                            <select class="form-select bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" 
                                id="jabatan" name="jabatan">
                                <option value="">Pilih Jabatan</option>
                                <option value="Kepala Sekolah">Kepala Sekolah</option>
                                <option value="Wakil Kepala Sekolah">Wakil Kepala Sekolah</option>
                                <option value="Bag. Kurikulum">Bag. Kurikulum</option>
                                <option value="Bag. Kesiswaan">Bag. Kesiswaan</option>
                                <option value="Kepala Tata Usaha">Kepala Tata Usaha</option>
                                <option value="Wali Kelas">Wali Kelas</option>
                                <option value="Bag. Ekonomi Bisnis">Bag. Ekonomi Bisnis</option>
                                <option value="Staf IT">Staf IT</option>
                                <option value="Staf TU">Staf TU</option>
                                <option value="Guru Mapel">Guru Mapel</option>
                                <option value="Guru Bimbingan Konseling">Guru Bimbingan Konseling</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;" 
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_guru" class="btn btn-primary" 
                            style="border-radius: 12px; padding: 10px 20px;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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