<?php
header('Content-Type: application/json');
require '../config/db.php';

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Prepare the SQL query
    $stmt = $conn->prepare("
        INSERT INTO vehicle (
            matricule, deposit_id, owner_id, fuelType, releaseYear, color, isAutomatic, 
            pricePerDay, pricePerHour, description, status, picture, hasAirConditioning, 
            hasBluetooth, hasCruiseControl, hasAMFMStereoRadio, hasLeatherInterior
        ) VALUES (
            :matricule, :deposit_id, :owner_id, :fuelType, :releaseYear, :color, :isAutomatic, 
            :pricePerDay, :pricePerHour, :description, :status, :picture, :hasAirConditioning, 
            :hasBluetooth, :hasCruiseControl, :hasAMFMStereoRadio, :hasLeatherInterior
        )
    ");

    // Bind parameters
    $stmt->bindParam(':matricule', $data['matricule']);
    $stmt->bindParam(':deposit_id', $data['deposit_id']);
    $stmt->bindParam(':owner_id', $data['owner_id']);
    $stmt->bindParam(':fuelType', $data['fuelType']);
    $stmt->bindParam(':releaseYear', $data['releaseYear']);
    $stmt->bindParam(':color', $data['color']);
    $stmt->bindParam(':isAutomatic', $data['isAutomatic']);
    $stmt->bindParam(':pricePerDay', $data['pricePerDay']);
    $stmt->bindParam(':pricePerHour', $data['pricePerHour']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':status', $data['status']);
    $stmt->bindParam(':picture', $data['picture']);
    $stmt->bindParam(':hasAirConditioning', $data['hasAirConditioning']);
    $stmt->bindParam(':hasBluetooth', $data['hasBluetooth']);
    $stmt->bindParam(':hasCruiseControl', $data['hasCruiseControl']);
    $stmt->bindParam(':hasAMFMStereoRadio', $data['hasAMFMStereoRadio']);
    $stmt->bindParam(':hasLeatherInterior', $data['hasLeatherInterior']);

    // Execute the query
    $stmt->execute();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Vehicle added successfully.']);
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(['success' => false, 'message' => 'Failed to add vehicle: ' . $e->getMessage()]);
}
?>