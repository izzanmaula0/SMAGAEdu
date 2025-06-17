<?php
session_start();
require_once "koneksi.php";

// Simple authentication
if (!isset($_SESSION['userid'])) {
    echo "Anda harus login terlebih dahulu";
    exit;
}

// Handle form submission
if (isset($_POST['action'])) {
    $kelas_id = $_POST['kelas_id'] ?? '';
    
    if ($_POST['action'] === 'start') {
        // Start presentation
        $file_path = $_POST['file_path'] ?? '';
        $total_slides = intval($_POST['total_slides'] ?? 10);
        
        // Check if presentation exists
        $check_query = "SELECT * FROM presentasi_aktif WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $kelas_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            // Update
            $query = "UPDATE presentasi_aktif SET 
                      file_path = ?, 
                      current_slide = 1, 
                      total_slides = ?, 
                      active = 1,
                      updated_at = NOW() 
                      WHERE kelas_id = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sis", $file_path, $total_slides, $kelas_id);
        } else {
            // Insert
            $query = "INSERT INTO presentasi_aktif 
                      (kelas_id, file_path, current_slide, total_slides, active, updated_at) 
                      VALUES (?, ?, 1, ?, 1, NOW())";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $kelas_id, $file_path, $total_slides);
        }
        
        $success = mysqli_stmt_execute($stmt);
        $message = $success ? "Presentasi berhasil dimulai" : "Gagal memulai presentasi: " . mysqli_error($koneksi);
    }
    elseif ($_POST['action'] === 'update') {
        // Update slide
        $current_slide = intval($_POST['current_slide'] ?? 1);
        
        $query = "UPDATE presentasi_aktif SET 
                  current_slide = ?,
                  updated_at = NOW()
                  WHERE kelas_id = ? AND active = 1";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "is", $current_slide, $kelas_id);
        
        $success = mysqli_stmt_execute($stmt);
        $message = $success ? "Slide berhasil diperbarui" : "Gagal memperbarui slide: " . mysqli_error($koneksi);
    }
    elseif ($_POST['action'] === 'end') {
        // End presentation
        $query = "UPDATE presentasi_aktif SET 
                  active = 0,
                  updated_at = NOW()
                  WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $kelas_id);
        
        $success = mysqli_stmt_execute($stmt);
        $message = $success ? "Presentasi berhasil diakhiri" : "Gagal mengakhiri presentasi: " . mysqli_error($koneksi);
    }
}

// Get current status
$current_status = [];
if (isset($_GET['kelas_id'])) {
    $kelas_id = $_GET['kelas_id'];
    
    $query = "SELECT * FROM presentasi_aktif WHERE kelas_id = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $kelas_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $current_status = mysqli_fetch_assoc($result);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Presentation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 800px; margin-top: 50px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Test Presentation System</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">Current Status</div>
            <div class="card-body">
                <?php if (!empty($current_status)): ?>
                    <table class="table">
                        <tr>
                            <th>Kelas ID</th>
                            <td><?php echo htmlspecialchars($current_status['kelas_id']); ?></td>
                        </tr>
                        <tr>
                            <th>File Path</th>
                            <td><?php echo htmlspecialchars($current_status['file_path']); ?></td>
                        </tr>
                        <tr>
                            <th>Current Slide</th>
                            <td><?php echo $current_status['current_slide']; ?></td>
                        </tr>
                        <tr>
                            <th>Total Slides</th>
                            <td><?php echo $current_status['total_slides']; ?></td>
                        </tr>
                        <tr>
                            <th>Active</th>
                            <td>
                                <?php if ($current_status['active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td><?php echo $current_status['updated_at']; ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p>No presentation data found for this class</p>
                <?php endif; ?>
                
                <form method="get" class="mt-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="kelas_id" placeholder="Enter Kelas ID" value="<?php echo $_GET['kelas_id'] ?? ''; ?>" required>
                        <button type="submit" class="btn btn-primary">Check Status</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Start Presentation</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="start">
                    
                    <div class="mb-3">
                        <label class="form-label">Kelas ID</label>
                        <input type="text" class="form-control" name="kelas_id" value="<?php echo $_GET['kelas_id'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">File Path</label>
                        <input type="text" class="form-control" name="file_path" placeholder="uploads/sample.pdf" required>
                        <div class="form-text">Example: uploads/sample.pdf or uploads/presentation.pptx</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Total Slides</label>
                        <input type="number" class="form-control" name="total_slides" value="10" min="1" required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Start Presentation</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">Update Slide</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="mb-3">
                        <label class="form-label">Kelas ID</label>
                        <input type="text" class="form-control" name="kelas_id" value="<?php echo $_GET['kelas_id'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Slide</label>
                        <input type="number" class="form-control" name="current_slide" value="<?php echo $current_status['current_slide'] ?? 1; ?>" min="1" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Slide</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">End Presentation</div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="end">
                    
                    <div class="mb-3">
                        <label class="form-label">Kelas ID</label>
                        <input type="text" class="form-control" name="kelas_id" value="<?php echo $_GET['kelas_id'] ?? ''; ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-danger">End Presentation</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>