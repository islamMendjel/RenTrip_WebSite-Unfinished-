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

// Predefined fuel types
$fuelTypes = ['Gasoline', 'Diesel', 'Electric', 'Hybrid'];

// Fetch vehicle details for editing
if (isset($_GET['id'])) {
    $vehicle_id = $_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM vehicle WHERE vehicle_id = :vehicle_id AND statu != 'Inactive'");
        $stmt->execute(['vehicle_id' => $vehicle_id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            $_SESSION['error'] = "Vehicle not found.";
            header("Location: ../admin/manage_vehicles.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to fetch vehicle details: " . $e->getMessage();
        header("Location: ../admin/manage_vehicles.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Vehicle ID not provided.";
    header("Location: ../admin/manage_vehicles.php");
    exit;
}

// Fetch all owners for the dropdown
$owners = [];
try {
    $stmt = $conn->query("
        SELECT owner.owner_id, profil.email 
        FROM owner 
        JOIN profil ON owner.profil_id = profil.profil_id 
        WHERE owner.status != 'Inactive';;
    ");
    $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to fetch owners: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize an array to hold the fields to update
    $updateFields = [];
    $params = [];

    // Check each field and add it to the updateFields array if it's not empty
    if (!empty($_POST['matricule'])) {
        $updateFields[] = 'matricule = ?';
        $params[] = htmlspecialchars(trim($_POST['matricule']));
    }
    if (!empty($_POST['name_vehicle'])) {
        $updateFields[] = 'name_vehicle = ?';
        $params[] = htmlspecialchars(trim($_POST['name_vehicle']));
    }
    if (!empty($_POST['owner_id'])) {
        $updateFields[] = 'owner_id = ?';
        $params[] = intval($_POST['owner_id']);
    }
    if (!empty($_POST['fuelType'])) {
        $updateFields[] = 'fuelType = ?';
        $params[] = htmlspecialchars(trim($_POST['fuelType']));
    }
    if (!empty($_POST['releaseYear'])) {
        $updateFields[] = 'releaseYear = ?';
        $params[] = intval($_POST['releaseYear']);
    }
    if (!empty($_POST['color'])) {
        $updateFields[] = 'color = ?';
        $params[] = htmlspecialchars(trim($_POST['color']));
    }
    if (isset($_POST['isAutomatic'])) {
        $updateFields[] = 'isAutomatic = ?';
        $params[] = intval($_POST['isAutomatic']);
    }
    if (!empty($_POST['pricePerDay'])) {
        $updateFields[] = 'pricePerDay = ?';
        $params[] = floatval($_POST['pricePerDay']);
    }
    if (!empty($_POST['pricePerHour'])) {
        $updateFields[] = 'pricePerHour = ?';
        $params[] = floatval($_POST['pricePerHour']);
    }
    if (!empty($_POST['description'])) {
        $updateFields[] = 'description = ?';
        $params[] = htmlspecialchars(trim($_POST['description']));
    }
    if (!empty($_POST['status'])) {
        $updateFields[] = 'status = ?';
        $params[] = htmlspecialchars(trim($_POST['status']));
    }
    if (!empty($_POST['picture'])) {
        $updateFields[] = 'picture = ?';
        $params[] = htmlspecialchars(trim($_POST['picture']));
    }
    if (isset($_POST['hasAirConditioning'])) {
        $updateFields[] = 'hasAirConditioning = ?';
        $params[] = intval($_POST['hasAirConditioning']);
    }
    if (isset($_POST['hasBluetooth'])) {
        $updateFields[] = 'hasBluetooth = ?';
        $params[] = intval($_POST['hasBluetooth']);
    }
    if (isset($_POST['hasCruiseControl'])) {
        $updateFields[] = 'hasCruiseControl = ?';
        $params[] = intval($_POST['hasCruiseControl']);
    }
    if (isset($_POST['hasAMFMStereoRadio'])) {
        $updateFields[] = 'hasAMFMStereoRadio = ?';
        $params[] = intval($_POST['hasAMFMStereoRadio']);
    }
    if (isset($_POST['hasLeatherInterior'])) {
        $updateFields[] = 'hasLeatherInterior = ?';
        $params[] = intval($_POST['hasLeatherInterior']);
    }

    // Only proceed if there are fields to update
    if (!empty($updateFields)) {
        try {
            // Build the SQL query dynamically
            $sql = "UPDATE vehicle SET " . implode(', ', $updateFields) . " WHERE vehicle_id = ?";
            $params[] = $vehicle_id;

            // Execute the query
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $_SESSION['success'] = "Vehicle updated successfully.";
            header("Location: ../admin/manage_vehicles.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to update vehicle: " . $e->getMessage();
            header("Location: ../admin/edit_vehicle.php?id=$vehicle_id");
            exit;
        }
    } else {
        $_SESSION['error'] = "No fields to update.";
        header("Location: ../admin/edit_vehicle.php?id=$vehicle_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Edit Vehicle</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="../admin/admin.php" class="hover:underline">Dashboard</a></li>
                    <li><a href="../admin/manage_useres.php" class="hover:underline">Users</a></li>
                    <li><a href="../admin/manage_vehicles.php" class="hover:underline">Vehicles</a></li>
                    <li><a href="../admin/statistics.php" class="hover:underline">Statistics</a></li>
                    <li><a href="logout.php" class="hover:underline">Logout</a></li>
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

        <!-- Display success messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success']); ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <section class="bg-white shadow-md rounded p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit Vehicle</h2>
            <form action="edit_vehicle.php?id=<?php echo $vehicle_id; ?>" method="POST">
                <div class="mb-4">
                    <label for="matricule" class="block text-gray-600">Matricule</label>
                    <input type="text" id="matricule" name="matricule" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="name_vehicle" class="block text-gray-600">Vehicle Name</label>
                    <input type="text" id="name_vehicle" name="name_vehicle"  
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="owner_id" class="block text-gray-600">Owner</label>
                    <select id="owner_id" name="owner_id"  
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach ($owners as $owner): ?>
                            <option value="<?php echo $owner['owner_id']; ?>">
                                <?php echo htmlspecialchars($owner['email']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="fuelType" class="block text-gray-600">Fuel Type</label>
                    <select id="fuelType" name="fuelType"  
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php foreach ($fuelTypes as $type): ?>
                            <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="releaseYear" class="block text-gray-600">Release Year</label>
                    <input type="number" id="releaseYear" name="releaseYear" min="1900" max="2100"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="color" class="block text-gray-600">Color</label>
                    <input type="text" id="color" name="color" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="isAutomatic" class="block text-gray-600">Is Automatic?</label>
                    <select id="isAutomatic" name="isAutomatic" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="pricePerDay" class="block text-gray-600">Price Per Day</label>
                    <input type="number" id="pricePerDay" name="pricePerDay"  
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="pricePerHour" class="block text-gray-600">Price Per Hour</label>
                    <input type="number" id="pricePerHour" name="pricePerHour" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-gray-600">Description</label>
                    <textarea id="description" name="description" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="mb-4">
                    <label for="status" class="block text-gray-600">Status</label>
                    <select id="status" name="status" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="available">Available</option>
                        <option value="rented">Rented</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="picture" class="block text-gray-600">Picture URL</label>                        <input type="text" id="picture" name="picture" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="hasAirConditioning" class="block text-gray-600">Air Conditioning</label>
                    <select id="hasAirConditioning" name="hasAirConditioning" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="hasBluetooth" class="block text-gray-600">Bluetooth</label>
                    <select id="hasBluetooth" name="hasBluetooth" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="hasCruiseControl" class="block text-gray-600">Cruise Control</label>
                    <select id="hasCruiseControl" name="hasCruiseControl" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="hasAMFMStereoRadio" class="block text-gray-600">AM/FM Stereo Radio</label>
                    <select id="hasAMFMStereoRadio" name="hasAMFMStereoRadio" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="hasLeatherInterior" class="block text-gray-600">Leather Interior</label>
                    <select id="hasLeatherInterior" name="hasLeatherInterior" 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <button type="submit" 
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Update Vehicle
                </button>
            </form>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>