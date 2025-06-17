<?php
session_start();
require "koneksi.php";

// Cek session - izinkan guru dan admin
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    header("Location: index.php");
    exit();
}

// Ambil ujian_id dari parameter URL
if (!isset($_GET['ujian_id'])) {
    die("ID Ujian tidak ditemukan");
}

$ujian_id = $_GET['ujian_id'];

// Ambil pengaturan export dari parameter URL
$bulatkan_nilai = isset($_GET['bulatkan']) ? (int)$_GET['bulatkan'] : 0;
$sembunyikan_siswa = isset($_GET['sembunyikan']) ? (int)$_GET['sembunyikan'] : 0;

// Query informasi ujian
$query_ujian = "SELECT u.*, k.tingkat 
                FROM ujian u
                JOIN kelas k ON u.kelas_id = k.id 
                WHERE u.id = '$ujian_id'";
$result_ujian = mysqli_query($koneksi, $query_ujian);

if (!$result_ujian || mysqli_num_rows($result_ujian) == 0) {
    die("Ujian tidak ditemukan");
}

$ujian = mysqli_fetch_assoc($result_ujian);
$ujian['judul'] = $ujian['judul'] ?? 'Judul Tidak Tersedia';
$ujian['mata_pelajaran'] = $ujian['mata_pelajaran'] ?? 'Mata Pelajaran Tidak Tersedia';
$ujian['tingkat'] = $ujian['tingkat'] ?? 'Tidak Diketahui';

// Total questions query
$query_total = "SELECT COUNT(*) as total FROM bank_soal WHERE ujian_id = '$ujian_id'";
$result_total = mysqli_query($koneksi, $query_total);
$total_questions = mysqli_fetch_assoc($result_total)['total'];

// Peserta query dengan pengaturan filter
$where_condition = "ks.kelas_id = '{$ujian['kelas_id']}'";

// Jika sembunyikan siswa yang tidak mengerjakan diaktifkan
if ($sembunyikan_siswa == 1) {
    $where_condition .= " AND EXISTS (
        SELECT 1 FROM jawaban_ujian ju2 
        WHERE ju2.siswa_id = s.id AND ju2.ujian_id = '$ujian_id'
    )";
}

$query_peserta = "
    SELECT 
        s.id as siswa_id,
        s.nama,
        s.nis,
        COUNT(DISTINCT ju.id) as attempted_questions,
        SUM(CASE WHEN ju.jawaban = bs.jawaban_benar THEN 1 ELSE 0 END) as correct_answers,
        SUM(CASE WHEN ju.jawaban != bs.jawaban_benar AND ju.jawaban IS NOT NULL THEN 1 ELSE 0 END) as wrong_answers
    FROM siswa s
    JOIN kelas_siswa ks ON s.id = ks.siswa_id
    LEFT JOIN jawaban_ujian ju ON s.id = ju.siswa_id AND ju.ujian_id = '$ujian_id'
    LEFT JOIN bank_soal bs ON bs.id = ju.soal_id
    WHERE $where_condition
    GROUP BY s.id
";
$result_peserta = mysqli_query($koneksi, $query_peserta);

if (!$result_peserta || mysqli_num_rows($result_peserta) == 0) {
    die("Tidak ada peserta ujian");
}

// Filename untuk hasil ekspor
$filename = "Hasil Ujian " . str_replace(" ", " ", $ujian['mata_pelajaran']) . " " . date("Y-m-d") . ".xls";

// Set header untuk download file CSV
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Buat HTML untuk Excel
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Hasil Ujian</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
echo '<body>';

// Judul
echo '<h3>Hasil Ujian ' . htmlspecialchars($ujian['mata_pelajaran']) . ' - ' . htmlspecialchars($ujian['judul']) . '</h3>';
echo '<p>Kelas ' . htmlspecialchars($ujian['tingkat']) . '</p>';

// Informasi pengaturan export
echo '<p style="font-size: 12px; color: #666;">';
echo 'Pengaturan Export: ';
if ($bulatkan_nilai == 1) {
    echo 'Nilai dibulatkan ';
} else {
    echo 'Nilai murni ';
}
if ($sembunyikan_siswa == 1) {
    echo '| Hanya siswa yang mengerjakan';
} else {
    echo '| Semua siswa';
}
echo '</p>';

// Tabel data
echo '<table border="1">';
echo '<tr style="background-color: #f3f3f3; font-weight: bold;">';
echo '<th>No</th>';
echo '<th>NIS</th>';
echo '<th>Nama Siswa</th>';
echo '<th>Status</th>';
echo '<th>Benar</th>';
echo '<th>Salah</th>';
echo '<th>Belum Dikerjakan</th>';
echo '<th>Nilai</th>';
echo '</tr>';

// Buat file pointer untuk output
$output = fopen('php://output', 'w');

// Encode output dengan UTF-8 BOM untuk support karakter khusus di Excel
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Tulis header file
$header = array("No", "Nama Siswa", "NIS", "Status", "Benar", "Salah", "Belum Dikerjakan", "Nilai");
fputcsv($output, $header);

// Tulis data peserta
$no = 1;
while ($peserta = mysqli_fetch_assoc($result_peserta)) {
    $unattempted = $total_questions - ($peserta['correct_answers'] + $peserta['wrong_answers']);
    $nilai = ($peserta['correct_answers'] / $total_questions) * 100;

    // Terapkan pembulatan jika diaktifkan
    if ($bulatkan_nilai == 1) {
        // Ambil bagian desimal
        $bagian_desimal = $nilai - floor($nilai);

        // Jika bagian desimal >= 0.6, bulatkan ke atas
        if ($bagian_desimal >= 0.6) {
            $nilai = ceil($nilai);
        } else {
            $nilai = floor($nilai);
        }
    }

    // Set status
    $status = ($peserta['attempted_questions'] > 0) ? 'Selesai' : 'Belum';

    echo '<tr>';
    echo '<td>' . $no . '</td>';
    echo '<td>' . htmlspecialchars($peserta['nis']) . '</td>';
    echo '<td>' . htmlspecialchars($peserta['nama']) . '</td>';
    echo '<td>' . $status . '</td>';
    echo '<td>' . $peserta['correct_answers'] . '</td>';
    echo '<td>' . $peserta['wrong_answers'] . '</td>';
    echo '<td>' . $unattempted . '</td>';
    
    // Format output nilai sesuai pengaturan
    if ($bulatkan_nilai == 1) {
        echo '<td>' . number_format($nilai, 0) . '</td>'; // Tanpa desimal jika dibulatkan
    } else {
        // Jika nilai murni, cek apakah memang ada desimal
        if ($nilai == floor($nilai)) {
            // Jika nilai bulat (misal: 80.0), tampilkan tanpa desimal
            echo '<td>' . number_format($nilai, 0) . '</td>';
        } else {
            // Jika ada desimal (misal: 85.5), tampilkan dengan 1 desimal
            echo '<td>' . number_format($nilai, 1) . '</td>';
        }
    }
    echo '</tr>';
    $no++;
}

echo '</table>';
echo '</body>';
echo '</html>';

// Tutup file pointer
fclose($output);
exit();
