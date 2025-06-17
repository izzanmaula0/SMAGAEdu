<?php
session_start();
require "koneksi.php";

$data = json_decode(file_get_contents('php://input'), true);
$project_id = $data['project_id'];
$title = $data['title'];
$content = $data['content'];
$user_id = $_SESSION['userid'];

$query = "INSERT INTO project_content (project_id, title, content) 
          VALUES (?, ?, ?)";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("iss", $project_id, $title, $content);

echo json_encode(['success' => $stmt->execute()]);
?>