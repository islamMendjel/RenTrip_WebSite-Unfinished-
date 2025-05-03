<?php
session_start();

// Check if the user is logged in and is a secretary
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'secretary') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

require '../config/db.php';

// Fetch all reservations from the database
$stmt = $conn->query("
    SELECT reservations.id, vehicles.name AS vehicle_name, profil.name AS tenant_name, 
           reservations.rental_date, reservations.return_date, reservations.status
    FROM reservations
    JOIN vehicles ON reservations.vehicle_id = vehicles.id
    JOIN profil ON reservations.tenant_id = profil.id
");
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $reservationId = $_POST['reservation_id'];
    $status = $_POST['status'];

    // Update reservation status in the database
    $stmt = $conn->prepare("UPDATE reservations SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $status, 'id' => $reservationId]);

    // Refresh the page to reflect the updated status
    header("Location: manage_reservations.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations - Secretary Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Manage Reservations</h1>
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
        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Reservations</h2>
            <p class="text-gray-600">Manage all reservations here.</p>
        </section>

        <section class="bg-white shadow-md rounded p-6">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Vehicle</th>
                        <th class="px-4 py-2">Tenant</th>
                        <th class="px-4 py-2">Rental Date</th>
                        <th class="px-4 py-2">Return Date</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr class="text-center">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($reservation['id']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($reservation['vehicle_name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($reservation['tenant_name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($reservation['rental_date']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($reservation['return_date']); ?></td>
                            <td class="px-4 py-2">
                                <form action="manage_reservations.php" method="POST" class="inline">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" 
                                        class="px-2 py-1 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $reservation['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="canceled" <?php echo $reservation['status'] === 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td class="px-4 py-2">
                                <a href="edit_reservation.php?id=<?php echo $reservation['id']; ?>" 
                                   class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">Edit</a>
                                <a href="delete_reservation.php?id=<?php echo $reservation['id']; ?>" 
                                   class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700" 
                                   onclick="return confirm('Are you sure you want to delete this reservation?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>