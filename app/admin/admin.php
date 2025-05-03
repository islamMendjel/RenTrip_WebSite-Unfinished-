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
// Check if the user is logged in and is an admin
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.14/dist/vue.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Admin Dashboard</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="admin.php" class="hover:underline">Dashboard</a></li>
                    <li><a href="manage_users.php" class="hover:underline">Users</a></li>
                    <li><a href="manage_vehicles.php" class="hover:underline">Vehicles</a></li>
                    <li><a href="statistics.php" class="hover:underline">Statistics</a></li>
                    <li><a href="../api/logout.php" class="hover:underline">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8" id="adminApp">
        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Welcome, Admin</h2>
            <p class="text-gray-600">Manage your users, vehicles, and system statistics here.</p>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Users</h3>
                <p class="text-gray-600">Add, update, or delete users from the system.</p>
                <a href="manage_users.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Vehicles</h3>
                <p class="text-gray-600">Add, update, or delete vehicles from the inventory.</p>
                <a href="manage_vehicles.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">View Statistics</h3>
                <p class="text-gray-600">View system performance and usage statistics.</p>
                <a href="statistics.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Show</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Profile</h3>
                <p class="text-gray-600">Update your profile and report issues.</p> <!-- Clarified text -->
                <a href="../api/edit_profile.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
        </section>

        <!-- Dynamic Data Section -->
        <section class="mt-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">System Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow-md rounded p-6">
                    <h4 class="text-lg font-bold text-gray-800">Total Users</h4>
                    <p class="text-3xl font-bold text-blue-600">{{ userCount }}</p>
                </div>
                <div class="bg-white shadow-md rounded p-6">
                    <h4 class="text-lg font-bold text-gray-800">Total Vehicles</h4>
                    <p class="text-3xl font-bold text-blue-600">{{ vehicleCount }}</p>
                </div>
                <div class="bg-white shadow-md rounded p-6">
                    <h4 class="text-lg font-bold text-gray-800">Active Reservations</h4>
                    <p class="text-3xl font-bold text-blue-600">{{ reservationCount }}</p>
                </div>
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
            el: '#adminApp',
            data: {
                userCount: 0,
                vehicleCount: 0,
                reservationCount: 0
            },
            methods: {
                fetchData() {
                    // Fetch user count
                    fetch('../api/get_user_count.php')
                        .then(response => response.json())
                        .then(data => {
                            this.userCount = data.count;
                        });

                    // Fetch vehicle count
                    fetch('../api/get_vehicle_count.php')
                        .then(response => response.json())
                        .then(data => {
                            this.vehicleCount = data.count;
                        });

                    // Fetch reservation count
                    fetch('../api/get_reservation_count.php')
                        .then(response => response.json())
                        .then(data => {
                            this.reservationCount = data.count;
                        });
                }
            },
            mounted() {
                this.fetchData(); // Fetch data when the page loads
            }
        });
    </script>
</body>
</html>