<?php
ob_start();
error_reporting(E_ALL);
// Di awal program
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'formula_debug.log');
error_log("================== MULAI PROSES BARU ==================");

require 'koneksi.php';
require 'vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

// Tambahkan fungsi untuk mendeteksi formula
function isFormulaFound($text) {
    // Deteksi karakter formula umum
    $specialChars = ['π', '∫', '∑', '∞', '±', '≤', '≥', '≠', '×', '÷', '→', '←', 'α', 'β', 'γ', 'δ', 'ε', 'λ', 'μ', 'Δ', '∂', '√'];
    
    foreach ($specialChars as $char) {
        if (strpos($text, $char) !== false) {
            return true;
        }
    }
    
    // Deteksi pola formula
    if (preg_match('/[a-zA-Z0-9]\^[a-zA-Z0-9]/', $text) || 
        preg_match('/[a-zA-Z0-9]_[a-zA-Z0-9]/', $text) || 
        preg_match('/[a-zA-Z0-9]+\/[a-zA-Z0-9]+/', $text) || 
        preg_match('/sqrt\([^)]+\)/', $text) ||
        strpos($text, '<m:oMath') !== false ||
        strpos($text, 'FORMULA_PLACEHOLDER_') !== false) {
        return true;
    }
    
    return false;
}

// Fungsi untuk mengekstrak XML mentah dari file DOCX
function extractDocxXml($docxPath, $xmlPath = 'word/document.xml')
{
    $content = '';

    // Tambahkan pengecekan apakah file ada
    if (empty($docxPath) || !file_exists($docxPath)) {
        error_log("File tidak ditemukan: " . ($docxPath ?? 'null'));
        return $content;
    }

    $zip = new ZipArchive();

    if ($zip->open($docxPath) === TRUE) {
        if (($index = $zip->locateName($xmlPath)) !== false) {
            $content = $zip->getFromIndex($index);
        }
        $zip->close();
    } else {
        error_log("Gagal membuka file ZIP: $docxPath");
    }

    return $content;
}

function cleanOptionText($text)
{
    // Hapus format seperti "**A. " atau simbol bullet lainnya
    $text = preg_replace('/\*+[A-D]\.\s*/i', '', $text);
    $text = preg_replace('/^[A-D]\.\s*/i', '', $text);
    return trim($text);
}

function extractCellText($cell)
{
    $text = '';
    $lastElementWasLineBreak = false;
    $debug = [];

    // Debug isi cell
    error_log("Extracting cell text, element count: " . count($cell->getElements()));

    foreach ($cell->getElements() as $elementIndex => $element) {
        $elementClass = get_class($element);
        $debug[] = "Element $elementIndex: $elementClass";

        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $subElementIndex => $textElement) {
                $subElementClass = get_class($textElement);
                $debug[] = "  SubElement $subElementIndex: $subElementClass";

                if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                    $elementText = $textElement->getText();

                    // Debug teks yang diekstrak
                    error_log("Teks dari elemen: " . substr($elementText, 0, 50));

                    // Cek apakah teks ini mengandung formula
                    if (isFormulaFound($elementText)) {
                        // Ganti dengan pesan error
                        $elementText = "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
                    }

                    $text .= $elementText;
                    $lastElementWasLineBreak = false;
                } elseif ($textElement instanceof \PhpOffice\PhpWord\Element\LineBreak) {
                    $text .= "\n";
                    $lastElementWasLineBreak = true;
                }
            }

            // Tambahkan line break di akhir TextRun jika tidak diakhiri dengan line break
            if (!$lastElementWasLineBreak) {
                $text .= "\n";
                $lastElementWasLineBreak = true;
            }
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
            if (!$lastElementWasLineBreak) {
                $text .= "\n";
                $lastElementWasLineBreak = true;
            }
        }
    }

    // Log debug
    error_log("Element breakdown: " . json_encode($debug));

    // Hapus line break terakhir jika ada
    $text = rtrim($text);

    // Dekode entitas HTML
    $text = html_entity_decode($text);

    // Deteksi formula dalam teks akhir
    if (isFormulaFound($text)) {
        $text = "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
    }

    // Log final text
    error_log("Hasil akhir teks: " . substr($text, 0, 100));

    return $text;
}

