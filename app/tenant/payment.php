<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    $_SESSION['error'] = "You must be logged in as a tenant to access this page.";
    header("Location: ../login.php");
    exit;
}

// Get the bill ID from the query string
$bill_id = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;

if (!$bill_id) {
    $_SESSION['error'] = "Invalid bill ID.";
    header("Location: tenant.php");
    exit;
}

try {
    // Fetch the bill details
    $stmt = $conn->prepare("
        SELECT b.totalAmount, v.name_vehicle 
        FROM bill b
        JOIN reservation r ON b.bill_id = r.bill_id
        JOIN vehicle v ON r.vehicle_id = v.vehicle_id
        WHERE b.bill_id = :bill_id AND r.tenant_id = :tenant_id
    ");
    $stmt->execute([
        ':bill_id' => $bill_id,
        ':tenant_id' => $_SESSION['profil_id']
    ]);
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bill) {
        throw new Exception("Bill not found or you do not have permission to access it.");
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
    <title>Payment - RenTriP Vehicle Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Payment</h1>
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Payment Details</h2>
            <div class="space-y-4">
                <p><span class="font-medium">Vehicle:</span> <?php echo htmlspecialchars($bill['name_vehicle']); ?></p>
                <p><span class="font-medium">Total Amount:</span> $<?php echo htmlspecialchars($bill['totalAmount']); ?></p>
                <form action="process_payment.php" method="POST">
                    <input type="hidden" name="bill_id" value="<?php echo $bill_id; ?>">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Confirm Payment
                    </button>
                </form>
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