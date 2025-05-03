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

require '../config/db.php';

// Fetch the report details based on the report ID
if (isset($_GET['reclamation_id'])) {
    // Validate reclamation_id as an integer
    $report_id = filter_var($_GET['reclamation_id'], FILTER_VALIDATE_INT);
    if (!$report_id) {
        $_SESSION['error'] = "Invalid report ID.";
        header("Location: report_problem.php"); // Redirect if reclamation_id is invalid
        exit;
    }

    // Select the current tenant by profil_id
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT tenant_id FROM tenant WHERE profil_id = :profil_id AND status != 'Inactive'");
    $stmt->execute(['profil_id' => $profil_id]);
    $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tenant) {
        throw new Exception("Tenant profile not found.");
    }

    $tenant_id = $tenant['tenant_id'];

    try {
        // Ensure the report belongs to the current tenant
        $stmt = $conn->prepare("SELECT * FROM reclamation WHERE reclamation_id = :reclamation_id AND tenant_id = :tenant_id");
        $stmt->execute(['reclamation_id' => $report_id, 'tenant_id' => $tenant_id]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$report) {
            $_SESSION['error'] = "The report does not exist or you do not have permission to view it.";
            header("Location: report_problem.php"); // Redirect if the report doesn't belong to the tenant
            exit;
        }
    } catch (PDOException $e) {
        // Log the error and redirect
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while fetching the report. Please try again later.";
        header("Location: report_problem.php");
        exit;
    }
} else {
    $_SESSION['error'] = "No report ID provided.";
    header("Location: report_problem.php"); // Redirect if no report ID is provided
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details - Tenant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Report Details</h1>
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
        <section class="bg-white shadow-md rounded p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Report: <?php echo htmlspecialchars($report['subject']); ?></h2>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700">Description</h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($report['content']); ?></p>
            </div>

            <!-- Display reply if available -->
            <?php if (!empty($report['reply'])): ?>
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-700">Reply</h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($report['reply']); ?></p>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No reply yet.</p>
            <?php endif; ?>

            <div class="mt-6">
                <a href="report_problem.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Back to Reports</a>
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