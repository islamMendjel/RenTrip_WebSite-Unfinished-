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
// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Get the selected vehicle IDs from the query string
$vehicleIds = isset($_GET['vehicles']) ? explode(',', $_GET['vehicles']) : [];

// Fetch the selected vehicles from the database
$selectedVehicles = [];
$totalPrice = 0;

if (!empty($vehicleIds)) {
    $placeholders = implode(',', array_fill(0, count($vehicleIds), '?'));
    try {
        $stmt = $conn->prepare("
            SELECT vehicle_id, name_vehicle, fuelType, pricePerDay, picture 
            FROM vehicle 
            WHERE vehicle_id IN ($placeholders) AND status = 'available' AND statu != 'Inactive'
        ");
        $stmt->execute($vehicleIds);
        $selectedVehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate the total price
        foreach ($selectedVehicles as $vehicle) {
            $totalPrice += $vehicle['pricePerDay'];
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to fetch selected vehicles: " . $e->getMessage();
        header("Location: vehicles.php");
        exit;
    }
} else {
    $_SESSION['error'] = "No vehicles selected.";
    header("Location: vehicles.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="index.html" class="hover:underline">Home</a></li>
                    <li><a href="tenant.php" class="hover:underline">Dashboard</a></li>
                    <li><a href="vehicles.php" class="hover:underline">Vehicles</a></li>
                    <li><a href="/api/logout.php" class="hover:underline">Logout</a></li>
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

        <section class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Confirmation</h2>
            <p class="text-gray-600">Review your selected vehicles and confirm your rental.</p>
        </section>

        <!-- Selected Vehicles Section -->
        <section class="mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Selected Vehicles</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($selectedVehicles as $vehicle): ?>
                    <div class="bg-white shadow-md rounded p-4">
                        <img src="<?php echo htmlspecialchars($vehicle['picture']); ?>" alt="Vehicle image" class="w-full h-48 object-cover mb-4 rounded">
                        <h4 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($vehicle['name_vehicle']); ?></h4>
                        <p class="text-gray-600">Type: <?php echo htmlspecialchars($vehicle['fuelType']); ?></p>
                        <p class="text-gray-600">Price: $<?php echo htmlspecialchars($vehicle['pricePerDay']); ?> per day</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Total Price Section -->
        <section class="mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Total Price</h3>
            <p class="text-gray-600">Total: $<?php echo htmlspecialchars($totalPrice); ?></p>
        </section>

        <!-- Confirmation Form -->
        <section>
            <form action="confirm_rental.php" method="POST">
                <input type="hidden" name="vehicle_ids" value="<?php echo htmlspecialchars(implode(',', $vehicleIds)); ?>">
                
                <div class="mb-4">
                    <label for="pickupDate" class="block text-gray-700">Pickup Date</label>
                    <input type="date" id="pickupDate" name="pickupDate" required
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="returnDate" class="block text-gray-700">Return Date</label>
                    <input type="date" id="returnDate" name="returnDate" required
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="returnTime" class="block text-gray-700">Return Time (if same day)</label>
                    <input type="time" id="returnTime" name="returnTime"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Confirm Rental</button>
            </form>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pickupDateInput = document.getElementById('pickupDate');
            const returnDateInput = document.getElementById('returnDate');
            const returnTimeInput = document.getElementById('returnTime');

            function updateReturnTimeField() {
                const pickupDate = new Date(pickupDateInput.value);
                const returnDate = new Date(returnDateInput.value);
                const timeDifference = returnDate - pickupDate;
                const oneDayInMilliseconds = 24 * 60 * 60 * 1000;

                if (timeDifference > oneDayInMilliseconds) {
                    returnTimeInput.disabled = true;
                    returnTimeInput.value = '';
                } else {
                    returnTimeInput.disabled = false;
                }
            }

            pickupDateInput.addEventListener('change', updateReturnTimeField);
            returnDateInput.addEventListener('change', updateReturnTimeField);
        });
    </script>
    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>