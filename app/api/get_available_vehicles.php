<?php
header('Content-Type: application/json');
require '../config/db.php';

try {
    // Fetch all available vehicles
    $stmt = $conn->query("
        SELECT vehicle_id, name_vehicle, fuelType, pricePerDay, picture 
        FROM vehicle 
        WHERE status = 'available'  AND statu != 'Inactive'
    ");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the vehicles as JSON
    echo json_encode($vehicles);
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['error' => 'Failed to fetch vehicles: ' . $e->getMessage()]);
}
?>