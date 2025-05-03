<?php
session_start();
require '../config/db.php';
// Regenerate session ID for security
session_regenerate_id(true);

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
    $_SESSION['error'] = "You must be logged in as a tenant to access this page.";
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Store vehicle IDs in session
$vehicleIds = isset($_GET['vehicles']) ? $_GET['vehicles'] : '';
$_SESSION['vehicle_id'] = explode(',', $vehicleIds);

// Select the current tenant by profil_id
$profil_id = $_SESSION['profil_id'];
try {
    $stmt = $conn->prepare("SELECT tenant_id FROM tenant WHERE profil_id = :profil_id AND status != 'Inactive'");
    $stmt->execute(['profil_id' => $profil_id]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
    // Fetch all reports submitted by the current tenant
    $tenant_id = $tenant['tenant_id'];
    $stmt = $conn->prepare("SELECT reclamation_id, subject FROM reclamation WHERE tenant_id = :tenant_id ORDER BY reclamation_id DESC");
    $stmt->execute(['tenant_id' => $tenant_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "An error occurred while fetching your reports. Please try again later.";
    error_log("Database error: " . $e->getMessage()); // Log the error for debugging
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report a Problem - Tenant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Report a Problem</h1>
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

    <main class="container mx-auto py-8">
        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); // Clear the error message ?>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success']); ?></span>
            </div>
            <?php unset($_SESSION['success']); // Clear the success message ?>
        <?php endif; ?>

        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Report a Problem</h2>
            <p class="text-gray-600">Let us know about any issues with your rental.</p>
        </section>

        <section class="bg-white shadow-md rounded p-6">
            <form action="../api/submit_problem.php?vehicles=<?php echo htmlspecialchars($vehicleIds); ?>" method="POST" class="space-y-4">
                <div>
                    <label for="subject" class="block text-gray-600">Subject</label>
                    <input type="text" id="subject" name="subject" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="description" class="block text-gray-600">Content</label>
                    <textarea id="content" name="content" rows="4" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Submit Report</button>
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