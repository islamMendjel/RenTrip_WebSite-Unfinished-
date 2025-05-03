<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.14/dist/vue.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="index.html" class="hover:underline">Home</a></li>
                    <?php if (isset($_SESSION['profil_id']) && $_SESSION['type'] == 'tenant'): ?>
                        <!-- Display these links if the user is logged in -->
                        <li><a href="tenant/tenant.php" class="hover:underline">Dashboard</a></li>
                        <li><a href="vehicles.php" class="hover:underline">Vehicles</a></li>
                        <li><a href="api/logout.php" class="hover:underline">Logout</a></li>
                    <?php else: ?>
                        <!-- Display these links if the user is not logged in -->
                        <li><a href="login.php" class="hover:underline">Login</a></li>
                        <li><a href="vehicles.php" class="hover:underline">Vehicles</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8" id="app">
        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); // Clear the error message ?>
        <?php endif; ?>

        <section class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Available Vehicles</h2>
            <p class="text-gray-600">Browse our wide selection of vehicles ready for rent.</p>
        </section>

        <!-- Search and Filter Section -->
        <section class="mb-6">
            <div class="flex justify-between items-center">
                <input type="text" v-model="searchQuery" placeholder="Search vehicles..." 
                    class="w-1/2 px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button @click="fetchVehicles" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Refresh List</button>
            </div>
        </section>

        <!-- Vehicle List -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="vehicle in filteredVehicles" :key="vehicle.vehicle_id" class="bg-white shadow-md rounded p-4">
                <img :src="vehicle.picture" alt="Vehicle image" class="w-full h-48 object-cover mb-4 rounded">
                <h3 class="text-xl font-bold text-gray-800">{{ vehicle.name_vehicle }}</h3>
                <p class="text-gray-600">Type: {{ vehicle.fuelType }}</p>
                <p class="text-gray-600">Price: ${{ vehicle.pricePerDay }} per day</p>
                <button @click="selectVehicle(vehicle)" 
                    class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Select</button>
                <button @click="reportVehicle(vehicle)" 
                    class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Report</button>
            </div>
        </section>

        <!-- Selected Vehicles Section -->
        <section v-if="selectedVehicles.length > 0" class="mt-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">Selected Vehicles</h3>
            <div class="space-y-4">
                <div v-for="vehicle in selectedVehicles" :key="vehicle.vehicle_id" class="bg-white shadow-md rounded p-4">
                    <h4 class="text-lg font-bold text-gray-800">{{ vehicle.name_vehicle }}</h4>
                    <p class="text-gray-600">Price: ${{ vehicle.pricePerDay }} per day</p>
                </div>
            </div>
            <button @click="proceedToConfirmation" 
                class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Proceed to Confirmation</button>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        new Vue({
            el: '#app',
            data: {
                searchQuery: '',
                vehicles: [], // Initially empty, will be populated from the database
                selectedVehicles: [] // Stores selected vehicles
            },
            computed: {
                filteredVehicles() {
                    return this.vehicles.filter(vehicle =>
                        vehicle.name_vehicle.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                }
            },
            methods: {
                fetchVehicles() {
                    fetch('api/get_available_vehicles.php')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Failed to fetch vehicles.');
                            }
                            return response.json();
                        })
                        .then(data => {
                            this.vehicles = data; // Update the vehicles array
                        })
                        .catch(error => {
                            console.error('Error fetching vehicles:', error);
                            alert('An error occurred while fetching vehicles. Please try again later.');
                        });
                },
                selectVehicle(vehicle) {
                    if (!this.selectedVehicles.some(v => v.vehicle_id === vehicle.vehicle_id)) {
                        this.selectedVehicles.push(vehicle);
                    }
                },
                reportVehicle(vehicle) {
                    // Add the vehicle to the selectedVehicles array if not already present
                    if (!this.selectedVehicles.some(v => v.vehicle_id === vehicle.vehicle_id)) {
                        this.selectedVehicles.push(vehicle);
                    }

                    // Redirect to the report page with selected vehicle IDs
                    const vehicleIds = this.selectedVehicles.map(v => v.vehicle_id);
                    window.location.href = `tenant/report.php?vehicles=${vehicleIds.join(',')}`;
                },
                proceedToConfirmation() {
                    if (this.selectedVehicles.length === 0) {
                        alert('Please select one vehicle to proceed.');
                        return;
                    }

                    // Redirect to the confirmation page with selected vehicle IDs
                    const vehicleIds = this.selectedVehicles.map(v => v.vehicle_id);
                    window.location.href = `tenant/confirmation.php?vehicles=${vehicleIds.join(',')}`;
                }
            },
            mounted() {
                this.fetchVehicles(); // Fetch vehicles when the page loads
            }
        });
    </script>
</body>
</html>