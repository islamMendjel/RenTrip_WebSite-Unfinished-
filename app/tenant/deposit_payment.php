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
            c.deposit,
            c.contract_status
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
    <title>Deposit Payment - RenTriP Vehicle Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Deposit Payment</h1>
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Deposit Payment</h2>
            <div class="space-y-4">
                <p><span class="font-medium">Contract ID:</span> <?php echo htmlspecialchars($contract['contract_id']); ?></p>
                <p><span class="font-medium">Deposit Amount:</span> $<?php echo htmlspecialchars($contract['deposit']); ?></p>
                <p><span class="font-medium">Contract Status:</span> <?php echo htmlspecialchars($contract['contract_status']); ?></p>
                <form action="process_deposit_payment.php" method="POST">
                    <input type="hidden" name="contract_id" value="<?php echo $contract_id; ?>">
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                        Confirm Deposit Payment
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