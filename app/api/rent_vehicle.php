<?php
session_start();
require '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$vehicleId = $data['vehicleId'];
$userId = $_SESSION['user_id']; // Ensure the user is logged in

// Validate inputs
if (empty($vehicleId) || empty($userId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Insert rental record into the database
$stmt = $conn->prepare("INSERT INTO rentals (vehicle_id, user_id, rental_date, return_date) VALUES (:vehicle_id, :user_id, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))");
$stmt->execute([
    'vehicle_id' => $vehicleId,
    'user_id' => $userId
]);

echo json_encode(['success' => true]);
?>