<?php
session_start();

// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure $_SESSION['vehicle_id'] is set and is an array
    if (!isset($_SESSION['vehicle_id']) || !is_array($_SESSION['vehicle_id'])) {
        $_SESSION['error'] = "Invalid vehicle ID.";
        header("Location: ../tenant/report.php");
        exit;
    }

    // Use the first vehicle ID in the array (or handle multiple IDs as needed)
    $vehicle_id = $_SESSION['vehicle_id'][0];

    // Fetch owner_id for the vehicle
    try {
        $stmt = $conn->prepare("SELECT owner_id FROM vehicle WHERE vehicle_id = :vehicle_id AND statu != 'Inactive'");
        $stmt->execute(['vehicle_id' => $vehicle_id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            throw new Exception("Vehicle not found.");
        }

        $owner_id = $vehicle['owner_id'];
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred while fetching vehicle details: " . $e->getMessage();
        header("Location: ../tenant/report.php");
        exit;
    }

    // Sanitize form inputs
    $subject = htmlspecialchars($_POST['subject']);
    $content = htmlspecialchars($_POST['content']);

    // Fetch tenant_id for the current user
    $profil_id = $_SESSION['profil_id'];
    try {
        $stmt = $conn->prepare("SELECT tenant_id FROM tenant WHERE profil_id = :profil_id 'status' => 'Active'");
        $stmt->execute(['profil_id' => $profil_id]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tenant) {
            throw new Exception("Tenant not found.");
        }

        $tenant_id = $tenant['tenant_id'];
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred while fetching tenant details: " . $e->getMessage();
        header("Location: ../tenant/report.php");
        exit;
    }

    // Insert problem report into the database
    try {
        $stmt = $conn->prepare("INSERT INTO reclamation (subject, content, owner_id, tenant_id, vehicle_id) VALUES (:subject, :content, :owner_id, :tenant_id, :vehicle_id)");
        $stmt->execute([
            'subject' => $subject,
            'content' => $content,
            'owner_id' => $owner_id,
            'tenant_id' => $tenant_id,
            'vehicle_id' => $vehicle_id
        ]);

        $_SESSION['success'] = "Report submitted successfully.";
        header("Location: ../tenant/report.php?success=1");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "An error occurred while submitting the report: " . $e->getMessage();
        header("Location: ../tenant/report.php");
        exit;
    }
}
?>