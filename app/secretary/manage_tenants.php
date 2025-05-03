<?php
session_start();
require '../config/db.php';

// Check if the user is logged in
if (!isset($_SESSION['profil_id'])) {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Fetch user profile
$profil_id = $_SESSION['profil_id'];
$stmt = $conn->prepare("SELECT profil_id, userName, password, type FROM profil WHERE profil_id = :profil_id");
$stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_INT); // Use PARAM_INT for integers
$stmt->execute();
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profil) {
    $_SESSION['error'] = "Invalid user.";
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Check if the user is a secretary
if ($profil['type'] !== 'secretary') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Pagination
$limit = 10; // Number of users per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Initialize $totalPages to a default value
$totalPages = 1;

// Build the base query
$query = "SELECT * 
          FROM profil
          JOIN tenant ON profil.profil_id = tenant.profil_id
          WHERE tenant.isValidated = 1 
          AND tenant.isExcluded = 0";
$conditions = [];
$params = [];

// Combine conditions (if any)
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Add pagination
$query .= " LIMIT :limit OFFSET :offset";

// Fetch filtered profiles from the database
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $profils = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total number of users for pagination
    $totalQuery = "SELECT COUNT(*) 
                   FROM profil
                   JOIN tenant ON profil.profil_id = tenant.profil_id
                   WHERE tenant.isValidated = 1 
                   AND tenant.isExcluded = 0";
    if (!empty($conditions)) {
        $totalQuery .= " AND " . implode(" AND ", $conditions);
    }
    $totalStmt = $conn->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $totalStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $totalStmt->execute();
    $totalUsers = $totalStmt->fetchColumn();
    $totalPages = ceil($totalUsers / $limit); // Update $totalPages based on the query result
} catch (PDOException $e) {
    // Handle database errors
    $_SESSION['error'] = "Failed to fetch profiles: " . $e->getMessage();
    $profils = []; // Set profiles to an empty array
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
        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); // Clear the error message ?>
        <?php endif; ?>

        <!-- Section 1: Quick Actions -->
        <section class="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Excluded Tenants</h3>
                <p class="text-gray-600">Review and Revoke Excluded Tenants.</p>
                <a href="excluded.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Non-Validated Tenants</h3>
                <p class="text-gray-600">Review and Accept or Refuse New Tenants.</p>
                <a href="non_validated.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
        </section>

        <!-- Section 2: User List -->
        <section>
            <h3 class="text-xl font-bold text-gray-800 mb-4">User List</h3>
            <table class="w-full bg-white shadow-md rounded">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">First Name</th>
                        <th class="px-4 py-2">Last Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Role</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($profils)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-600">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($profils as $profil): ?>
                            <tr class="text-center">
                                <td class="px-4 py-2"><?php echo htmlspecialchars($profil['firstName']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($profil['lastName']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($profil['email']); ?></td>
                                <td class="px-4 py-2"><?php echo htmlspecialchars($profil['type']); ?></td>
                                <td class="px-4 py-2">
                                    <a href="../api/edit_user.php?id=<?php echo $profil['profil_id']; ?>" 
                                       class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">Edit</a>
                                    <a href="#" onclick="confirmDelete(<?php echo $profil['profil_id']; ?>)" 
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
                    <a href="manage_tenants.php?page=<?php echo $page - 1; ?>" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-l hover:bg-blue-700">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="manage_tenants.php?page=<?php echo $i; ?>" 
                       class="bg-blue-600 text-white px-4 py-2 mx-1 hover:bg-blue-700 <?php echo $i === $page ? 'bg-blue-700' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="manage_tenants.php?page=<?php echo $page + 1; ?>" 
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
        function confirmDelete(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = `../api/delete_user.php?id=${userId}&csrf_token=<?php echo $csrfToken; ?>`;
            }
        }
    </script>
</body>
</html>