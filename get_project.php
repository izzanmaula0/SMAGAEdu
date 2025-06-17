<?php
session_start();
require "koneksi.php";

$userId = $_SESSION['userid'];
$query = "SELECT * FROM projects WHERE user_id = '$userId'";
$result = mysqli_query($koneksi, $query);

$projects = [];
while($row = mysqli_fetch_assoc($result)) {
    $projects[] = $row;
}

echo json_encode($projects);