<?php
header('Content-Type: application/json');
require '../config/db.php';

try {
    // Fetch all vehicles from the database
    $stmt = $conn->query("SELECT * FROM vehicle WHERE statu != 'Inactive'");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the vehicles as JSON
    echo json_encode($vehicles);
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['success' => false, 'message' => 'Failed to fetch vehicles: ' . $e->getMessage()]);
}
?>