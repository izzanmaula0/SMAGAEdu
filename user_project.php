<?php
session_start();
require "koneksi.php";

$data = json_decode(file_get_contents('php://input'), true);
$project_id = $data['project_id'];
$user_id = $_SESSION['userid'];

// Set all projects inactive
$query1 = "UPDATE project_context SET is_active = 0 WHERE user_id = ?";
$stmt1 = $koneksi->prepare($query1);
$stmt1->bind_param("s", $user_id);
$stmt1->execute();

// Set selected project active
$query2 = "UPDATE project_context SET is_active = 1 WHERE id = ? AND user_id = ?";
$stmt2 = $koneksi->prepare($query2);
$stmt2->bind_param("is", $project_id, $user_id);

echo json_encode(['success' => $stmt2->execute()]);
?>