<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Initialize pagination variables
$limit = 10; // Number of vehicles per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$totalPages = 1; // Default value to avoid undefined variable warnings

// Fetch all owners for the dropdown
$owners = [];
try {
    $stmt = $conn->query("
        SELECT owner.owner_id, profil.email 
        FROM owner 
        JOIN profil ON owner.profil_id = profil.profil_id AND owner.status != 'Inactive';
    ");
    $owners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to fetch owners: " . $e->getMessage();
}

// Predefined fuel types
$fuelTypes = ['Gasoline', 'Diesel', 'Electric', 'Hybrid'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: manage_vehicles.php");
        exit;
    }

    // Sanitize and validate inputs
    $matricule = htmlspecialchars(trim($_POST['matricule']));
    $name_vehicle = htmlspecialchars(trim($_POST['name_vehicle']));
    $owner_id = intval($_POST['owner_id']);
    $fuelType = htmlspecialchars(trim($_POST['fuelType']));
    $releaseYear = filter_var($_POST['releaseYear'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1900, 'max_range' => 2100]
    ]);
    $color = htmlspecialchars(trim($_POST['color']));
    $isAutomatic = isset($_POST['isAutomatic']) ? 1 : 0;
    $pricePerDay = filter_var($_POST['pricePerDay'], FILTER_VALIDATE_FLOAT);
    $pricePerHour = filter_var($_POST['pricePerHour'], FILTER_VALIDATE_FLOAT);
    $description = htmlspecialchars(trim($_POST['description']));
    $status = htmlspecialchars(trim($_POST['status']));
    $picture = htmlspecialchars(trim($_POST['picture']));
    $hasAirConditioning = isset($_POST['hasAirConditioning']) ? 1 : 0;
    $hasBluetooth = isset($_POST['hasBluetooth']) ? 1 : 0;
    $hasCruiseControl = isset($_POST['hasCruiseControl']) ? 1 : 0;
    $hasAMFMStereoRadio = isset($_POST['hasAMFMStereoRadio']) ? 1 : 0;
    $hasLeatherInterior = isset($_POST['hasLeatherInterior']) ? 1 : 0;

    // Validate required fields
    if (empty($matricule) || empty($name_vehicle) || empty($owner_id) || empty($fuelType) || $releaseYear === false || $pricePerDay === false) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: manage_vehicles.php");
        exit;
    }

    try {
        // Insert the new vehicle into the database
        $stmt = $conn->prepare("
            INSERT INTO vehicle (
                matricule, name_vehicle, owner_id, fuelType, releaseYear, color, isAutomatic, 
                pricePerDay, pricePerHour, description, status, picture, hasAirConditioning, 
                hasBluetooth, hasCruiseControl, hasAMFMStereoRadio, hasLeatherInterior
            ) VALUES (
                :matricule, :name_vehicle, :owner_id, :fuelType, :releaseYear, :color, :isAutomatic, 
                :pricePerDay, :pricePerHour, :description, :status, :picture, :hasAirConditioning, 
                :hasBluetooth, :hasCruiseControl, :hasAMFMStereoRadio, :hasLeatherInterior
            )
        ");

        $stmt->execute([
            ':matricule' => $matricule,
            ':name_vehicle' => $name_vehicle,
            ':owner_id' => $owner_id,
            ':fuelType' => $fuelType,
            ':releaseYear' => $releaseYear,
            ':color' => $color,
            ':isAutomatic' => $isAutomatic,
            ':pricePerDay' => $pricePerDay,
            ':pricePerHour' => $pricePerHour,
            ':description' => $description,
            ':status' => $status,
            ':picture' => $picture,
            ':hasAirConditioning' => $hasAirConditioning,
            ':hasBluetooth' => $hasBluetooth,
            ':hasCruiseControl' => $hasCruiseControl,
            ':hasAMFMStereoRadio' => $hasAMFMStereoRadio,
            ':hasLeatherInterior' => $hasLeatherInterior
        ]);

        $_SESSION['success'] = "Vehicle added successfully.";
        header("Location: manage_vehicles.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to add vehicle: " . $e->getMessage();
    }
}

// Search and Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build the base query
$query = "SELECT * FROM vehicle WHERE statu != 'Inactive'";
$conditions = [];
$params = [];

// Add search condition
if ($search) {
    $conditions[] = "(name_vehicle LIKE :search OR matricule LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add status filter condition
if ($status && in_array($status, ['available', 'rented', 'maintenance', 'lost'])) {
    $conditions[] = "status = :status";
    $params[':status'] = $status;
}

// Combine conditions
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Add pagination
$query .= " LIMIT :limit OFFSET :offset";

// Fetch filtered vehicles from the database
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of vehicles for pagination
    $totalQuery = "SELECT COUNT(*) FROM vehicle WHERE statu != 'Inactive'";
    if (!empty($conditions)) {
        $totalQuery .= " AND " . implode(" AND ", $conditions);
    }
    $totalStmt = $conn->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $totalStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $totalStmt->execute();
    $totalVehicles = $totalStmt->fetchColumn();
    $totalPages = ceil($totalVehicles / $limit);
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to fetch vehicles: " . $e->getMessage();
    $vehicles = []; // Set vehicles to an empty array
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Manage Vehicles</h1>
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

    <main class="container mx-auto py-8">
        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Vehicle Management</h2>
            <p class="text-gray-600">Add, update, or delete vehicles from the system.</p>
        </section>

        <!-- Add Vehicle Form -->
        <section class="mb-6">
            <button onclick="toggleAddVehicleForm()" 
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Add New Vehicle
            </button>
            <div id="addVehicleForm" class="bg-white shadow-md rounded p-6 mt-4 hidden">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Add Vehicle</h3>
                <form action="manage_vehicles.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <div class="mb-4">
                        <label for="matricule" class="block text-gray-600">Matricule</label>
                        <input type="text" id="matricule" name="matricule" required 
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="name_vehicle" class="block text-gray-600">Vehicle Name</label>
                        <input type="text" id="name_vehicle" name="name_vehicle" required 
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="owner_id" class="block text-gray-600">Owner</label>
                        <select id="owner_id" name="owner_id" required 
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
                        <select id="fuelType" name="fuelType" required 
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
                        <input type="number" id="pricePerDay" name="pricePerDay" required 
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
                        <label for="picture" class="block text-gray-600">Picture URL</label>
                        <input type="text" id="picture" name="picture" 
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
                        Add Vehicle
                    </button>
                </form>
            </div>
        </section>
        <section class="mb-6">
            <!-- Search and Filter Bar -->
            <form action="manage_vehicles.php" method="GET" class="flex items-center space-x-4">
                <input type="text" name="search" placeholder="Search by name or matricule" value="<?php echo htmlspecialchars($search); ?>"
                       class="border border-gray-300 rounded px-4 py-2 w-full">
                <select name="status" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">All status</option>
                    <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="rented" <?php echo $status === 'rented' ? 'selected' : ''; ?>>Rented</option>
                    <option value="maintenance" <?php echo $status === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    <option value="lost" <?php echo $status === 'lost' ? 'selected' : ''; ?>>Lost</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Filter
                </button>
            </form>
        </section>

        <!-- Vehicle List -->
        <section>
            <h3 class="text-xl font-bold text-gray-800 mb-4">Vehicle List</h3>
            <table class="w-full bg-white shadow-md rounded">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Matricule</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Owner</th>
                        <th class="px-4 py-2">Fuel Type</th>
                        <th class="px-4 py-2">Price Per Day</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vehicles)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-2 text-center text-gray-600">No Vehicles found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr class="text-center">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($vehicle['matricule']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($vehicle['name_vehicle']); ?></td>
                                <td class="px-4 py-2">
                                    <?php 
                                        $ownerEmail = '';
                                        foreach ($owners as $owner) {
                                            if ($owner['owner_id'] == $vehicle['owner_id']) {
                                                $ownerEmail = $owner['email'];
                                                break;
                                            }
                                        }
                                        echo htmlspecialchars($ownerEmail);
                                    ?>
                                </td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($vehicle['fuelType']); ?></td>
                                <td class="px-4 py-2">$<?php echo htmlspecialchars($vehicle['pricePerDay']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($vehicle['status']); ?></td>
                                <td class="px-4 py-2">
                                    <a href="../api/edit_vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>" 
                                       class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">Edit</a>
                                    <a href="#" onclick="confirmDelete(<?php echo $vehicle['vehicle_id']; ?>)" 
                                       class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex justify-center mt-6">
                <?php if ($page > 1): ?>
                    <a href="manage_vehicles.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-l hover:bg-blue-700">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="manage_vehicles.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="bg-blue-600 text-white px-4 py-2 mx-1 hover:bg-blue-700 <?php echo $i === $page ? 'bg-blue-700' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="manage_vehicles.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-r hover:bg-blue-700">Next</a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for Confirmation Dialog -->
    <script>
        function confirmDelete(vehicleId) {
            if (confirm("Are you sure you want to delete this vehicle?")) {
                window.location.href = `../api/delete_vehicle.php?id=${vehicleId}&csrf_token=<?php echo $csrfToken; ?>`;
            }
        }
        function toggleAddVehicleForm() {
            const form = document.getElementById('addVehicleForm');
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html>