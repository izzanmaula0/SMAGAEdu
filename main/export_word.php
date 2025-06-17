<?php
session_start();
require "koneksi.php";
require 'vendor/autoload.php'; // Memuat library PHPWord

// Pastikan user sudah login dan memiliki akses
if (!isset($_SESSION['userid']) || ($_SESSION['level'] != 'guru' && $_SESSION['level'] != 'admin')) {
    header("Location: index.php");
    exit();
}

// Pastikan ada ujian_id dan jenis sekolah
if (!isset($_GET['ujian_id']) || !isset($_GET['jenis'])) {
    header("Location: ujian_guru.php");
    exit();
}

$ujian_id = $_GET['ujian_id'];
$jenis_sekolah = $_GET['jenis']; // smp atau sma
$userid = $_SESSION['userid'];

// Ambil data ujian
$query_ujian = "SELECT u.*, k.tingkat, k.nama_kelas 
                FROM ujian u 
                INNER JOIN kelas k ON u.kelas_id = k.id 
                WHERE u.id = '$ujian_id' AND u.guru_id = '$userid'";
$result_ujian = mysqli_query($koneksi, $query_ujian);

// Cek apakah ujian ditemukan dan milik guru tersebut 
if (mysqli_num_rows($result_ujian) == 0) {
    header("Location: ujian_guru.php");
    exit();
}

$ujian = mysqli_fetch_assoc($result_ujian);

// Ambil data soal beserta deskripsinya
$soal_dengan_desc = [];
$soal_tanpa_desc = [];

// Ambil deskripsi soal
$query_descriptions = "SELECT * FROM soal_descriptions WHERE ujian_id = '$ujian_id' ORDER BY id ASC";
$result_descriptions = mysqli_query($koneksi, $query_descriptions);
$descriptions = [];
while ($desc = mysqli_fetch_assoc($result_descriptions)) {
    $descriptions[$desc['id']] = $desc;
    $soal_dengan_desc[$desc['id']] = [
        'deskripsi' => $desc,
        'soal' => []
    ];
}

// Ambil semua soal
$query_soal = "SELECT * FROM bank_soal WHERE ujian_id = '$ujian_id' ORDER BY id ASC";
$result_soal = mysqli_query($koneksi, $query_soal);

// Kelompokkan soal berdasarkan description_id
while ($soal = mysqli_fetch_assoc($result_soal)) {
    if (!empty($soal['description_id']) && isset($soal_dengan_desc[$soal['description_id']])) {
        $soal_dengan_desc[$soal['description_id']]['soal'][] = $soal;
    } else {
        $soal_tanpa_desc[] = $soal;
    }
}

// Hitung total soal
$total_soal = count($soal_tanpa_desc);
foreach ($soal_dengan_desc as $desc_id => $data) {
    $total_soal += count($data['soal']);
}

// Buat dokumen PHPWord
$phpWord = new \PhpOffice\PhpWord\PhpWord();

$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

// Tambahkan style
$sectionStyle = [
    'orientation' => 'portrait',
    'marginTop' => 709,
    'marginRight' => 1000,
    'marginBottom' => 1000,
    'marginLeft' => 1000,
];

// Buat section baru
$section = $phpWord->addSection($sectionStyle);

// Tambahkan header dengan logo sekolah (SMP)
$header = $section->addHeader();
if ($jenis_sekolah == 'smp') {
    // Header untuk SMP
    $header->addImage(
        'assets/logo_smp.png', // Pastikan file logo ada
        [
            'width' => '480', // Fixed width 
            'height' => '85', // Height will scale proportionally
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]
    );
} else {
    // Header untuk SMA
    $header->addImage(
        'assets/logo_sma.png', // Pastikan file logo ada
        [
            'width' => '480', // Fixed width 
            'height' => '85', // Height will scale proportionally
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]
    );
}



// Tambahkan judul ujian
$section->addText(
    strtoupper($ujian['judul']),
    ['bold' => true, 'size' => 14],
    ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 200]
);

// Buat tabel informasi ujian
$tableStyle = [
    'borderSize' => 6,
    'borderColor' => '000000',
    'cellMargin' => 80
];
$firstRowStyle = ['bgColor' => 'EEEEEE'];
$phpWord->addTableStyle('InfoTable', $tableStyle, $firstRowStyle);
$table = $section->addTable('InfoTable');

// Baris 1: Mata Pelajaran
$table->addRow();
$table->addCell(3000)->addText('Mata Pelajaran', ['bold' => true]);
$table->addCell(7000)->addText(': ' . $ujian['mata_pelajaran']);