function saveImageFromElement($imageElement, $questionNumber, $optionLetter = null, $imageCount = 1)
{
    try {
        // Tentukan direktori berdasarkan jenis gambar (soal atau jawaban)
        $prefix = $optionLetter ? 'jawaban/' : 'soal/';
        $uploadDir = 'uploads/' . $prefix;

        // Buat direktori jika belum ada
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Buat nama file unik
        $identifier = $questionNumber;
        if ($optionLetter) {
            $identifier .= "_option_" . $optionLetter;
        }
        $filename = 'question_' . $identifier . '_img_' . $imageCount . '.png';
        $fullPath = $uploadDir . $filename;

        // Ambil dan simpan data gambar
        if (method_exists($imageElement, 'getImageString')) {
            $imageData = $imageElement->getImageString();
        } else if (method_exists($imageElement, 'getContent')) {
            $imageData = $imageElement->getContent();
        } else {
            // Gunakan refleksi untuk mengakses properti private
            $reflection = new ReflectionObject($imageElement);
            $property = $reflection->getProperty('imageString');
            $property->setAccessible(true);
            $imageData = $property->getValue($imageElement);
        }

        file_put_contents($fullPath, $imageData);

        return $fullPath;
    } catch (Exception $e) {
        error_log("Error saving image: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk mengekstrak gambar dari sel
function extractCellImages($cell, $questionNumber, $optionLetter = null)
{
    $images = [];
    $imageCount = 0;

    foreach ($cell->getElements() as $element) {
        // Cek gambar langsung dalam sel
        if ($element instanceof \PhpOffice\PhpWord\Element\Image) {
            $imageCount++;
            $filename = saveImageFromElement($element, $questionNumber, $optionLetter, $imageCount);
            if ($filename) {
                $images[] = $filename;
            }
        }
        // Cek gambar dalam TextRun
        else if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            foreach ($element->getElements() as $textElement) {
                if ($textElement instanceof \PhpOffice\PhpWord\Element\Image) {
                    $imageCount++;
                    $filename = saveImageFromElement($textElement, $questionNumber, $optionLetter, $imageCount);
                    if ($filename) {
                        $images[] = $filename;
                    }
                }
            }
        }
    }

    return $images;
}

function parseSoal($phpWord, $docxPath = null)
{
    $soalArray = [];
    $currentSoal = null;

    // Debug awal
    error_log("Memulai parseSoal");

    foreach ($phpWord->getSections() as $sectionIndex => $section) {
        error_log("Memproses section #" . $sectionIndex);

        foreach ($section->getElements() as $elementIndex => $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                error_log("Menemukan tabel di section #" . $sectionIndex);

                foreach ($element->getRows() as $rowIndex => $row) {
                    $cells = $row->getCells();
                    if (count($cells) < 2) continue;

                    $firstCell = extractCellText($cells[0]);
                    $firstCell = trim(preg_replace('/[^A-Z0-9]/i', '', $firstCell));

                    if (empty($firstCell)) continue;

                    if (is_numeric($firstCell)) {
                        if ($currentSoal !== null) {
                            $soalArray[] = $currentSoal;
                        }

                        // Ekstrak teks dan gambar
                        $soalText = extractCellText($cells[1]);

                        // Debug untuk soal
                        error_log("Menemukan soal #$firstCell dengan panjang teks: " . strlen($soalText));
                        error_log("Teks soal awal: " . substr($soalText, 0, 100));

                        // Jika soal kosong, coba ekstrak dengan cara alternatif
                        if (empty(trim($soalText))) {
                            error_log("Soal #$firstCell kosong, mencoba ekstraksi alternatif");
                            $soalText = extractCellTextAlternative($cells[1]);
                            error_log("Hasil ekstraksi alternatif: " . substr($soalText, 0, 100));
                        }

                        // Deteksi formula dalam soal
                        if (isFormulaFound($soalText)) {
                            error_log("Formula terdeteksi dalam soal #$firstCell");
                            $soalText = "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
                        }

                        $soalImages = extractCellImages($cells[1], $firstCell);

                        $currentSoal = [
                            'no' => $firstCell,
                            'soal' => $soalText,
                            'soal_images' => $soalImages,
                            'pilihan' => [],
                            'pilihan_images' => []
                        ];
                    } elseif ($currentSoal !== null && preg_match('/^[A-D]$/i', $firstCell)) {
                        $optionLetter = strtoupper($firstCell);
                        $optionText = extractCellText($cells[1]);
                        $optionText = cleanOptionText($optionText);
                        $optionImages = extractCellImages($cells[1], $currentSoal['no'], $optionLetter);

                        // Deteksi formula dalam opsi
                        if (isFormulaFound($optionText)) {
                            error_log("Formula terdeteksi dalam opsi $optionLetter soal #{$currentSoal['no']}");
                            $optionText = "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
                        }

                        $currentSoal['pilihan'][$optionLetter] = $optionText;
                        $currentSoal['pilihan_images'][$optionLetter] = $optionImages;
                    }
                }
            }
        }
    }

    if ($currentSoal !== null) {
        $soalArray[] = $currentSoal;
    }

    error_log("Total soal yang berhasil di-parse: " . count($soalArray));

    return $soalArray;
}

// Fungsi tambahan untuk ekstraksi teks alternatif
function extractCellTextAlternative($cell)
{
    // Coba ekstrak langsung dari XML struktur sel
    try {
        $text = '';

        // Gunakan refleksi untuk mengakses properti internal jika diperlukan
        $reflection = new ReflectionObject($cell);
        if ($reflection->hasProperty('element')) {
            $elementProp = $reflection->getProperty('element');
            $elementProp->setAccessible(true);
            $element = $elementProp->getValue($cell);

            // Konversi ke string XML
            $dom = new DOMDocument();
            $xml = $dom->saveXML($element);

            // Cek apakah ada oMath
            if (strpos($xml, '<m:oMath') !== false) {
                error_log("Ditemukan oMath dalam struktur XML sel");
                return "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
            }
        }

        // Jika masih kosong, gunakan metode biasa
        if (empty($text)) {
            $text = extractCellText($cell);
        }

        return $text;
    } catch (Exception $e) {
        error_log("Error dalam ekstraksi teks alternatif: " . $e->getMessage());
        return extractCellText($cell); // Fallback ke metode biasa
    }
}

function cleanText($text) {
    // LANGKAH 1: Simpan terlebih dahulu semua tag img
    $imgTags = [];
    $imgCount = 0;
    
    if (preg_match_all('/<img[^>]+>/i', $text, $matches)) {
        foreach ($matches[0] as $imgTag) {
            $placeholder = "___IMG_TAG_" . $imgCount . "___";
            $imgTags[$placeholder] = $imgTag;
            $text = str_replace($imgTag, $placeholder, $text);
            $imgCount++;
        }
    }
    
    // Deteksi formula
    if (isFormulaFound($text)) {
        return "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
    }
    
    // LANGKAH 3: Lakukan pembersihan normal pada teks
    // Hapus formatting bold dari Word dan tanda bintang lainnya
    $text = preg_replace('/\*+/', '', $text);
    
    // Hapus label pilihan (A., B., C., D.) di awal teks
    $text = preg_replace('/^[A-D]\.\s*/i', '', $text);
    
    // Normalisasi apostrof
    $text = str_replace(array('\'', "'", "'", '`', '´', "'", '&apos;', '&#039;'), "'", $text);
    
    // Decode HTML entities
    $text = html_entity_decode($text);
    
    // Normalisasi line breaks
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);
    $text = str_replace("\n", "<br>", $text);
    
    // Normalisasi spasi horizontal
    $text = preg_replace('/[ \t]+/', ' ', $text);
    
    // LANGKAH 4: Kembalikan tag img yang sudah disimpan
    foreach ($imgTags as $placeholder => $imgTag) {
        $text = str_replace($placeholder, $imgTag, $text);
    }
    
    return trim($text);
}

