<?php
require "koneksi.php";

if(isset($_GET['tingkat'])) {
    $tingkat = mysqli_real_escape_string($koneksi, $_GET['tingkat']);
    $kelas_id = isset($_GET['kelas_id']) ? mysqli_real_escape_string($koneksi, $_GET['kelas_id']) : '';
    
    // Jika kelas_id tidak ada, berikan pesan error
    if(empty($kelas_id)) {
        echo '<p class="text-danger">ID Kelas diperlukan</p>';
        exit();
    }
    
    $query = "SELECT * FROM siswa WHERE tingkat = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $tingkat);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        // Tambahkan style khusus untuk memperbaiki tampilan
        echo '<style>
            .student-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 12px;
                margin-bottom: 8px;
                border-radius: 8px;
                border: 1px solid #e9ecef;
                transition: all 0.2s ease;
            }
            .student-item:hover {
                background-color: #f8f9fa;
            }
            .student-info {
                display: flex;
                align-items: center;
                gap: 8px;
                flex: 1;
            }
            .form-check-input.siswa-checkbox {
                margin-top: 0;
                margin-right: 10px;
                flex-shrink: 0;
            }
            .student-name {
                margin-bottom: 0;
                font-size: 14px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 200px;
            }
            .badge {
                font-size: 10px;
                padding: 5px 8px;
                white-space: nowrap;
            }
        </style>';
        
        while($siswa = mysqli_fetch_assoc($result)) {
            // Periksa apakah siswa sudah ada dalam kelas
            $check_query = "SELECT id FROM kelas_siswa WHERE kelas_id = ? AND siswa_id = ?";
            $check_stmt = mysqli_prepare($koneksi, $check_query);
            mysqli_stmt_bind_param($check_stmt, "ii", $kelas_id, $siswa['id']);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            $is_in_class = mysqli_num_rows($check_result) > 0;
            ?>
            <div class="student-item" style="background-color: <?php echo $is_in_class ? '#f0f0f0' : 'white'; ?>;">
                <div class="student-info">
                    <input class="form-check-input siswa-checkbox" type="checkbox" 
                           name="siswa_ids[]" value="<?php echo $siswa['id']; ?>"
                           id="siswa_<?php echo $siswa['id']; ?>"
                           <?php echo $is_in_class ? 'disabled checked' : ''; ?>>
                    <label class="student-name" for="siswa_<?php echo $siswa['id']; ?>">
                        <?php echo htmlspecialchars($siswa['nama']); ?>
                    </label>
                </div>
                <?php if($is_in_class): ?>
                <span class="badge" style="background-color:rgb(219, 106, 68); color:white;">Sudah terdaftar</span>
                <?php endif; ?>
            </div>
            <?php
            mysqli_stmt_close($check_stmt);
        }
    } else {
        echo '<div class="alert alert-info text-center">Tidak ada siswa untuk tingkat ini</div>';
    }
}
?>