// Baris 2: Kelas
$table->addRow();
$table->addCell(3000)->addText('Kelas', ['bold' => true]);
$table->addCell(7000)->addText(': ' . $ujian['tingkat'] . ' ' . $ujian['nama_kelas']);

// Baris 3: Alokasi Waktu
$table->addRow();
$table->addCell(3000)->addText('Alokasi Waktu', ['bold' => true]);
$table->addCell(7000)->addText(': ' . $ujian['durasi'] . ' Menit');

// Baris 4: Jumlah Soal
$table->addRow();
$table->addCell(3000)->addText('Jumlah Soal', ['bold' => true]);
$table->addCell(7000)->addText(': ' . $total_soal . ' Soal');

// Baris 5: Bentuk Soal
$table->addRow();
$table->addCell(3000)->addText('Bentuk Soal', ['bold' => true]);
$table->addCell(7000)->addText(': Pilihan Ganda');

// Tambahkan sedikit spasi
$section->addTextBreak(1);

// Tambahkan instruksi
$section->addText(
    'Pilihan Ganda: Pilihlah salah satu jawaban yang dianggap paling benar',
    ['bold' => true, 'size' => 12],
    ['spaceAfter' => 200]
);

// Tambahkan soal-soal
$nomor_soal = 1;

// Tambahkan soal dengan deskripsi dulu
foreach ($soal_dengan_desc as $desc_id => $data) {
    if (empty($data['soal'])) continue;

    // Tambahkan deskripsi
    $section->addText(
        $data['deskripsi']['title'],
        ['bold' => true, 'size' => 12],
        ['spaceAfter' => 100]
    );

    $section->addText(
        $data['deskripsi']['content'],
        ['size' => 11],
        ['spaceAfter' => 200]
    );

    // Tambahkan soal-soal di bawah deskripsi ini
    foreach ($data['soal'] as $soal) {
        // Tambahkan soal
        $section->addText(
            $nomor_soal . '. ' . $soal['pertanyaan'],
            ['size' => 11],
            ['spaceAfter' => 100]
        );

        // Tambahkan gambar jika ada
        if (!empty($soal['gambar_soal'])) {
            // Ambil URL gambar dan konversi ke path lokal jika perlu
            $gambarUrl = $soal['gambar_soal'];
            if (file_exists($gambarUrl)) {
                $section->addImage(
                    $gambarUrl,
                    ['width' => 200, 'height' => 150, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
                );
            }
        }

        // Tambahkan pilihan jawaban
        if ($soal['jenis_soal'] == 'pilihan_ganda') {
            $section->addText('A. ' . $soal['jawaban_a'], ['size' => 11]);
            $section->addText('B. ' . $soal['jawaban_b'], ['size' => 11]);
            $section->addText('C. ' . $soal['jawaban_c'], ['size' => 11]);
            $section->addText('D. ' . $soal['jawaban_d'], ['size' => 11]);
        }

        // Tambahkan spasi
        $section->addTextBreak(1);

        $nomor_soal++;
    }
}

// Tambahkan soal tanpa deskripsi
foreach ($soal_tanpa_desc as $soal) {
    // Tambahkan soal
    $section->addText(
        $nomor_soal . '. ' . $soal['pertanyaan'],
        ['size' => 11],
        ['spaceAfter' => 100]
    );

    // Tambahkan gambar jika ada
    if (!empty($soal['gambar_soal'])) {
        // Ambil URL gambar dan konversi ke path lokal jika perlu
        $gambarUrl = $soal['gambar_soal'];
        if (file_exists($gambarUrl)) {
            $section->addImage(
                $gambarUrl,
                ['width' => 200, 'height' => 150, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
        }
    }

    // Tambahkan pilihan jawaban
    if ($soal['jenis_soal'] == 'pilihan_ganda') {
        $section->addText('A. ' . $soal['jawaban_a'], ['size' => 11]);
        $section->addText('B. ' . $soal['jawaban_b'], ['size' => 11]);
        $section->addText('C. ' . $soal['jawaban_c'], ['size' => 11]);
        $section->addText('D. ' . $soal['jawaban_d'], ['size' => 11]);
    }

    // Tambahkan spasi
    $section->addTextBreak(1);

    $nomor_soal++;
}


// Buat Section baru untuk Lembar Jawaban
$answerSection = $phpWord->addSection($sectionStyle);

// Tambahkan header untuk lembar jawaban
$answerHeader = $answerSection->addHeader();
if ($jenis_sekolah == 'smp') {
    // Header untuk SMP
    $answerHeader->addImage(
        'assets/logo_smp.png', // Pastikan file logo ada
        [
            'width' => '480', // Fixed width 
            'height' => '85', // Height will scale proportionally
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]
    );
} else {
    // Header untuk SMA
    $answerHeader->addImage(
        'assets/logo_sma.png', // Pastikan file logo ada
        [
            'width' => '480', // Fixed width 
            'height' => '85', // Height will scale proportionally
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]
    );
}

// Tambahkan garis pemisah di header lembar jawaban
$answerHeader->addLine(['weight' => 1, 'width' => 450, 'height' => 0]);

// Tambahkan judul lembar jawaban
$answerSection->addText(
    'LEMBAR JAWABAN ' . strtoupper($ujian['judul']),
    ['bold' => true, 'size' => 14],
    ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 200]
);

// Buat tabel informasi yang sama untuk lembar jawaban
$answerTable = $answerSection->addTable('InfoTable');

// Baris 1: Mata Pelajaran
$answerTable->addRow();
$answerTable->addCell(3000)->addText('Mata Pelajaran', ['bold' => true]);
$answerTable->addCell(7000)->addText(': ' . $ujian['mata_pelajaran']);

// Baris 2: Kelas
$answerTable->addRow();
$answerTable->addCell(3000)->addText('Kelas', ['bold' => true]);
$answerTable->addCell(7000)->addText(': ' . $ujian['tingkat'] . ' ' . $ujian['nama_kelas']);

// Baris 3: Alokasi Waktu
$answerTable->addRow();
$answerTable->addCell(3000)->addText('Alokasi Waktu', ['bold' => true]);
$answerTable->addCell(7000)->addText(': ' . $ujian['durasi'] . ' Menit');

// Baris 4: Jumlah Soal
$answerTable->addRow();
$answerTable->addCell(3000)->addText('Jumlah Soal', ['bold' => true]);
$answerTable->addCell(7000)->addText(': ' . $total_soal . ' Soal');

// Baris 5: Bentuk Soal
$answerTable->addRow();
$answerTable->addCell(3000)->addText('Bentuk Soal', ['bold' => true]);
$answerTable->addCell(7000)->addText(': Pilihan Ganda');

// Tambahkan sedikit spasi
$answerSection->addTextBreak(1);

// Tambahkan instruksi
$answerSection->addText(
    'KUNCI JAWABAN PILIHAN GANDA',
    ['bold' => true, 'size' => 12],
    ['spaceAfter' => 200]
);

// Buat tabel untuk jawaban
$columnCount = 5; // Jumlah kolom
$rowCount = ceil($total_soal / $columnCount); // Jumlah baris

$answerGridTable = $answerSection->addTable();

// Reset nomor soal
$nomor_soal = 1;

// Ambil kunci jawaban untuk setiap soal
$kunci_jawaban = [];

// Soal dengan deskripsi
foreach ($soal_dengan_desc as $desc_id => $data) {
    foreach ($data['soal'] as $soal) {
        if ($soal['jenis_soal'] == 'pilihan_ganda') {
            $kunci_jawaban[$nomor_soal] = $soal['jawaban_benar'];
            $nomor_soal++;
        }
    }
}

// Soal tanpa deskripsi
foreach ($soal_tanpa_desc as $soal) {
    if ($soal['jenis_soal'] == 'pilihan_ganda') {
        $kunci_jawaban[$nomor_soal] = $soal['jawaban_benar'];
        $nomor_soal++;
    }
}

// Buat tabel jawaban
for ($i = 0; $i < $rowCount; $i++) {
    $answerGridTable->addRow();

    for ($j = 0; $j < $columnCount; $j++) {
        $index = $i * $columnCount + $j + 1;

        if ($index <= $total_soal) {
            $jawaban = isset($kunci_jawaban[$index]) ? $kunci_jawaban[$index] : '-';
            $answerGridTable->addCell(1800)->addText(
                $index . '. ' . $jawaban,
                ['bold' => true],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
        } else {
            $answerGridTable->addCell(1800)->addText('');
        }
    }
}


// Simpan file ke tmp directory
$filename = 'Ujian_' . preg_replace('/[^A-Za-z0-9]/', '_', $ujian['judul']) . '_' . date('YmdHis') . '.docx';
$filepath = 'tmp/' . $filename;

// Pastikan direktori tmp ada
if (!is_dir('tmp')) {
    mkdir('tmp', 0777, true);
}

// Simpan file
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save($filepath);

// Unduh file
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);

// Hapus file setelah diunduh (opsional)
unlink($filepath);
exit;
