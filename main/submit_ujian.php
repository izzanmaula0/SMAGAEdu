<?php
include 'includes/session_config.php'; // Tambahkan ini di awal
require "koneksi.php";

validateSession();

// Ambil data dari POST
$ujian_id = $_POST['ujian_id'] ?? null;
$answers = json_decode($_POST['answers'], true);

if (!$ujian_id) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit();
}

// Ambil ID siswa dari username
$query_siswa = "SELECT id FROM siswa WHERE username = ?";
$stmt_siswa = $koneksi->prepare($query_siswa);
$stmt_siswa->bind_param("s", $_SESSION['userid']);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();
$siswa = $result_siswa->fetch_assoc();

if (!$siswa) {
    echo json_encode(['success' => false, 'error' => 'Siswa tidak ditemukan']);
    exit();
}

$siswa_id = $siswa['id'];

// Ambil jawaban sementara dari database
$query_jawaban = "SELECT soal_id, jawaban FROM jawaban_sementara WHERE ujian_id = ? AND siswa_id = ?";
$stmt_jawaban = $koneksi->prepare($query_jawaban);
$stmt_jawaban->bind_param("ii", $ujian_id, $siswa_id);
$stmt_jawaban->execute();
$result_jawaban = $stmt_jawaban->get_result();

// Simpan jawaban dari database ke array
$jawaban_array = [];
while ($row = $result_jawaban->fetch_assoc()) {
    $jawaban_array[$row['soal_id']] = $row['jawaban'];
}

try {
    // Mulai transaksi
    $koneksi->begin_transaction();

    // Bersihkan jawaban final sebelumnya jika ada
    $query_delete = "DELETE FROM jawaban_ujian WHERE ujian_id = ? AND siswa_id = ?";
    $stmt_delete = $koneksi->prepare($query_delete);
    $stmt_delete->bind_param("ii", $ujian_id, $siswa_id);
    $stmt_delete->execute();

    // Masukkan semua jawaban ke tabel jawaban_ujian
    $query_insert = "INSERT INTO jawaban_ujian (ujian_id, siswa_id, soal_id, jawaban) VALUES (?, ?, ?, ?)";
    $stmt_insert = $koneksi->prepare($query_insert);

    foreach ($jawaban_array as $soal_id => $jawaban) {
        $stmt_insert->bind_param("iiis", $ujian_id, $siswa_id, $soal_id, $jawaban);
        $stmt_insert->execute();
    }

    // Hapus jawaban sementara
    $query_cleanup = "DELETE FROM jawaban_sementara WHERE ujian_id = ? AND siswa_id = ?";
    $stmt_cleanup = $koneksi->prepare($query_cleanup);
    $stmt_cleanup->bind_param("ii", $ujian_id, $siswa_id);
    $stmt_cleanup->execute();

    // Hitung hasil ujian
    $query_soal = "SELECT id, jawaban_benar FROM bank_soal WHERE ujian_id = ?";
    $stmt_soal = $koneksi->prepare($query_soal);
    $stmt_soal->bind_param("i", $ujian_id);
    $stmt_soal->execute();
    $result_soal = $stmt_soal->get_result();

    $total_soal = 0;
    $jumlah_benar = 0;
    $jumlah_salah = 0;

    while ($soal = $result_soal->fetch_assoc()) {
        $total_soal++;
        $soal_id = $soal['id'];

        // Cek jawaban siswa
        if (isset($jawaban_array[$soal_id])) {
            $jawaban_siswa = $jawaban_array[$soal_id];
            $jawaban_benar = $soal['jawaban_benar'];

            if ($jawaban_siswa === $jawaban_benar) {
                $jumlah_benar++;
            } else {
                $jumlah_salah++;
            }
        } else {
            // Soal tidak dijawab dihitung salah
            $jumlah_salah++;
        }
    }

    $tidak_dijawab = $total_soal - count($jawaban_array);
    $nilai = $total_soal > 0 ? ($jumlah_benar / $total_soal) * 100 : 0;

    // Simpan hasil ujian ke tabel hasil_ujian
    $query_hasil = "INSERT INTO hasil_ujian (siswa_id, ujian_id, jumlah_benar, jumlah_salah, tidak_dijawab, nilai, waktu_submit) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt_hasil = $koneksi->prepare($query_hasil);
    $stmt_hasil->bind_param("iiiiid", $siswa_id, $ujian_id, $jumlah_benar, $jumlah_salah, $tidak_dijawab, $nilai);
    $stmt_hasil->execute();

    // Commit transaksi
    $koneksi->commit();

    echo json_encode([
        'success' => true,
        'redirect' => 'waiting_room.php?ujian_id=' . $ujian_id,
        'nilai' => round($nilai, 2),
        'benar' => $jumlah_benar,
        'total' => $total_soal
    ]);
} catch (Exception $e) {
    // Rollback jika terjadi error
    $koneksi->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
