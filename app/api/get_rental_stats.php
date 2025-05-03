<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Fetch rental statistics (e.g., rentals per month)
    $query = "
        SELECT 
            DATE_FORMAT(rental_start, '%Y-%m') AS month, 
            COUNT(*) AS rental_count 
        FROM contracts 
        GROUP BY DATE_FORMAT(rental_start, '%Y-%m') 
        ORDER BY month
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for Chart.js
    $labels = [];
    $data = [];
    foreach ($results as $row) {
        $labels[] = $row['month'];
        $data[] = $row['rental_count'];
    }

    // Return JSON response
    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}