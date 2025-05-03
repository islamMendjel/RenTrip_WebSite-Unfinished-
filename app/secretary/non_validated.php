<?php
session_start();

// Check if the user is logged in and is a secretary
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'secretary') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

// Function to fetch non-validated tenants
function fetchNonValidatedTenants($conn) {
    try {
        // Query for non-validated tenants
        $query = "SELECT *
                  FROM tenant, profil
                  WHERE tenant.profil_id = profil.profil_id
                  AND isValidated = 0";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['error'] = "Failed to fetch non-validated tenants: " . $e->getMessage();
        return [];
    }
}

// Fetch non-validated tenants
$non_validated_tenants = fetchNonValidatedTenants($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tenants - Secretary Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Manage Tenants</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="../index.html" class="hover:underline">Home</a></li>
                    <li><a href="secretary.php" class="hover:underline">Dashboard</a></li>
                    <li><a href="manage_reservations.php" class="hover:underline">Reservations</a></li>
                    <li><a href="manage_tenants.php" class="hover:underline">Tenants</a></li>
                    <li><a href="manage_vehicles.php" class="hover:underline">Vehicles</a></li>
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

        <section class="bg-white shadow-md rounded p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Manage Non-Validated Tenants</h2>
            <p class="text-gray-600">Review and Accept or Refuse New Tenants.</p>
            <table class="min-w-full bg-white border rounded shadow">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-2 px-4 text-left">ID</th>
                        <th class="py-2 px-4 text-left">Name</th>
                        <th class="py-2 px-4 text-left">Email</th>
                        <th class="py-2 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($non_validated_tenants)): ?>
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center text-gray-600">No non-validated tenants found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($non_validated_tenants as $tenant): ?>
                            <tr>
                                <td class="py-2 px-4"><?php echo htmlspecialchars($tenant['tenant_id']); ?></td>
                                <td class="py-2 px-4"><?php echo htmlspecialchars($tenant['firstName'] . ' ' . $tenant['lastName']); ?></td>
                                <td class="py-2 px-4"><?php echo htmlspecialchars($tenant['email']); ?></td>
                                <td class="py-2 px-4 text-center">
                                    <a href="../api/actions.php?action=accept&tenant_id=<?php echo $tenant['tenant_id']; ?>" 
                                       class="text-green-600 hover:underline">Accept</a> | 
                                    <a href="../api/actions.php?action=refuse&tenant_id=<?php echo $tenant['tenant_id']; ?>" 
                                       class="text-red-600 hover:underline">Refuse</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>