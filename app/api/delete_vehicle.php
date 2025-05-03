<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'admin') {
    $_SESSION['error'] = "You must be logged in as an admin to access this page.";
    header("Location: ../login.php");
    exit;
}

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: ../admin/manage_vehicles.php");
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Allow GET requests but warn about potential security risks
    // Note: GET requests are less secure because they don't include a CSRF token
    $_SESSION['warning'] = "Deleting via GET requests is not recommended for security reasons.";
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../admin/manage_vehicles.php");
    exit;
}

// Validate and sanitize the vehicle ID
if (isset($_REQUEST['id'])) { // Use $_REQUEST to handle both GET and POST
    $vehicle_id = filter_var($_REQUEST['id'], FILTER_VALIDATE_INT);
    if ($vehicle_id === false) {
        $_SESSION['error'] = "Invalid vehicle ID.";
        header("Location: ../admin/manage_vehicles.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Vehicle ID not provided.";
    header("Location: ../admin/manage_vehicles.php");
    exit;
}

try {
    // Check if the vehicle exists
    $stmt = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE vehicle_id = :vehicle_id AND statu != 'Inactive'");
    $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
    $stmt->execute();
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        $_SESSION['error'] = "Vehicle not found or already inactive.";
        header("Location: ../admin/manage_vehicles.php");
        exit;
    }

    // Mark the vehicle as inactive
    $stmt = $conn->prepare("UPDATE vehicle SET statu = 'Inactive' WHERE vehicle_id = :vehicle_id AND status != 'rented'");
    $stmt->bindParam(':vehicle_id', $vehicle_id, PDO::PARAM_INT);
    $stmt->execute();

    // Set success message
    $_SESSION['success'] = "Vehicle deleted successfully.";
} catch (PDOException $e) {
    // Handle database errors
    $_SESSION['error'] = "Failed to delete vehicle: " . $e->getMessage();
}

// Redirect back to the manage vehicles page
header("Location: ../admin/manage_vehicles.php");
exit;
?>