<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['profil_id'])){
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT profil_id, userName, password, type FROM profil WHERE profil_id = :profil_id AND status != 'Inactive'");
    $stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_STR); // Binding parameters securely
    $stmt->execute();
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$profil) {
        $_SESSION['error'] = "Invalid user.";
        header("Location: ../login.php"); // Redirect to login page
        exit;
    }
}
// Check if is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

require '../config/db.php';

// Fetch rental details based on the reservation ID
if (isset($_GET['id'])) {
    $reservation_id = $_GET['id'];
    // Select the current tenant by profil_id
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT tenant_id FROM tenant WHERE profil_id = :profil_id AND status = 'status'");
    $stmt->execute(['profil_id' => $profil_id]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tenant) {
        throw new Exception("Tenant profile not found.");
    }

    $tenant_id = $tenant['tenant_id'];

    // Ensure the reservation belongs to the current tenant
    $stmt = $conn->prepare("
        SELECT 
            r.reservation_id,
            r.creationDate,
            r.returnDate,
            r.pickupLocation,
            r.returnLocation,
            r.state,
            v.matricule AS vehicle_matricule,
            v.fuelType,
            v.color,
            v.pricePerDay,
            v.pricePerHour,
            d.deposit_id,
            d.capacity,
            l.location_id,
            l.placeName,
            l.zipCode,
            b.bill_id,
            b.totalAmount,
            o.owner_id,
            o.profil_id AS owner_profil_id
        FROM 
            reservation r
        JOIN 
            vehicle v ON r.vehicle_matricule = v.matricule
        JOIN 
            deposit d ON v.deposit_id = d.deposit_id
        JOIN 
            location l ON d.location_id = l.location_id
        JOIN 
            bill b ON r.bill_id = b.bill_id
        JOIN 
            owner o ON v.owner_id = o.owner_id
        WHERE 
            r.reservation_id = :reservation_id AND r.tenant_id = :tenant_id
    ");
    $stmt->execute(['reservation_id' => $reservation_id, 'tenant_id' => $tenant_id]);
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        header("Location: check_agency_fees.php"); // Redirect if the rental doesn't belong to the tenant
        exit;
    }
} else {
    header("Location: check_agency_fees.php"); // Redirect if no reservation ID is provided
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Details - Tenant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Rental Details</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="../index.html" class="hover:underline">Home</a></li>
                    <li><a href="tenant.php" class="hover:underline">Dashboard</a></li>
                    <li><a href="../vehicles.php" class="hover:underline">Vehicles</a></li>
                    <li><a href="../api/logout.php" class="hover:underline">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8">
        <section class="bg-white shadow-md rounded p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Rental Details</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Vehicle Information</h3>
                    <p class="text-gray-600">Matricule: <?php echo htmlspecialchars($rental['vehicle_matricule']); ?></p>
                    <p class="text-gray-600">Fuel Type: <?php echo htmlspecialchars($rental['fuelType']); ?></p>
                    <p class="text-gray-600">Color: <?php echo htmlspecialchars($rental['color']); ?></p>
                    <p class="text-gray-600">Price Per Day: $<?php echo htmlspecialchars($rental['pricePerDay']); ?></p>
                    <p class="text-gray-600">Price Per Hour: $<?php echo htmlspecialchars($rental['pricePerHour']); ?></p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Deposit Information</h3>
                    <p class="text-gray-600">Deposit ID: <?php echo htmlspecialchars($rental['deposit_id']); ?></p>
                    <p class="text-gray-600">Capacity: <?php echo htmlspecialchars($rental['capacity']); ?></p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Location Information</h3>
                    <p class="text-gray-600">Place Name: <?php echo htmlspecialchars($rental['placeName']); ?></p>
                    <p class="text-gray-600">Zip Code: <?php echo htmlspecialchars($rental['zipCode']); ?></p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Bill Information</h3>
                    <p class="text-gray-600">Total Amount: $<?php echo htmlspecialchars($rental['totalAmount']); ?></p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Owner Information</h3>
                    <p class="text-gray-600">Owner ID: <?php echo htmlspecialchars($rental['owner_id']); ?></p>
                </div>
            </div>
            <div class="mt-6">
                <a href="check_agency_fees.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Back to Fees</a>
            </div>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>