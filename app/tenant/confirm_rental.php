<?php
session_start();
require '../config/db.php';
if (isset($_SESSION['profil_id'])){
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT profil_id, userName, password, type FROM profil WHERE profil_id = :profil_id  AND status != 'Inactive'");
    $stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_STR); // Binding parameters securely
    $stmt->execute();
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$profil) {
        $_SESSION['error'] = "Invalid user.";
        header("Location: ../login.php"); // Redirect to login page
        exit;
    }
}
// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}


// Get the selected vehicle IDs from the form
$vehicleIds = isset($_POST['vehicle_ids']) ? explode(',', $_POST['vehicle_ids']) : [];
$pickupDate = $_POST['pickupDate'];
$returnDate = $_POST['returnDate'];
$returnTime = $_POST['returnTime'];

if (empty($vehicleIds)) {
    $_SESSION['error'] = "No vehicles selected.";
    header("Location: ../vehicles.php");
    exit;
}

// Fetch the selected vehicles from the database
$placeholders = implode(',', array_fill(0, count($vehicleIds), '?'));
$stmt = $conn->prepare("
    SELECT vehicle_id, pricePerDay, pricePerHour 
    FROM vehicle 
    WHERE vehicle_id IN ($placeholders) AND statu != 'Inactive'
");
$stmt->execute($vehicleIds);
$selectedVehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate the rental duration
$pickupDateTime = new DateTime($pickupDate);
$returnDateTime = new DateTime($returnDate);
$interval = $pickupDateTime->diff($returnDateTime);

// Initialize total price
$totalPrice = 0;

// Calculate the total price for each vehicle
foreach ($selectedVehicles as $vehicle) {
    $pricePerDay = $vehicle['pricePerDay'];
    $pricePerHour = $vehicle['pricePerHour'];

    if ($interval->days > 1) {
        // Rental duration is more than one day: charge per day
        $totalPrice += $pricePerDay * $interval->days;
    } else {
        // Rental duration is one day or less: charge per hour
        $hours = $interval->h + ($interval->days * 24); // Convert days to hours if any
        $totalPrice += $pricePerHour * $hours;
    }
}

// If the rental duration is more than one day, set returnTime to NULL
if ($interval->days > 1) {
    $returnTime = NULL;
}

try {
    // Start a transaction
    $conn->beginTransaction();

    // Create a bill record
    $stmt = $conn->prepare("
        INSERT INTO bill (totalAmount) 
        VALUES (:totalAmount)
    ");
    $stmt->execute([
        ':totalAmount' => $totalPrice
    ]);

    // Get the bill_id of the newly created bill
    $billId = $conn->lastInsertId();

    // Update the status of the selected vehicles to "rented"
    $stmt = $conn->prepare("
        UPDATE vehicle 
        SET status = 'rented' 
        WHERE vehicle_id IN ($placeholders) AND status = 'available'
    ");
    $stmt->execute($vehicleIds);

    // Create a rental record for each vehicle
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT tenant_id FROM tenant WHERE profil_id = :profil_id AND status != 'Inactive'");
    $stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_STR); // Binding parameters securely
    $stmt->execute();
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
    $tenant_id = $profil['tenant_id'];
    foreach ($vehicleIds as $vehicleId) {
        $stmt = $conn->prepare("
            INSERT INTO reservation (tenant_id, vehicle_id, pickupDate, returnDate, returnTime, bill_id, status) 
            VALUES (:tenant_id, :vehicle_id, :pickupDate, :returnDate, :returnTime, :bill_id, 'inactive')
        ");
        $stmt->execute([
            ':tenant_id' => $tenant_id,
            ':vehicle_id' => $vehicle['vehicle_id'],
            ':pickupDate' => $pickupDate,
            ':returnDate' => $returnDate,
            ':returnTime' => $returnTime,
            ':bill_id' => $billId
        ]);
    }

    // Commit the transaction
    $conn->commit();

    $_SESSION['success'] = "Rental confirmed successfully.";
    header("Location: tenant.php");
    exit;
} catch (PDOException $e) {
    // Roll back the transaction on error
    $conn->rollBack();
    $_SESSION['error'] = "Failed to confirm rental: " . $e->getMessage();
    header("Location: confirmation.php?vehicles=" . implode(',', $vehicleIds));
    exit;
}
?>