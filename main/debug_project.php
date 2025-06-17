<?php
require "koneksi.php";

$project_id = $_GET['id'] ?? '';

// Get project details query
$query = "SELECT * FROM project_knowledge WHERE project_id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'i', $project_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Debug output
$debug_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $debug_data[] = $row;
}

echo json_encode([
    'success' => true,
    'debug' => $debug_data 
]);