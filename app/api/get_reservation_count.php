<?php
require '../config/db.php'; 

$stmt = $conn->query("SELECT COUNT(*) as count FROM reservation");
$result = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($result);
?>