<?php
session_start();
require '../config/db.php';

// Check if the user is logged in
if (!isset($_SESSION['profil_id'])) {
    $_SESSION['error'] = "You must be logged in to access this page.";
    header("Location: ../login.php");
    exit;
}

// Fetch user profile
$profil_id = $_SESSION['profil_id'];
$stmt = $conn->prepare("SELECT profil_id, userName, password, type FROM profil WHERE profil_id = :profil_id  AND status != 'Inactive'");
$stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_INT);
$stmt->execute();
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profil) {
    $_SESSION['error'] = "Invalid user.";
    header("Location: ../login.php");
    exit;
}

// Check if the user is an admin
if ($profil['type'] !== 'admin') {
    $_SESSION['error'] = "You do not have permission to access this page.";
    header("Location: ../login.php");
    exit;
}
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.14/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Statistics</h1>
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

    <main class="container mx-auto py-8" id="statsApp">
        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">System Statistics</h2>
            <p class="text-gray-600">Analyze the performance and usage metrics of the platform.</p>
        </section>

        <section class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Vehicle Rental Statistics</h3>
            <canvas id="rentalChart" class="w-full max-w-2xl mx-auto"></canvas>
        </section>

        <section>
            <h3 class="text-xl font-bold text-gray-800 mb-4">User Engagement</h3>
            <canvas id="userChart" class="w-full max-w-2xl mx-auto"></canvas>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        new Vue({
            el: '#statsApp',
            data: {
                rentalData: { labels: [], data: [] },
                userData: { labels: [], data: [] }
            },
            mounted() {
                this.fetchRentalData();
                this.fetchUserData();
            },
            methods: {
                fetchRentalData() {
                    fetch('../api/get_rental_stats.php')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Failed to fetch rental data.');
                            }
                            return response.json();
                        })
                        .then(data => {
                            this.rentalData = data;
                            this.renderRentalChart();
                        })
                        .catch(error => {
                            console.error('Error fetching rental data:', error);
                            alert('An error occurred while fetching rental data. Please try again later.');
                        });
                },
                fetchUserData() {
                    fetch('../api/get_user_stats.php')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Failed to fetch user data.');
                            }
                            return response.json();
                        })
                        .then(data => {
                            this.userData = data;
                            this.renderUserChart();
                        })
                        .catch(error => {
                            console.error('Error fetching user data:', error);
                            alert('An error occurred while fetching user data. Please try again later.');
                        });
                },
                renderRentalChart() {
                    const ctx = document.getElementById('rentalChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.rentalData.labels,
                            datasets: [{
                                label: 'Rentals Per Month',
                                data: this.rentalData.data,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                renderUserChart() {
                    const ctx = document.getElementById('userChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: this.userData.labels,
                            datasets: [{
                                label: 'User Distribution',
                                data: this.userData.data,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(75, 192, 192, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>