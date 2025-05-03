<?php
session_start();

// Check if the user is logged in and is a secretary
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'secretary') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

function fetchExcludedTenants($conn) {
    // Query for excluded tenants
    $query = "SELECT * FROM tenants WHERE excluded = 1";
    return $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
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
        <!-- <section class="mb-8">
            <h3 class="text-xl font-semibold text-gray-700 mt-8 mb-2">Non-Validated Tenants</h3>
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
                    <?php
                    $non_validated_tenants = fetchNonValidatedTenants($conn);
                    foreach ($non_validated_tenants as $tenant) {
                        echo "<tr>
                            <td class='py-2 px-4'>{$tenant['id']}</td>
                            <td class='py-2 px-4'>{$tenant['name']}</td>
                            <td class='py-2 px-4'>{$tenant['email']}</td>
                            <td class='py-2 px-4 text-center'>
                                <a href='../api/actions.php?action=accept&id={$tenant['id']}' class='text-green-600 hover:underline'>Accept</a> | 
                                <a href='../api/actions.php?action=refuse&id={$tenant['id']}' class='text-red-600 hover:underline'>Refuse</a>
                            </td>
                          </tr>";
                    }
                    ?>
                </tbody>
            </table> -->
        </section>
        
        <section class="bg-white shadow-md rounded p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Manage Excluded Tenants</h2>
            <p class="text-gray-600">Review and Revoke Excluded Tenants.</p>
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
                    <?php
                    $excluded_tenants = fetchExcludedTenants($conn);
                    foreach ($excluded_tenants as $tenant) {
                        echo "<tr>
                            <td class='py-2 px-4'>{$tenant['id']}</td>
                            <td class='py-2 px-4'>{$tenant['name']}</td>
                            <td class='py-2 px-4'>{$tenant['email']}</td>
                            <td class='py-2 px-4 text-center'>
                                <a href='../api/actions.php?action=revoke_exclude&tenant_id={$tenant['id']}' class='text-blue-600 hover:underline'>Revoke Exclude</a>
                            </td>
                          </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>