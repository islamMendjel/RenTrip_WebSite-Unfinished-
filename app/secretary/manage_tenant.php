<?php
session_start();

// Check if the user is logged in and is a secretary
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'secretary') {
    header("Location: ../login.php");
    exit;
}

require '../config/db.php';

function fetchValidatedAndNotExcludedTenants($db) {
    // Query for validated and not excluded tenants
    $query = "SELECT * FROM tenants WHERE validated = 1 AND excluded = 0";
    return $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
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
        <section class="mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Excluded Tenants</h3>
                <p class="text-gray-600">Review and Revoke Excluded Tenants.</p>
                <a href="excluded.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
            <div class="bg-white shadow-md rounded p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manage Non-Validated Tenants</h3>
                <p class="text-gray-600">Review and Accept or Refuse New Tenants.</p> <!-- Fixed text -->
                <a href="non_validated.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Go</a>
            </div>
        </section>
        <!-- Section 2: Manage Validated and Not Excluded Tenants -->
        <section class="bg-white shadow-md rounded p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Manage Validated and Not Excluded Tenants</h2>
            <p class="text-gray-600">Manage all tenants here.</p>
            <table class="min-w-full bg-white border rounded shadow">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-2 px-4 text-left">ID</th>
                        <th class="py-2 px-4 text-left">Name</th>
                        <th class="py-2 px-4 text-left">Email</th>
                        <th class="py-2 px-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $validated_tenants = fetchValidatedAndNotExcludedTenants($db);
                    foreach ($validated_tenants as $tenant) {
                        echo "<tr>
                            <td class='py-2 px-4'>{$tenant['id']}</td>
                            <td class='py-2 px-4'>{$tenant['name']}</td>
                            <td class='py-2 px-4'>{$tenant['email']}</td>
                            <td class='py-2 px-4 text-center'>
                                <a href='#' onclick='confirmExclude({$tenant["id"]})' class='text-yellow-600 hover:underline'>Add to Excluded List</a> | 
                                <a href='#' onclick='showReservations({$tenant["id"]})' class='text-blue-600 hover:underline'>Establish Contract</a> | 
                                <a href='#' onclick='showInvoiceReservations({$tenant["id"]})' class='text-purple-600 hover:underline'>Establish Invoice</a>
                            </td>
                        </tr>";
                    }
                ?>

                </tbody>
            </table>
        </section>
        <div id="reservationModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
            <div class="bg-white w-3/4 max-w-2xl rounded shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">Reservations Without Contracts</h2>
                <table class="min-w-full bg-white border rounded shadow">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 text-left">Reservation ID</th>
                            <th class="py-2 px-4 text-left">Start Date</th>
                            <th class="py-2 px-4 text-left">End Date</th>
                            <th class="py-2 px-4 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody id="reservationList">
                        <!-- Dynamic content will be injected here -->
                    </tbody>
                </table>
                <button onclick="closeModal()" class="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>
        <div id="invoiceModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
            <div class="bg-white w-3/4 max-w-2xl rounded shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4">Reservations Without Invoices</h2>
                <table class="min-w-full bg-white border rounded shadow">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 text-left">Reservation ID</th>
                            <th class="py-2 px-4 text-left">Start Date</th>
                            <th class="py-2 px-4 text-left">End Date</th>
                            <th class="py-2 px-4 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceReservationList">
                        <!-- Dynamic content will be injected here -->
                    </tbody>
                </table>
                <button onclick="closeInvoiceModal()" class="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                    Close
                </button>
            </div>
        </div>


    </main>

    <script>
        new Vue({
            el: '#secretaryApp',
            methods: {
                navigateTo(page) {
                    // Implement navigation logic if needed
                    switch (page) {
                        case 'excluded':
                            window.location.href = 'excluded.php';
                            break;
                        case 'non_validated':
                            window.location.href = 'non_validated.php';
                            break;
                        default:
                            console.log('Invalid page');
                    }
                }
            }
        });
        function confirmExclude(tenantId) {
            // Display confirmation dialog
            if (confirm("Are you sure you want to add this tenant to the excluded list?")) {
                // Redirect to the PHP script for excluding the tenant
                window.location.href = `../api/actions.php?action=exclude&tenant_id=${tenantId}`;
            }
        }
        function showReservations(tenantId) {
            // Fetch reservations without contracts for the tenant
            fetch(`../api/actions.php?action=fetch&tenant_id=${tenantId}`)
                .then((response) => response.json())
                .then((data) => {
                    const reservationList = document.getElementById('reservationList');
                    reservationList.innerHTML = '';

                    data.forEach((reservation) => {
                        reservationList.innerHTML += `
                            <tr>
                                <td class="py-2 px-4">${reservation.id}</td>
                                <td class="py-2 px-4">${reservation.start_date}</td>
                                <td class="py-2 px-4">${reservation.end_date}</td>
                                <td class="py-2 px-4 text-center">
                                    <button onclick="createContract(${reservation.id})" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                                        Establish Contract
                                    </button>
                                </td>
                            </tr>`;
                    });

                    // Show modal
                    document.getElementById('reservationModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('reservationModal').classList.add('hidden');
        }

        function createContract(reservationId) {
            // Send request to create a contract
            fetch(`../api/actions.php?action=contract&reservation_id=${reservationId}`, {
                method: 'POST',
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert('Contract successfully created!');
                        closeModal();
                    } else {
                        alert('Failed to create contract. Please try again.');
                    }
                });
        }

        function showInvoiceReservations(tenantId) {
            // Fetch reservations without invoices for the tenant
            fetch(`../api/actions.php?action=fetch_invoice_reservations&tenant_id=${tenantId}`)
                .then((response) => response.json())
                .then((data) => {
                    const reservationList = document.getElementById('invoiceReservationList');
                    reservationList.innerHTML = '';

                    data.forEach((reservation) => {
                        reservationList.innerHTML += `
                            <tr>
                                <td class="py-2 px-4">${reservation.id}</td>
                                <td class="py-2 px-4">${reservation.start_date}</td>
                                <td class="py-2 px-4">${reservation.end_date}</td>
                                <td class="py-2 px-4 text-center">
                                    <button onclick="createInvoice(${reservation.id})" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                                        Establish Invoice
                                    </button>
                                </td>
                            </tr>`;
                    });

                    // Show modal
                    document.getElementById('invoiceModal').classList.remove('hidden');
                });
        }

        function closeInvoiceModal() {
            document.getElementById('invoiceModal').classList.add('hidden');
        }

        function createInvoice(reservationId) {
            // Send request to create an invoice
            fetch(`../api/actions.php?action=invoice&reservation_id=${reservationId}`, {
                method: 'POST',
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert('Invoice successfully created!');
                        closeInvoiceModal();
                    } else {
                        alert('Failed to create invoice. Please try again.');
                    }
                });
        }

    </script>

    <?php if (isset($_GET['message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

</body>
</html>