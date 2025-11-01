<?php
require '../config/db.php';
$id = $_GET['id'];
$stmt = $mysqli->prepare("DELETE FROM roteiros WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
header("Location: index.php");
?>