function parseKunciJawaban($phpWord)
{
    $kunciJawaban = [];

    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                foreach ($element->getRows() as $row) {
                    $cells = $row->getCells();
                    if (count($cells) < 2) continue;

                    $no = extractCellText($cells[0]);
                    $jawaban = extractCellText($cells[1]);

                    if (empty($no) || strpos($no, '---') !== false) continue;

                    if (is_numeric($no) && preg_match('/^[A-D]$/i', $jawaban)) {
                        $kunciJawaban[$no] = strtoupper($jawaban);
                    }
                }
            }
        }
    }
    return $kunciJawaban;
}

// Tambahkan di awal program untuk menganalisis struktur dokumen
function analyzeDocumentStructure($docxPath)
{
    error_log("Menganalisis struktur dokumen " . basename($docxPath));

    $zip = new ZipArchive();
    if ($zip->open($docxPath) === TRUE) {
        // Cek apakah ada file document.xml
        if ($zip->locateName('word/document.xml') !== false) {
            $content = $zip->getFromName('word/document.xml');

            // Hitung jumlah elemen oMath
            $oMathCount = substr_count($content, '<m:oMath');
            error_log("Jumlah elemen oMath dalam dokumen: $oMathCount");

            // Cek apakah ada tabel
            $tableCount = substr_count($content, '<w:tbl');
            error_log("Jumlah tabel dalam dokumen: $tableCount");

            // Cek apakah ada paragraf
            $paraCount = substr_count($content, '<w:p>');
            error_log("Jumlah paragraf dalam dokumen: $paraCount");
        }

        $zip->close();
    }
}

