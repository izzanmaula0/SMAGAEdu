<?php
require "koneksi.php";
session_start();

if(!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$query = "SHOW TABLES FROM smagaedu";
$result = mysqli_query($koneksi, $query);
$tables = [];
while($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $table = $_POST['table'];
    
    switch($action) {
        case 'add':
            $columns = array_keys($_POST);
            $values = array_values($_POST);
            $columns = array_diff($columns, ['action', 'table']);
            $values = array_diff($values, [$action, $table]);
            
            $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES ('" . implode("','", $values) . "')";
            mysqli_query($koneksi, $sql);
            break;
            
        case 'edit':
            $id = $_POST['id'];
            $updates = [];
            foreach($_POST as $key => $value) {
                if(!in_array($key, ['action', 'table', 'id'])) {
                    $updates[] = "$key='$value'";
                }
            }
            $sql = "UPDATE $table SET " . implode(',', $updates) . " WHERE id=$id";
            mysqli_query($koneksi, $sql);
            break;
            
            case 'delete':
                $id = $_POST['id'];
                if($table == 'siswa') {
                    // Delete in the correct order to handle foreign key constraints
                    mysqli_query($koneksi, "DELETE FROM jawaban_ujian WHERE siswa_id = $id");
                    mysqli_query($koneksi, "DELETE FROM kelas_siswa WHERE siswa_id = $id");
                    $sql = "DELETE FROM siswa WHERE id = $id";
                } else {
                    $sql = "DELETE FROM $table WHERE id = $id";
                }
                mysqli_query($koneksi, $sql);
                break;

                case 'bulk_delete':
                    if(isset($_POST['selected_ids'])) {
                        $ids = $_POST['selected_ids'];
                        if($table == 'siswa') {
                            foreach($ids as $id) {
                                $id = intval($id);
                                mysqli_query($koneksi, "DELETE FROM jawaban_ujian WHERE siswa_id = $id");
                                mysqli_query($koneksi, "DELETE FROM kelas_siswa WHERE siswa_id = $id");
                                mysqli_query($koneksi, "DELETE FROM siswa WHERE id = $id");
                            }
                        } else {
                            $ids_string = implode(',', array_map('intval', $ids));
                            $sql = "DELETE FROM $table WHERE id IN ($ids_string)";
                            mysqli_query($koneksi, $sql);
                        }
                    }
                    break;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand">Admin Dashboard</a>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>
    <div class="container mt-3">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Database Management</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="delete_database.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete the database? This action cannot be undone.');">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete Database
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">Data Tables</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach($tables as $table): ?>
                        <a href="?table=<?= $table ?>" class="list-group-item list-group-item-action <?= isset($_GET['table']) && $_GET['table'] == $table ? 'active' : '' ?>">
                            <i class="fas fa-table me-2"></i><?= ucfirst($table) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <?php if(isset($_GET['table'])): 
                    $table = $_GET['table'];
                    $result = mysqli_query($koneksi, "SELECT * FROM $table");
                    $columns = mysqli_fetch_fields($result);
                ?>
                    <div class="card shadow">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary"><i class="fas fa-database me-2"></i><?= ucfirst($table) ?></h5>
                            <div>
                                <button type="button" class="btn btn-outline-danger me-2" id="bulkDeleteBtn" disabled>
                                    <i class="fas fa-trash me-1"></i>Hapus Terpilih
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="fas fa-plus me-1"></i>Tambah Data
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dataTable" class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <?php foreach($columns as $column): ?>
                                                <th><?= ucfirst($column->name) ?></th>
                                            <?php endforeach; ?>
                                            <th width="130">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input row-checkbox" 
                                                    value="<?= $row['id'] ?>">
                                            </td>
                                            <?php foreach($columns as $column): ?>
                                                <td><?= $row[$column->name] ?></td>
                                            <?php endforeach; ?>
                                            <td>
                                                <button class="btn btn-sm btn-warning edit-btn" 
                                                        data-id="<?= $row['id'] ?>"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <br> <br>
                                                <button class="btn btn-sm btn-danger delete-btn"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>



                    <!-- Add Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Multiple</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="bulk_delete">
                    <input type="hidden" name="table" value="<?= $table ?>">
                    <div id="selectedIdsContainer"></div>
                    <p>Apakah Anda yakin ingin menghapus semua data yang dipilih?</p>
                    <p class="text-danger">Jumlah data terpilih: <span id="selectedCount">0</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Semua</button>
                </div>
            </form>
        </div>
    </div>
</div>

                    <!-- Add Modal -->
                    <div class="modal fade" id="addModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tambah Data</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="table" value="<?= $table ?>">
                                        <?php foreach($columns as $column): ?>
                                            <?php if($column->name != 'id'): ?>
                                            <div class="mb-3">
                                                <label class="form-label"><?= $column->name ?></label>
                                                <input type="text" name="<?= $column->name ?>" class="form-control" required>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Data</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="table" value="<?= $table ?>">
                                        <input type="hidden" name="id" id="edit-id">
                                        <?php foreach($columns as $column): ?>
                                            <?php if($column->name != 'id'): ?>
                                            <div class="mb-3">
                                                <label class="form-label"><?= $column->name ?></label>
                                                <input type="text" name="<?= $column->name ?>" 
                                                       id="edit-<?= $column->name ?>" class="form-control" required>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="table" value="<?= $table ?>">
                                        <input type="hidden" name="id" id="delete-id">
                                        <p>Apakah Anda yakin ingin menghapus data ini?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert alert-info">
                        Pilih tabel untuk melihat dan mengelola data
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#dataTable').DataTable();

        // Handle Edit Button
        $('.edit-btn').click(function() {
            var row = $(this).closest('tr');
            var id = $(this).data('id');
            $('#edit-id').val(id);
            
            row.find('td').each(function(index) {
                if(index < row.find('td').length - 1) { // Skip action column
                    var fieldName = $('#dataTable thead th').eq(index).text();
                    $('#edit-' + fieldName).val($(this).text());
                }
            });
        });

        // Handle Delete Button
        $('.delete-btn').click(function() {
            var id = $(this).data('id');
            $('#delete-id').val(id);
        });
    });

    $(document).ready(function() {
    // Handle Select All checkbox
    $('#selectAll').change(function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkDeleteButton();
    });

    // Handle individual checkboxes
    $('.row-checkbox').change(function() {
        updateBulkDeleteButton();
        // Update select all checkbox
        let allChecked = $('.row-checkbox:not(:checked)').length === 0;
        $('#selectAll').prop('checked', allChecked);
    });

    // Update bulk delete button state
    function updateBulkDeleteButton() {
        let checkedCount = $('.row-checkbox:checked').length;
        $('#bulkDeleteBtn').prop('disabled', checkedCount === 0);
        $('#selectedCount').text(checkedCount);
    }

    // Handle bulk delete button click
    $('#bulkDeleteBtn').click(function() {
        let selectedIds = $('.row-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        // Create hidden inputs for selected IDs
        let inputs = selectedIds.map(id => 
            `<input type="hidden" name="selected_ids[]" value="${id}">`
        ).join('');
        
        $('#selectedIdsContainer').html(inputs);
        $('#bulkDeleteModal').modal('show');
    });
});
    </script>
</body>
</html>