<?php

/**
 * Fungsi untuk mendapatkan konteks sekolah yang relevan dengan pertanyaan user
 * @param string $query Pertanyaan atau pesan dari user
 * @return string Konteks yang relevan untuk dikirim ke AI
 */


function getContextualKnowledge($query) {
    // Path ke direktori knowledge
    $knowledgePath = __DIR__ . '/knowledge';
    
    // Pastikan direktori knowledge ada
    if (!file_exists($knowledgePath)) {
        mkdir($knowledgePath, 0755, true);
    }
    
    // Daftar semua file knowledge
    $knowledgeFiles = [
        'umum' => $knowledgePath . '/umum.json',
        'fasilitas' => $knowledgePath . '/fasilitas.json',
        'kurikulum' => $knowledgePath . '/kurikulum.json',
        'ekstrakurikuler' => $knowledgePath . '/ekstrakurikuler.json',
        'guru' => $knowledgePath . '/guru.json',
        'peraturan' => $knowledgePath . '/peraturan.json',
    ];
    
    // Kategori yang selalu disertakan
    $alwaysInclude = ['umum'];
    
    // Keyword untuk setiap kategori
    $keywords = [
        'fasilitas' => ['fasilitas', 'gedung', 'ruangan', 'lab', 'kelas', 'perpustakaan'],
        'kurikulum' => ['kurikulum', 'pelajaran', 'mapel', 'kbm', 'belajar', 'mata pelajaran'],
        'ekstrakurikuler' => ['ekskul', 'ekstrakurikuler', 'kegiatan', 'klub', 'lomba'],
        'guru' => ['guru', 'tenaga pengajar', 'mengajar', 'staff', 'pendidik', 'karyawan'],
        'peraturan' => ['aturan', 'peraturan', 'tata tertib', 'disiplin', 'sanksi', 'hukuman', 'larangan']
    ];
    
    // Tentukan file yang relevan
    $relevantCategories = $alwaysInclude;
    foreach ($keywords as $category => $terms) {
        foreach ($terms as $term) {
            if (stripos($query, $term) !== false && !in_array($category, $relevantCategories)) {
                $relevantCategories[] = $category;
                break;
            }
        }
    }
    
    // Gabungkan konten dari file yang relevan
    $contextData = [];
    foreach ($relevantCategories as $category) {
        if (isset($knowledgeFiles[$category]) && file_exists($knowledgeFiles[$category])) {
            $jsonData = json_decode(file_get_contents($knowledgeFiles[$category]), true);
            if ($jsonData) {
                $contextData[$category] = $jsonData;
            }
        }
    }
    
    // Format data untuk AI
    $formattedContext = "INFORMASI TENTANG SEKOLAH:\n\n";
    
    foreach ($contextData as $category => $data) {
        $formattedContext .= "### " . strtoupper($category) . "\n";
        
        // Format data sesuai struktur JSON
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $formattedContext .= "{$key}:\n";
                if (isset($value[0]) && is_scalar($value[0])) {
                    // Array sederhana (daftar)
                    foreach ($value as $item) {
                        $formattedContext .= "- {$item}\n";
                    }
                } else {
                    // Array asosiatif (object)
                    foreach ($value as $subKey => $subValue) {
                        if (is_array($subValue)) {
                            $formattedContext .= "  {$subKey}:\n";
                            foreach ($subValue as $subItem) {
                                $formattedContext .= "    - {$subItem}\n";
                            }
                        } else {
                            $formattedContext .= "  {$subKey}: {$subValue}\n";
                        }
                    }
                }
            } else {
                $formattedContext .= "{$key}: {$value}\n";
            }
        }
        $formattedContext .= "\n";
    }
    
    return $formattedContext;
}
?>