<?php
session_start();
require_once "koneksi.php";

// Simple check for login
if (!isset($_SESSION['userid'])) {
    echo "Please login first";
    exit;
}

// Get current status
$status = [];
$debug = [];

// Check database structure
try {
    $table_query = "SHOW TABLES LIKE 'presentasi_aktif'";
    $result = mysqli_query($koneksi, $table_query);
    $debug['table_exists'] = mysqli_num_rows($result) > 0;
    
    if ($debug['table_exists']) {
        $cols_query = "SHOW COLUMNS FROM presentasi_aktif";
        $result = mysqli_query($koneksi, $cols_query);
        $debug['columns'] = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $debug['columns'][] = $row;
        }
    }
} catch (Exception $e) {
    $debug['db_error'] = $e->getMessage();
}

// Handle test start presentation
if (isset($_POST['start'])) {
    $kelas_id = $_POST['kelas_id'];
    $file_path = $_POST['file_path'];
    $total_slides = intval($_POST['total_slides']);
    
    try {
        // Check if exists
        $check_query = "SELECT * FROM presentasi_aktif WHERE kelas_id = ?";
        $stmt = mysqli_prepare($koneksi, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $kelas_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $update_query = "UPDATE presentasi_aktif SET 
                          file_path = ?, 
                          current_slide = 1, 
                          total_slides = ?, 
                          active = 1,
                          updated_at = NOW() 
                          WHERE kelas_id = ?";
            $stmt = mysqli_prepare($koneksi, $update_query);
            mysqli_stmt_bind_param($stmt, "sis", $file_path, $total_slides, $kelas_id);
            $status['action'] = "update";
        } else {
            $insert_query = "INSERT INTO presentasi_aktif 
                          (kelas_id, file_path, current_slide, total_slides, active, updated_at) 
                          VALUES (?, ?, 1, ?, 1, NOW())";
            $stmt = mysqli_prepare($koneksi, $insert_query);
            mysqli_stmt_bind_param($stmt, "ssi", $kelas_id, $file_path, $total_slides);
            $status['action'] = "insert";
        }
        
        $result = mysqli_stmt_execute($stmt);
        $status['success'] = $result;
        $status['error'] = mysqli_error($koneksi);
    } catch (Exception $e) {
        $status['success'] = false;
        $status['error'] = $e->getMessage();
    }
}

// Get current presentations
$presentations = [];
try {
    $query = "SELECT * FROM presentasi_aktif ORDER BY updated_at DESC";
    $result = mysqli_query($koneksi, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $presentations[] = $row;
    }
} catch (Exception $e) {
    $debug['presentations_error'] = $e->getMessage();
}

// Test endpoints
$endpoints = [
    'check_presentation' => 'ajax/check_presentation.php?kelas_id=TEST',
    'start_presentation' => 'ajax/start_presentation.php',
    'update_slide' => 'ajax/update_slide.php',
    'end_presentation' => 'ajax/end_presentation.php'
];

$endpoint_results = [];
foreach ($endpoints as $name => $url) {
    $endpoint_results[$name] = file_exists($url) ? 'File exists' : 'File not found';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentation Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 900px; margin-top: 30px; margin-bottom: 50px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Presentation System Debug</h1>
        
        <div class="alert alert-info">
            <strong>User Info:</strong> ID: <?php echo $_SESSION['userid']; ?>, 
            Level: <?php echo $_SESSION['level']; ?>
        </div>
        
        <?php if (!empty($status)): ?>
            <div class="alert alert-<?php echo $status['success'] ? 'success' : 'danger'; ?>">
                <h5>Action Result</h5>
                <pre><?php print_r($status); ?></pre>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Database Check</div>
                    <div class="card-body">
                        <pre><?php print_r($debug); ?></pre>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">Endpoint Files</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($endpoint_results as $name => $result): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $name; ?>
                                <span class="badge <?php echo strpos($result, 'exists') !== false ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $result; ?>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Test Start Presentation</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Kelas ID</label>
                                <input type="text" class="form-control" name="kelas_id" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File Path</label>
                                <input type="text" class="form-control" name="file_path" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Slides</label>
                                <input type="number" class="form-control" name="total_slides" value="10" required>
                            </div>
                            <button type="submit" name="start" class="btn btn-primary">Start Presentation</button>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">Current Presentations</div>
                    <div class="card-body">
                        <?php if (empty($presentations)): ?>
                            <p class="text-muted">No presentations found</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Kelas ID</th>
                                            <th>Slide</th>
                                            <th>Active</th>
                                            <th>Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($presentations as $p): ?>
                                        <tr>
                                            <td><?php echo $p['kelas_id']; ?></td>
                                            <td><?php echo $p['current_slide']; ?>/<?php echo $p['total_slides']; ?></td>
                                            <td><?php echo $p['active'] ? 'Yes' : 'No'; ?></td>
                                            <td><?php echo $p['updated_at']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h4>Test AJAX Calls</h4>
            <div class="mb-3">
                <button class="btn btn-outline-primary" onclick="testCheckPresentation()">Test Check Presentation</button>
                <button class="btn btn-outline-success" onclick="testStartPresentation()">Test Start Presentation</button>
            </div>
            <div class="card">
                <div class="card-header">AJAX Test Results</div>
                <div class="card-body">
                    <pre id="ajaxResult">Results will appear here...</pre>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function testCheckPresentation() {
        const kelasId = prompt("Enter Kelas ID to check:", "TEST");
        if (!kelasId) return;
        
        document.getElementById('ajaxResult').textContent = "Loading...";
        
        fetch(`ajax/check_presentation.php?kelas_id=${kelasId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('ajaxResult').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('ajaxResult').textContent = `Error: ${error.message}`;
            });
    }
    
    function testStartPresentation() {
        const kelasId = prompt("Enter Kelas ID:", "TEST");
        if (!kelasId) return;
        
        const filePath = prompt("Enter File Path:", "uploads/sample.pdf");
        if (!filePath) return;
        
        const totalSlides = prompt("Enter Total Slides:", "10");
        
        document.getElementById('ajaxResult').textContent = "Loading...";
        
        const formData = new FormData();
        formData.append('kelas_id', kelasId);
        formData.append('file_path', filePath);
        formData.append('total_slides', totalSlides);
        
        fetch('ajax/start_presentation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('ajaxResult').textContent = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            document.getElementById('ajaxResult').textContent = `Error: ${error.message}`;
        });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>