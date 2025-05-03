<?php
session_start();

// Ensure the user is logged in and has a secretary profile
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'secretary') {
    header("Location: ../login.php"); // Redirect to login if unauthorized
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles - Secretary Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.14/dist/vue.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Manage Vehicles</h1>
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

    <main class="container mx-auto py-8" id="vehiclesApp">
        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Vehicle Management</h2>
            <p class="text-gray-600">Add, delete vehicles from the system.</p>
        </section>

        <section class="mb-6">
            <button @click="showAddVehicleForm = !showAddVehicleForm" 
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Add New Vehicle
            </button>

            <div v-if="showAddVehicleForm" class="bg-white shadow-md rounded p-6 mt-4">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Add Vehicle</h3>
                <form @submit.prevent="addVehicle">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-600">Vehicle Name</label>
                        <input type="text" id="name" v-model="newVehicle.name" required 
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="type" class="block text-gray-600">Vehicle Type</label>
                        <select id="type" v-model="newVehicle.type" required 
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Sedan">Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="Truck">Truck</option>
                            <option value="Van">Van</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-gray-600">Price Per Day</label>
                        <input type="number" id="price" v-model="newVehicle.pricePerDay" required 
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Add Vehicle
                    </button>
                </form>
            </div>
        </section>

        <section>
            <h3 class="text-xl font-bold text-gray-800 mb-4">Vehicle List</h3>
            <table class="w-full bg-white shadow-md rounded">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">Price Per Day</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="vehicle in vehicles" :key="vehicle.id" class="text-center">
                        <td class="px-4 py-2">{{ vehicle.name }}</td>
                        <td class="px-4 py-2">{{ vehicle.type }}</td>
                        <td class="px-4 py-2">${{ vehicle.pricePerDay }}</td>
                        <td class="px-4 py-2">
                            <button @click="editVehicle(vehicle)" 
                                class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600">Edit</button>
                            <button @click="deleteVehicle(vehicle.id)" 
                                class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        new Vue({
            el: '#vehiclesApp',
            data: {
                showAddVehicleForm: false,
                newVehicle: {
                    name: '',
                    type: 'Sedan',
                    pricePerDay: ''
                },
                vehicles: [] // Initially empty, will be populated from the database
            },
            methods: {
                fetchVehicles() {
                    fetch('../api/get_vehicles.php')
                        .then(response => response.json())
                        .then(data => {
                            this.vehicles = data;
                        });
                },
                addVehicle() {
                    fetch('../api/add_vehicle.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(this.newVehicle)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.fetchVehicles(); // Refresh the vehicle list
                            this.newVehicle = { name: '', type: 'Sedan', pricePerDay: '' }; // Reset form
                            this.showAddVehicleForm = false;
                        } else {
                            alert(data.message);
                        }
                    });
                },
                editVehicle(vehicle) {
                    alert(`Edit functionality for ${vehicle.name} will be implemented.`);
                },
                deleteVehicle(id) {
                    if (confirm('Are you sure you want to delete this vehicle?')) {
                        fetch(`../api/delete_vehicle.php?id=${id}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.fetchVehicles(); // Refresh the vehicle list
                                } else {
                                    alert(data.message);
                                }
                            });
                    }
                }
            },
            mounted() {
                this.fetchVehicles(); // Fetch vehicles when the page loads
            }
        });
    </script>
</body>
</html>