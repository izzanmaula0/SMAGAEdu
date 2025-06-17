<?php
require "koneksi.php";

$siswa_id = $_GET['siswa_id'] ?? null;
$semesters = $_GET['semesters'] ?? 'all';

$limit = ($semesters === '3') ? "LIMIT 3" : "";

$query = "SELECT semester, tahun_ajaran,
          (nilai_akademik + keaktifan + pemahaman)/3 as akademik,
          (kehadiran_ibadah + kualitas_ibadah + pemahaman_agama)/3 as ibadah,
          (minat_bakat + prestasi + keaktifan_ekskul)/3 as pengembangan,
          created_at
          FROM pg 
          WHERE siswa_id = ?
          ORDER BY created_at DESC {$limit}";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [
    'labels' => [],
    'akademik' => [],
    'ibadah' => [],
    'pengembangan' => []
];

while ($row = mysqli_fetch_assoc($result)) {
    $data['labels'][] = "Semester " . $row['semester'] . " " . $row['tahun_ajaran'];
    $data['akademik'][] = $row['akademik'];
    $data['ibadah'][] = $row['ibadah'];
    $data['pengembangan'][] = $row['pengembangan'];
}

header('Content-Type: application/json');
echo json_encode($data);