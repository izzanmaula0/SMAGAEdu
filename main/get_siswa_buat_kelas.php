<?php
require "koneksi.php";

if(isset($_GET['tingkat'])) {
    $tingkat = mysqli_real_escape_string($koneksi, $_GET['tingkat']);
    
    $query = "SELECT * FROM siswa WHERE tingkat = ? ORDER BY nama ASC";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $tingkat);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        // Style untuk tampilan siswa
        echo '<style>
            .student-item {
                display: flex;
                align-items: center;
                padding: 10px 12px;
                margin-bottom: 8px;
                border-radius: 8px;
                border: 1px solid #e9ecef;
                transition: all 0.2s ease;
                background-color: white;
            }
            .student-item:hover {
                background-color: #f8f9fa;
                border-color: #da7756;
            }
            .student-info {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
            }
            .form-check-input.siswa-checkbox {
                margin-top: 0;
                flex-shrink: 0;
            }
            .student-name {
                margin-bottom: 0;
                font-size: 14px;
                font-weight: 500;
            }
            .student-detail {
                font-size: 12px;
                color: #6c757d;
            }
        </style>';
        
        while($siswa = mysqli_fetch_assoc($result)) {
            ?>
            <div class="student-item">
                <div class="student-info">
                    <input class="form-check-input siswa-checkbox" type="checkbox" 
                           name="siswa_ids[]" value="<?php echo $siswa['id']; ?>"
                           id="siswa_<?php echo $siswa['id']; ?>">
                    <label class="student-name" for="siswa_<?php echo $siswa['id']; ?>">
                        <?php echo htmlspecialchars($siswa['nama']); ?>
                        <div class="student-detail">
                            <?php echo htmlspecialchars($siswa['username']); ?>
                        </div>
                    </label>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="text-center py-4 text-muted">
                <i class="bi bi-person-x fs-4 mb-2 d-block"></i>
                <p class="mb-0 small">Tidak ada siswa untuk tingkat kelas ini</p>
              </div>';
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo '<div class="text-center py-4 text-muted">
            <p class="mb-0 small">Parameter tingkat tidak ditemukan</p>
          </div>';
}
?>