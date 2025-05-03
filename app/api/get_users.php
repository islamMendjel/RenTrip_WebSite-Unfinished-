<?php
require '../config/db.php';

try {
    // Fetch all profiles from the database
    $stmt = $conn->query("SELECT profil_id, firstName, lastName, email, role FROM profil  AND status != 'Inactive'");
    $profils = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set the response header to JSON
    header('Content-Type: application/json');

    // Return the profiles as JSON
    echo json_encode([
        'success' => true,
        'data' => $profils
    ]);
} catch (PDOException $e) {
    // Log the error for debugging
    error_log("Database error: " . $e->getMessage());

    // Return a JSON error response
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch profiles.',
        'error' => $e->getMessage() // Include the error message for debugging
    ]);
}
?>