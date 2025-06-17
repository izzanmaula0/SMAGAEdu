<?php
include 'includes/session_config.php';
require "koneksi.php";
require_once 'groq_config_ujian.php';

// Validasi session
validateSession();

if (!isset($_SESSION['userid']) || !isset($_POST['ujian_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter tidak lengkap']);
    exit();
}

$ujian_id = $_POST['ujian_id'];
$userid = $_SESSION['userid'];

error_log("Getting exam results for ujian_id: $ujian_id, userid: $userid");

// Ambil ID siswa dari username
$query_get_siswa_id = "SELECT id FROM siswa WHERE username = ?";
$stmt_get_siswa = $koneksi->prepare($query_get_siswa_id);
$stmt_get_siswa->bind_param("s", $userid);
$stmt_get_siswa->execute();
$result_get_siswa = $stmt_get_siswa->get_result();
$siswa_data = $result_get_siswa->fetch_assoc();

if (!$siswa_data) {
    http_response_code(404);
    echo json_encode(['error' => 'Siswa tidak ditemukan']);
    exit();
}

$siswa_id = $siswa_data['id'];

try {
    // Ambil data ujian
    $query_ujian = "SELECT u.*, k.nama_kelas FROM ujian u 
                    LEFT JOIN kelas k ON u.kelas_id = k.id 
                    WHERE u.id = ?";
    $stmt_ujian = $koneksi->prepare($query_ujian);
    $stmt_ujian->bind_param("i", $ujian_id);
    $stmt_ujian->execute();
    $result_ujian = $stmt_ujian->get_result();
    $data_ujian = $result_ujian->fetch_assoc();

    if (!$data_ujian) {
        throw new Exception('Data ujian tidak ditemukan');
    }

    // Hitung total soal untuk ujian ini
    $query_total_soal = "SELECT COUNT(*) as total FROM bank_soal WHERE ujian_id = ?";
    $stmt_total = $koneksi->prepare($query_total_soal);
    $stmt_total->bind_param("i", $ujian_id);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_soal = $result_total->fetch_assoc()['total'];

    if ($total_soal == 0) {
        throw new Exception('Tidak ada soal untuk ujian ini');
    }

    // Hitung jawaban benar dan salah secara real-time
    $query_statistik = "SELECT 
    COUNT(CASE WHEN ju.jawaban = bs.jawaban_benar THEN 1 END) as benar,
    COUNT(CASE WHEN ju.jawaban != bs.jawaban_benar AND ju.jawaban IS NOT NULL THEN 1 END) as salah,
    COUNT(ju.id) as total_dijawab
    FROM jawaban_ujian ju
    JOIN bank_soal bs ON ju.soal_id = bs.id
    WHERE ju.ujian_id = ? AND ju.siswa_id = ?";

    $stmt_statistik = $koneksi->prepare($query_statistik);
    $stmt_statistik->bind_param("ii", $ujian_id, $siswa_id);
    $stmt_statistik->execute();
    $result_statistik = $stmt_statistik->get_result();
    $statistik = $result_statistik->fetch_assoc();

    // Hitung nilai dan statistik
    $benar = (int)$statistik['benar'];
    $salah = (int)$statistik['salah'];
    $total_dijawab = (int)$statistik['total_dijawab'];
    $tidak_dijawab = $total_soal - $total_dijawab;
    $nilai = $total_soal > 0 ? ($benar / $total_soal) * 100 : 0;

    // Buat data hasil ujian
    $hasil_ujian = [
        'nilai' => $nilai,
        'jumlah_benar' => $benar,
        'jumlah_salah' => $salah,
        'tidak_dijawab' => $tidak_dijawab
    ];

    // Ambil jawaban siswa dari tabel jawaban_ujian dan join dengan bank_soal
    $query_jawaban = "SELECT ju.*, bs.pertanyaan, bs.jawaban_a, bs.jawaban_b, bs.jawaban_c, bs.jawaban_d, bs.jawaban_benar
                      FROM jawaban_ujian ju
                      JOIN bank_soal bs ON ju.soal_id = bs.id
                      WHERE ju.ujian_id = ? AND ju.siswa_id = ?
                      ORDER BY ju.soal_id";
    $stmt_jawaban = $koneksi->prepare($query_jawaban);
    $stmt_jawaban->bind_param("ii", $ujian_id, $siswa_id);
    $stmt_jawaban->execute();
    $result_jawaban = $stmt_jawaban->get_result();

    $jawaban_detail = [];
    $benar = 0;
    $salah = 0;

    while ($row = $result_jawaban->fetch_assoc()) {
        $is_correct = ($row['jawaban'] === $row['jawaban_benar']);
        if ($is_correct) {
            $benar++;
        } else {
            $salah++;
        }

        $jawaban_detail[] = [
            'pertanyaan' => $row['pertanyaan'],
            'jawaban_siswa' => $row['jawaban'],
            'jawaban_benar' => $row['jawaban_benar']
        ];
    }

    // Gunakan data dari hasil_ujian untuk konsistensi
    $analisis_data = [
        'ujian' => [
            'judul' => $data_ujian['judul'],
            'deskripsi' => $data_ujian['deskripsi'] ?? '',
            'mata_pelajaran' => $data_ujian['mata_pelajaran'] ?? 'Umum',
            'kelas' => $data_ujian['nama_kelas'] ?? 'Semua Kelas'
        ],
        'hasil' => [
            'nilai' => $hasil_ujian['nilai'],
            'total_soal' => $hasil_ujian['jumlah_benar'] + $hasil_ujian['jumlah_salah'] + $hasil_ujian['tidak_dijawab'],
            'benar' => $hasil_ujian['jumlah_benar'],
            'salah' => $hasil_ujian['jumlah_salah'],
            'tidak_dijawab' => $hasil_ujian['tidak_dijawab'],
            'persentase' => $hasil_ujian['nilai']
        ],
        'jawaban_detail' => $jawaban_detail,
        'siswa' => [
            'nama' => $_SESSION['nama']
        ]
    ];

    echo json_encode([
        'success' => true,
        'data' => $analisis_data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
