<?php
session_start();

// Ensure the user is logged in and has a secretary profile
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'secretary') {
    header("Location: ../login.php"); // Redirect to login if unauthorized
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary Dashboard - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.14/dist/vue.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Secretary Dashboard</h1>
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

    <main class="container mx-auto py-8" id="secretaryApp">
        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['profil_name']); ?></h2>
            <p class="text-gray-600">Manage reservations, vehicles, and tenant information here.</p>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Reservations</h3>
                <p class="text-gray-600">View and manage all reservations.</p>
                <a href="manage_reservations.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Vehicles</h3>
                <p class="text-gray-600">Add, modify, or delete vehicles.</p> <!-- Fixed text -->
                <a href="manage_vehicles.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Tenants</h3>
                <p class="text-gray-600">View and manage tenant information.</p>
                <a href="manage_tenants.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Profile</h3>
                <p class="text-gray-600">Update your profile and report issues.</p> <!-- Clarified text -->
                <a href="../api/edit_profile.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        new Vue({
            el: '#secretaryApp',
            methods: {
                navigateTo(page) {
                    // Implement navigation logic if needed
                    switch (page) {
                        case 'manage_reservations':
                            window.location.href = 'manage_reservations.php';
                            break;
                        case 'manage_vehicles':
                            window.location.href = 'manage_vehicles.php';
                            break;
                        case 'manage_tenants':
                            window.location.href = 'manage_tenants.php';
                            break;
                        case 'edit_profile':
                            window.location.href = '../api/edit_profile.php';
                            break;
                        default:
                            console.log('Invalid page');
                    }
                }
            }
        });
    </script>
</body>
</html>
