<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="index.html" class="hover:underline">Home</a></li>
                    <li><a href="tenant/register.php" class="hover:underline">Register</a></li>
                    <li><a href="vehicles.php" class="hover:underline">Vehicles</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8">
        <section class="bg-white shadow-md rounded p-6 max-w-md mx-auto">
            <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center">Login</h3>
            <!-- Ensure the form action points to login.php in the API folder -->
            <form action="api/login.php" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block text-gray-600">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-gray-600">Password</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <!-- Added correct placement of PHP code for error handling -->
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']); // Clear the error message after displaying it
                }
                ?>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Don't have an account? <a href="tenant/register.php" class="text-blue-600 hover:underline">Register here</a></p>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 RenTriP Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>
