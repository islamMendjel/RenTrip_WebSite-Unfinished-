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
    header("Location: login.php"); // Redirect to login page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.14/dist/vue.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Tenant Dashboard</h1>
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

    <main class="container mx-auto py-8" id="tenantApp">
        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['profil_name']); ?></h2>
            <p class="text-gray-600">Manage your rentals, profile, and more here.</p>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Rent a Car</h3>
                <p class="text-gray-600">Browse and rent available vehicles.</p>
                <a href="../vehicles.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Check Agency Fees</h3>
                <p class="text-gray-600">View and manage your rental fees.</p>
                <a href="check_agency_fees.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Profile</h3>
                <p class="text-gray-600">Update your personal information.</p>
                <a href="../api/edit_profile.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Report a Problem</h3>
                <p class="text-gray-600">Report any issues with your rental.</p>
                <a href="report_problem.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
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