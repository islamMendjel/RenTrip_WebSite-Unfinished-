<?php
require '../config/db.php';

$stmt = $conn->query("SELECT COUNT(*) as count FROM vehicle WHERE statu != 'Inactive'");
$vehicles = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($vehicles);
?>