<?php
session_start();
require "koneksi.php";

// Cek apakah user sudah login dan punya akses admin
if (!isset($_SESSION['userid']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Proses form jika ada
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_info') {
        $kategori = $_POST['kategori'];
        $judul = !empty($_POST['judul']) ? $_POST['judul'] : NULL;
        $konten = $_POST['konten'];

        // Cek apakah ini kategori baru dengan keywords
        if (isset($_POST['keywords'])) {
            $keywords = $_POST['keywords'];

            // Tambahkan entri keywords untuk kategori baru
            $keywordQuery = "INSERT INTO informasi_sekolah (kategori, judul, konten) VALUES (?, 'keywords', ?)";
            $keywordStmt = mysqli_prepare($koneksi, $keywordQuery);
            mysqli_stmt_bind_param($keywordStmt, "ss", $kategori, $keywords);

            if (!mysqli_stmt_execute($keywordStmt)) {
                $error = "Gagal menambahkan keywords untuk kategori baru: " . mysqli_error($koneksi);
            }
        }

        // Tambahkan informasi
        $infoQuery = "INSERT INTO informasi_sekolah (kategori, judul, konten) VALUES (?, ?, ?)";
        $infoStmt = mysqli_prepare($koneksi, $infoQuery);
        mysqli_stmt_bind_param($infoStmt, "sss", $kategori, $judul, $konten);

        if (mysqli_stmt_execute($infoStmt)) {
            $message = "Informasi berhasil ditambahkan";
        } else {
            $error = "Gagal menambahkan informasi: " . mysqli_error($koneksi);
        }
    }

    // Tambahkan kondisi untuk menangani delete
    else if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = $_POST['id'];

            // Query untuk menghapus data
            $deleteQuery = "DELETE FROM informasi_sekolah WHERE id = ?";
            $deleteStmt = mysqli_prepare($koneksi, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "i", $id);

            if (mysqli_stmt_execute($deleteStmt)) {
                $message = "Informasi berhasil dihapus";
            } else {
                $error = "Gagal menghapus informasi: " . mysqli_error($koneksi);
            }
        } else {
            $error = "ID informasi tidak valid";
        }
    }
    // Tambahkan kondisi untuk menangani edit jika diperlukan
    else if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        if (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id = $_POST['id'];
            $kategori = $_POST['kategori'];
            $judul = !empty($_POST['judul']) ? $_POST['judul'] : NULL;
            $konten = $_POST['konten'];

            // Query untuk update data
            $updateQuery = "UPDATE informasi_sekolah SET kategori = ?, judul = ?, konten = ? WHERE id = ?";
            $updateStmt = mysqli_prepare($koneksi, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, "sssi", $kategori, $judul, $konten, $id);

            if (mysqli_stmt_execute($updateStmt)) {
                $message = "Informasi berhasil diperbarui";
            } else {
                $error = "Gagal memperbarui informasi: " . mysqli_error($koneksi);
            }
        } else {
            $error = "ID informasi tidak valid";
        }
    }
}

// Ambil semua kategori yang ada
$categoryQuery = "SELECT DISTINCT kategori FROM informasi_sekolah ORDER BY kategori";
$categoryResult = mysqli_query($koneksi, $categoryQuery);
$categories = [];

while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row['kategori'];
}

// Ambil data untuk ditampilkan
$query = "SELECT * FROM informasi_sekolah ORDER BY kategori, judul";
$result = mysqli_query($koneksi, $query);