// Panggil di awal program
analyzeDocumentStructure($_FILES['fileSoal']['tmp_name']);

// Mulai bagian utama program
try {
    error_log("Starting process_word.php execution");

    if (!isset($_FILES['fileSoal']) || !isset($_FILES['fileJawaban'])) {
        echo json_encode(['status' => 'error', 'message' => 'Files not uploaded']);
        exit;
    }

    if ($_FILES['fileSoal']['error'] !== 0 || $_FILES['fileJawaban']['error'] !== 0) {
        echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
        exit;
    }

    $fileSoal = $_FILES['fileSoal']['tmp_name'];
    $fileJawaban = $_FILES['fileJawaban']['tmp_name'];

    if (empty($fileSoal) || empty($fileJawaban)) {
        throw new Exception('File soal dan jawaban harus diupload');
    }

    // Cek apakah dokumen berisi formula
    $xmlContent = extractDocxXml($fileSoal);
    if (strpos($xmlContent, '<m:oMath') !== false) {
        error_log("Dokumen berisi formula matematika yang tidak dapat diproses");
    }

    // Load dokumen untuk PhpWord
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($fileSoal);

    // Parse soal
    $soalArray = parseSoal($phpWord, $fileSoal);

    if (empty($soalArray)) {
        throw new Exception('No questions found in document');
    }

    $phpWord = \PhpOffice\PhpWord\IOFactory::load($fileJawaban);
    $kunciJawaban = parseKunciJawaban($phpWord);

    $ujian_id = $_POST['ujian_id'];

    mysqli_begin_transaction($koneksi);

    foreach ($soalArray as $soal) {
        $no = $soal['no'];

        // Proses teks soal
        $pertanyaan = $soal['soal'];

        // Deteksi formula dalam teks soal
        if (isFormulaFound($pertanyaan)) {
            $pertanyaan = "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
        }

        // Bersihkan teks
        $pertanyaan = cleanText($pertanyaan);

        // Debug isi pertanyaan
        error_log("Isi pertanyaan final soal #$no: " . substr($pertanyaan, 0, 100));

        $gambar_soal = null;

        // Simpan gambar pertanyaan jika ada
        if (!empty($soal['soal_images'])) {
            $gambar_soal = $soal['soal_images'][0]; // ambil gambar pertama
        }

        $jawaban_a = '';
        $jawaban_b = '';
        $jawaban_c = '';
        $jawaban_d = '';

        // Untuk setiap pilihan jawaban
        foreach (["A", "B", "C", "D"] as $option) {
            $jawaban = $soal['pilihan'][$option] ?? '';

            if (!empty($jawaban)) {
                // Deteksi formula dalam jawaban
                if (isFormulaFound($jawaban)) {
                    $jawaban = "FORMULA ERROR : Mohon maaf saat ini kami belum dapat mengambil formula Anda langsung melalui import soal, silahkan gunakan gambar atau buat manual.";
                }

                // Bersihkan teks
                $jawaban = cleanText($jawaban);

                // Simpan ke variabel yang sesuai
                if ($option == 'A') $jawaban_a = $jawaban;
                if ($option == 'B') $jawaban_b = $jawaban;
                if ($option == 'C') $jawaban_c = $jawaban;
                if ($option == 'D') $jawaban_d = $jawaban;
            }
        }

        // Tambahkan tag img untuk gambar pilihan jawaban
        if (!empty($soal['pilihan_images']['A'])) {
            foreach ($soal['pilihan_images']['A'] as $imagePath) {
                $jawaban_a .= "<br><img src='{$imagePath}' class='option-image'>";
            }
        }
        if (!empty($soal['pilihan_images']['B'])) {
            foreach ($soal['pilihan_images']['B'] as $imagePath) {
                $jawaban_b .= "<br><img src='{$imagePath}' class='option-image'>";
            }
        }
        if (!empty($soal['pilihan_images']['C'])) {
            foreach ($soal['pilihan_images']['C'] as $imagePath) {
                $jawaban_c .= "<br><img src='{$imagePath}' class='option-image'>";
            }
        }
        if (!empty($soal['pilihan_images']['D'])) {
            foreach ($soal['pilihan_images']['D'] as $imagePath) {
                $jawaban_d .= "<br><img src='{$imagePath}' class='option-image'>";
            }
        }

        $jawaban_benar = $kunciJawaban[$no] ?? '';

        // Gunakan prepared statement untuk menyimpan ke database
        $query = "INSERT INTO bank_soal (ujian_id, jenis_soal, pertanyaan, gambar_soal, jawaban_a, jawaban_b, jawaban_c, jawaban_d, jawaban_benar) 
                VALUES (?, 'pilihan_ganda', ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param(
            $stmt,
            'isssssss',
            $ujian_id,
            $pertanyaan,
            $gambar_soal,
            $jawaban_a,
            $jawaban_b,
            $jawaban_c,
            $jawaban_d,
            $jawaban_benar
        );
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
            error_log("Error menyimpan soal #$no: " . mysqli_stmt_error($stmt));
        } else {
            error_log("Soal #$no berhasil disimpan ke database");
        }
    }

    error_log("DOKUMEN: " . $_FILES['fileSoal']['name']);
    error_log("STRUKTUR SOAL: " . json_encode($soalArray, JSON_PRETTY_PRINT));

    mysqli_commit($koneksi);

    ob_clean();

    // PENTING: Pastikan tidak ada output tambahan sebelum JSON
    // Juga pastikan tidak ada spasi atau baris baru sebelum
    echo json_encode(['status' => 'success']);
    exit;
} catch (Exception $e) {
    if (isset($koneksi)) mysqli_rollback($koneksi);
    error_log("Error: " . $e->getMessage());
    ob_clean(); // Bersihkan output buffer sebelum mengirim JSON

    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}