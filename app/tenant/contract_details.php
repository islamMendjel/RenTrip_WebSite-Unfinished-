<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    $_SESSION['error'] = "You must be logged in as a tenant to access this page.";
    header("Location: ../login.php");
    exit;
}

// Get the contract ID from the query string
$contract_id = isset($_GET['contract_id']) ? intval($_GET['contract_id']) : 0;

if (!$contract_id) {
    $_SESSION['error'] = "Invalid contract ID.";
    header("Location: tenant.php");
    exit;
}

try {
    // Fetch the contract details
    $stmt = $conn->prepare("
        SELECT 
            c.contract_id,
            c.tenant_phone,
            c.tenant_email,
            c.vehicle_name,
            c.vehicle_year,
            c.vehicle_matricule,
            c.vehicle_descreption,
            c.rental_start,
            c.rental_end,
            c.deposit,
            c.contract_status,
            c.created_at,
            c.updated_at
        FROM 
            contracts c
        WHERE 
            c.contract_id = :contract_id
    ");
    $stmt->execute([':contract_id' => $contract_id]);
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        throw new Exception("Contract not found.");
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: tenant.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: tenant.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Details - RenTriP Vehicle Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Contract Details</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="../index.html" class="hover:underline">Home</a></li>
                    <li><a href="tenant.php" class="hover:underline">Dashboard</a></li>
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
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <section class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Contract Details</h2>
            <div class="space-y-4">
                <p><span class="font-medium">Contract ID:</span> <?php echo htmlspecialchars($contract['contract_id']); ?></p>
                <p><span class="font-medium">Tenant Phone:</span> <?php echo htmlspecialchars($contract['tenant_phone']); ?></p>
                <p><span class="font-medium">Tenant Email:</span> <?php echo htmlspecialchars($contract['tenant_email']); ?></p>
                <p><span class="font-medium">Vehicle Name:</span> <?php echo htmlspecialchars($contract['vehicle_name']); ?></p>
                <p><span class="font-medium">Vehicle Year:</span> <?php echo htmlspecialchars($contract['vehicle_year']); ?></p>
                <p><span class="font-medium">Vehicle Matricule:</span> <?php echo htmlspecialchars($contract['vehicle_matricule']); ?></p>
                <p><span class="font-medium">Vehicle Description:</span> <?php echo htmlspecialchars($contract['vehicle_descreption']); ?></p>
                <p><span class="font-medium">Rental Start:</span> <?php echo htmlspecialchars($contract['rental_start']); ?></p>
                <p><span class="font-medium">Rental End:</span> <?php echo htmlspecialchars($contract['rental_end']); ?></p>
                <p><span class="font-medium">Deposit:</span> $<?php echo htmlspecialchars($contract['deposit']); ?></p>
                <p><span class="font-medium">Contract Status:</span> <?php echo htmlspecialchars($contract['contract_status']); ?></p>
                <p><span class="font-medium">Created At:</span> <?php echo htmlspecialchars($contract['created_at']); ?></p>
                <p><span class="font-medium">Updated At:</span> <?php echo htmlspecialchars($contract['updated_at']); ?></p>
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