// Filter kategori
$filter_category = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Query dengan filter dan pagination
if (!empty($filter_category)) {
    $count_query = "SELECT COUNT(*) as total FROM informasi_sekolah WHERE kategori = ?";
    $count_stmt = mysqli_prepare($koneksi, $count_query);
    mysqli_stmt_bind_param($count_stmt, "s", $filter_category);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_records = $count_row['total'];

    $query = "SELECT * FROM informasi_sekolah WHERE kategori = ? ORDER BY kategori, judul LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "sii", $filter_category, $records_per_page, $offset);
} else {
    $count_query = "SELECT COUNT(*) as total FROM informasi_sekolah";
    $count_result = mysqli_query($koneksi, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_records = $count_row['total'];

    $query = "SELECT * FROM informasi_sekolah ORDER BY kategori, judul LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $records_per_page, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Informasi Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<style>
    body {
        font-family: merriweather;
    }

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
</style>

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

<body class="bg-white">
    <div class="container ps-4 col-utama pt-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">Data SAGA AI</h2>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#schoolInfoModal">
                    <i class="bi bi-plus-circle"></i> Tambah Informasi Sekolah
                </button>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- iOS-style Info List Card -->
        <div class="card animate-fade-in shadow-none border" style="border-radius: 20px;">
            <div class="card-body p-0">
                <!-- Search bar iOS style -->
                <div class="px-4 pt-4 pb-2">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light border-0" style="border-radius: 12px 0 0 12px;">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInfo" class="form-control bg-light border-0"
                            placeholder="Cari informasi..." style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <div class="table-responsive px-2">
                    <table class="table table-borderless align-middle">
                        <thead class="text-muted" style="font-size: 0.85rem; font-weight: 600;">
                            <tr>
                                <th class="ps-3" style="width: 15%">Kategori</th>
                                <th style="width: 20%">Judul</th>
                                <th style="width: 35%">Konten</th>
                                <th style="width: 15%">Terakhir Diperbarui</th>
                                <th style="width: 15%"></th>
                            </tr>
                        </thead>
                        <tbody id="infoTableBody">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="info-item">
                                        <td class="ps-3">
                                            <span class="badge" style="background-color: rgba(86, 142, 218, 0.15); color: rgb(86, 142, 218); font-weight: 600; padding: 5px 10px; border-radius: 6px;">
                                                <?= htmlspecialchars($row['kategori']) ?>
                                            </span>
                                        </td>
                                        <td class="fw-medium"><?= htmlspecialchars($row['judul'] ?: '-') ?></td>
                                        <td class="text-muted"><?= htmlspecialchars(substr($row['konten'], 0, 100)) . (strlen($row['konten']) > 100 ? '...' : '') ?></td>
                                        <td><span class="text-muted"><?= htmlspecialchars($row['updated_at']) ?></span></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button"
                                                    class="btn btn-sm text-muted edit-btn"
                                                    style="background: none;"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-kategori="<?= htmlspecialchars($row['kategori']) ?>"
                                                    data-judul="<?= htmlspecialchars($row['judul'] ?: '') ?>"
                                                    data-konten="<?= htmlspecialchars($row['konten']) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm text-danger delete-btn"
                                                    style="background: none;"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-konten="<?= htmlspecialchars(substr($row['konten'], 0, 50)) . (strlen($row['konten']) > 50 ? '...' : '') ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center py-4">
                                            <i class="bi bi-info-circle text-muted" style="font-size: 2rem;"></i>
                                            <p class="text-muted mt-2">Tidak ada data informasi</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Informasi Sekolah -->
    <div class="modal fade" id="schoolInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" id="infoForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Informasi Sekolah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <div class="input-group">
                                <select id="kategoriSelect" name="kategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php
                                    // Ambil semua kategori yang ada
                                    $kategoriQuery = "SELECT DISTINCT kategori FROM informasi_sekolah ORDER BY kategori";
                                    $kategoriResult = mysqli_query($koneksi, $kategoriQuery);

                                    while ($row = mysqli_fetch_assoc($kategoriResult)) {
                                        echo '<option value="' . htmlspecialchars($row['kategori']) . '">' .
                                            ucfirst(htmlspecialchars($row['kategori'])) . '</option>';
                                    }
                                    ?>
                                    <option value="new">+ Kategori Baru</option>
                                </select>
                                <button type="button" id="showNewCategoryBtn" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Form untuk kategori baru (hidden by default) -->
                        <div id="newCategoryForm" class="mb-3 border p-3 rounded bg-light" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Nama Kategori Baru</label>
                                <input type="text" id="newCategory" name="new_category" class="form-control" placeholder="Contoh: prestasi, alumni">
                                <div class="form-text">Gunakan huruf kecil tanpa spasi. Untuk spasi gunakan underscore (_).</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kata Kunci (Keywords)</label>
                                <input type="text" id="categoryKeywords" name="category_keywords" class="form-control" placeholder="Contoh: prestasi, penghargaan, juara, lomba">
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> Kata kunci adalah frasa yang membantu AI menampilkan informasi kategori ini.
                                    Pisahkan dengan koma. Sertakan kata-kata yang mungkin digunakan dalam pertanyaan.
                                </div>
                            </div>
                            <button type="button" id="addCategoryBtn" class="btn btn-sm btn-success">
                                Tambahkan Kategori
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Judul (Opsional)</label>
                            <input type="text" name="judul" id="judul" class="form-control" placeholder="Contoh: Fasilitas Sekolah, Prestasi Akademik">
                            <div class="form-text">Judul digunakan untuk mengelompokkan informasi sejenis.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konten</label>
                            <textarea name="konten" id="konten" class="form-control" rows="4" required placeholder="Masukkan informasi yang ingin disimpan..."></textarea>
                            <div class="form-text">
                                <i class="bi bi-lightbulb"></i> Tips:
                                <ul class="mt-1 small">
                                    <li>Mulai konten dengan kata kunci penting (contoh: "Kepala Sekolah: Ahmad")</li>
                                    <li>Batasi setiap entri hingga 200 karakter untuk hasil optimal</li>
                                    <li>Untuk daftar, pisahkan dengan koma (contoh: "IPA, Matematika, Bahasa Inggris")</li>
                                </ul>
                            </div>
                        </div>

                        <input type="hidden" name="action" value="add_info">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tambahkan di bagian script atau file JS terpisah
        document.addEventListener('DOMContentLoaded', function() {
            // Tombol untuk menampilkan form kategori baru
            const showNewCategoryBtn = document.getElementById('showNewCategoryBtn');
            const newCategoryForm = document.getElementById('newCategoryForm');
            const kategoriSelect = document.getElementById('kategoriSelect');
            const addCategoryBtn = document.getElementById('addCategoryBtn');
            const newCategoryInput = document.getElementById('newCategory');
            const categoryKeywordsInput = document.getElementById('categoryKeywords');

            // Tampilkan form kategori baru saat tombol + diklik
            showNewCategoryBtn.addEventListener('click', function() {
                newCategoryForm.style.display = 'block';
                newCategoryInput.focus();
            });

            // Tambahkan kategori baru ke select
            addCategoryBtn.addEventListener('click', function() {
                const newCategory = newCategoryInput.value.trim();

                if (!newCategory) {
                    alert('Nama kategori tidak boleh kosong');
                    return;
                }

                // Periksa format kategori (hanya huruf kecil, angka, dan underscore)
                if (!/^[a-z0-9_]+$/.test(newCategory)) {
                    alert('Nama kategori hanya boleh mengandung huruf kecil, angka, dan underscore.');
                    return;
                }

                // Periksa apakah kategori ini sudah ada
                let categoryExists = false;
                for (let i = 0; i < kategoriSelect.options.length; i++) {
                    if (kategoriSelect.options[i].value === newCategory) {
                        categoryExists = true;
                        break;
                    }
                }

                if (categoryExists) {
                    alert('Kategori ini sudah ada');
                    return;
                }

                // Tambahkan kategori baru ke dropdown
                const newOption = document.createElement('option');
                newOption.value = newCategory;
                newOption.textContent = newCategory.charAt(0).toUpperCase() + newCategory.slice(1);

                // Sisipkan sebelum opsi "Kategori Baru"
                kategoriSelect.insertBefore(newOption, kategoriSelect.options[kategoriSelect.options.length - 1]);

                // Pilih kategori baru
                kategoriSelect.value = newCategory;

                // Sembunyikan form kategori baru
                newCategoryForm.style.display = 'none';

                // Tambahkan keywords ke local storage untuk sementara
                const keywords = categoryKeywordsInput.value.trim();
                localStorage.setItem('new_category_keywords_' + newCategory, keywords);

                // Reset input
                newCategoryInput.value = '';
                categoryKeywordsInput.value = '';

                // Highlight select box untuk menunjukkan perubahan
                kategoriSelect.classList.add('border-success');
                setTimeout(() => {
                    kategoriSelect.classList.remove('border-success');
                }, 2000);
            });

            // Tampilkan form kategori baru jika opsi "Kategori Baru" dipilih
            kategoriSelect.addEventListener('change', function() {
                if (this.value === 'new') {
                    newCategoryForm.style.display = 'block';
                    newCategoryInput.focus();
                } else {
                    newCategoryForm.style.display = 'none';
                }
            });

            // Tambahkan handler untuk form submit
            document.getElementById('infoForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const kategori = kategoriSelect.value;

                if (kategori === 'new' || kategori === '') {
                    alert('Silahkan pilih atau tambahkan kategori terlebih dahulu');
                    return;
                }

                // Cek apakah ini kategori baru
                const isNewCategory = localStorage.getItem('new_category_keywords_' + kategori) !== null;

                // Jika kategori baru, tambahkan keywords ke form
                if (isNewCategory) {
                    const keywords = localStorage.getItem('new_category_keywords_' + kategori);
                    const keywordsInput = document.createElement('input');
                    keywordsInput.type = 'hidden';
                    keywordsInput.name = 'keywords';
                    keywordsInput.value = keywords;
                    this.appendChild(keywordsInput);

                    // Hapus dari local storage
                    localStorage.removeItem('new_category_keywords_' + kategori);
                }

                // Submit form
                this.submit();
            });
        });
    </script>

    <!-- Modal Edit Informasi -->
    <div class="modal fade" id="editInfoModal" tabindex="-1" aria-labelledby="editInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editInfoModalLabel">Edit Informasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-body px-4">
                        <div class="mb-4">
                            <label for="edit-kategori" class="form-label fw-medium small mb-2">Kategori</label>
                            <select name="kategori" id="edit-kategori" class="form-select bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category) ?>"><?= ucfirst(htmlspecialchars($category)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="edit-judul" class="form-label fw-medium small mb-2">Judul (Opsional)</label>
                            <input type="text" name="judul" id="edit-judul" class="form-control bg-light border-0" style="border-radius: 12px; padding: 12px 15px;">
                        </div>
                        <div class="mb-4">
                            <label for="edit-konten" class="form-label fw-medium small mb-2">Konten</label>
                            <textarea name="konten" id="edit-konten" class="form-control bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"
                            style="border-radius: 12px; padding: 10px 20px;">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Hapus Informasi -->
    <div class="modal fade" id="deleteInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus informasi ini?</p>
                        <p id="delete-preview" class="fst-italic"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kategori Baru -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="addCategoryModalLabel">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="add_category">
                    <div class="modal-body px-4">
                        <div class="mb-4">
                            <label for="new_category" class="form-label fw-medium small mb-2">Nama Kategori</label>
                            <input type="text" name="new_category" class="form-control bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" required>
                            <small class="text-muted">Gunakan huruf kecil tanpa spasi (e.g., prestasi, alumni)</small>
                        </div>
                        <div class="mb-4">
                            <label for="keywords" class="form-label fw-medium small mb-2">Keywords (dipisahkan koma)</label>
                            <input type="text" name="keywords" class="form-control bg-light border-0" style="border-radius: 12px; padding: 12px 15px;" required>
                            <small class="text-muted">Contoh: prestasi, penghargaan, award, juara</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" style="border-radius: 12px; padding: 10px 20px;"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"
                            style="border-radius: 12px; padding: 10px 20px;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit button
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const kategori = this.dataset.kategori;
                    const judul = this.dataset.judul;
                    const konten = this.dataset.konten;

                    document.getElementById('edit-id').value = id;
                    document.getElementById('edit-kategori').value = kategori;
                    document.getElementById('edit-judul').value = judul;
                    document.getElementById('edit-konten').value = konten;

                    new bootstrap.Modal(document.getElementById('editInfoModal')).show();
                });
            });

            // Delete button
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const konten = this.dataset.konten;

                    document.getElementById('delete-id').value = id;
                    document.getElementById('delete-preview').textContent = konten;

                    new bootstrap.Modal(document.getElementById('deleteInfoModal')).show();
                });
            });
        });
    </script>
</body>

</html>