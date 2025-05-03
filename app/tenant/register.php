<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="../index.html" class="hover:underline">Home</a></li>
                    <li><a href="../login.php" class="hover:underline">Login</a></li>
                    <li><a href="../vehicles.php" class="hover:underline">Vehicles</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8">
        <section class="bg-white shadow-md rounded p-6 max-w-md mx-auto">
            <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center">Register</h3>
            <!-- Update the form action to point to register.php -->
            <form action="../api/register.php" method="POST" class="space-y-4">
                <div>
                    <label for="name" class="block text-gray-600">Username</label>
                    <input type="text" id="name" name="userName" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="name" class="block text-gray-600">Lience Number</label>
                    <input type="number" id="lienceNumber" name="lienceNumber" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-gray-600">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-gray-600">Password</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="confirm_password" class="block text-gray-600">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Register</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Already have an account? <a href="../login.php" class="text-blue-600 hover:underline">Login here</a></p>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>