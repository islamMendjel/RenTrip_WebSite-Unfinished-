<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    $_SESSION['error'] = "You must be logged in as a tenant to access this page.";
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Initialize variables
$rentals = [];
$error = '';

try {
    // Select the current tenant by profil_id
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT tenant_id FROM tenant WHERE profil_id = :profil_id AND status != 'Inactive'");
    $stmt->execute(['profil_id' => $profil_id]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tenant) {
        throw new Exception("Tenant profile not found.");
    }

    $tenant_id = $tenant['tenant_id'];

    // Fetch all rentals for the current tenant
    $stmt = $conn->prepare("
        SELECT 
            r.reservation_id,
            r.pickupDate,
            r.returnDate,
            r.contract_id,
            v.name_vehicle AS vehicle_name, -- Use vehicle name instead of ID
            v.fuelType,
            v.color,
            v.pricePerDay,
            v.pricePerHour,
            b.bill_id,
            b.totalAmount,
            b.status AS bill_status
        FROM 
            reservation r
        JOIN 
            vehicle v ON r.vehicle_id = v.vehicle_id
        JOIN 
            bill b ON r.bill_id = b.bill_id
        WHERE 
            r.tenant_id = :tenant_id
    ");
    $stmt->execute(['tenant_id' => $tenant_id]);
    $rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log database errors
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching your rental data. Please try again later.";
} catch (Exception $e) {
    // Log other errors
    error_log("Error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Agency Fees - Tenant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Check Agency Fees</h1>
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
        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); // Clear the error message ?>
        <?php endif; ?>

        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Your Rental Fees</h2>
            <p class="text-gray-600">View and manage your rental fees here.</p>
        </section>

        <section class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Fee Details</h3>
            <?php if (empty($rentals)): ?>
                <p class="text-gray-600 text-center">You have no rental fees to display.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($rentals as $rental): ?>
                        <div class="block bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-200">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800">
                                        Vehicle: <?php echo htmlspecialchars($rental['vehicle_name']); ?>
                                    </h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?php echo htmlspecialchars($rental['fuelType']); ?> • <?php echo htmlspecialchars($rental['color']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-blue-600">
                                        $<?php echo htmlspecialchars($rental['totalAmount']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Status: <?php echo htmlspecialchars($rental['bill_status']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-gray-600">
                                <p><span class="font-medium">Pickup:</span> <?php echo htmlspecialchars($rental['pickupDate']); ?></p>
                                <p><span class="font-medium">Return:</span> <?php echo htmlspecialchars($rental['returnDate']); ?></p>
                            </div>
                            <!-- Add Pay Now button if bill status is 'Ready to Paid' -->
                            <?php if ($rental['bill_status'] === 'Ready to Paid'): ?>
                                <div class="mt-4">
                                    <a href="payment.php?bill_id=<?php echo $rental['bill_id']; ?>" 
                                       class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                        Pay Now
                                    </a>
                                </div>
                            <?php endif; ?>
                            <!-- Add View Contract button -->
                            <?php if (!empty($rental['contract_id'])): ?>
                                        <div class="mt-4">
                                            <a href="contract_details.php?contract_id=<?php echo $rental['contract_id']; ?>" 
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                                View Contract
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (empty($rental['contract_id']) && $rental['bill_status'] === 'Paid'): ?>
                                        <div class="mt-4">
                                            <a href="deposit_payment.php?contract_id=<?php echo $rental['contract_id']; ?>" 
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                            Deposit payment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>