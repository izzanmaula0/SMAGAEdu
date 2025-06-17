<?php
function cosineSimilarity($vec1, $vec2) {
    $dotProduct = 0;
    $norm1 = 0;
    $norm2 = 0;
    
    for ($i = 0; $i < count($vec1); $i++) {
        $dotProduct += $vec1[$i] * $vec2[$i];
        $norm1 += $vec1[$i] * $vec1[$i];
        $norm2 += $vec2[$i] * $vec2[$i];
    }
    
    $norm1 = sqrt($norm1);
    $norm2 = sqrt($norm2);
    
    return $dotProduct / ($norm1 * $norm2);
}

function generateEmbedding($text) {
    // Panggil API embedding (contoh menggunakan OpenAI)
    $apiKey = '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';
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    
    $data = [
        'model' => 'text-embedding-ada-002',
        'input' => $text
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $embedding = json_decode($response, true)['data'][0]['embedding'];
    
    return $embedding;
}

function semanticSearch($queryEmbedding, $projectEmbeddings, $topK = 5) {
    $similarities = [];
    
    foreach ($projectEmbeddings as $embedding) {
        $similarity = cosineSimilarity($queryEmbedding, $embedding['embedding']);
        $similarities[] = [
            'content' => $embedding['content'],
            'similarity' => $similarity
        ];
    }
    
    usort($similarities, function($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });
    
    return array_slice($similarities, 0, $topK);
}

function fetchProjectEmbeddings($projectId, $connection) {
    $query = "SELECT content, embedding 
              FROM project_file_contents pfc
              JOIN project_files pf ON pfc.project_file_id = pf.id
              WHERE pf.project_id = ?";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    $embeddings = [];
    while ($row = $result->fetch_assoc()) {
        $embeddings[] = [
            'content' => $row['content'],
            'embedding' => json_decode($row['embedding'], true)
        ];
    }

    return $embeddings;
}