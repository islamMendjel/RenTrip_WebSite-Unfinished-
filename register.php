<?php
// Include the database connection file
require 'app/config/db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $userName = $_POST['userName'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $region = $_POST['region'];

    // Validate inputs
    if (empty($userName) || empty($email) || empty($password) || empty($confirm_password)) {
        die("Please fill in all fields.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT profil_id FROM profil WHERE email = :email AND status != 'Inactive'");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        die("Email already registered.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $type = 'admin';
    // Insert profil into the database
    $stmt = $conn->prepare("INSERT INTO profil (firstName, lastName, phone, email, userName, password, type) VALUES (:firstName, :lastName, :phone, :email, :userName, :password, :type)");
    $stmt->execute([
        'firstName' => $firstName,
        'lastName' => $lastName,
        'phone' => $phone,
        'email' => $email,
        'userName' => $userName,
        'password' => $hashed_password,
        'type' => $type
    ]);

    // Get the last inserted profile ID
    $profil_id = $conn->lastInsertId();

    // Now you can use $profil_id in another query
    // Example: Insert into the tenant table
    $stmt = $conn->prepare("INSERT INTO admin (region, profil_id) VALUES (:region, :profil_id)");
    $stmt->execute([
        'profil_id' => $profil_id,
        'region' => $region
    ]);
    // Redirect to login page after successful registration
    header("Location: app/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register a New Admin - Vehicle Rental Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">IKAR Vehicle Rental</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="../index.html" class="hover:underline">Home</a></li>
                    <li><a href="app/login.php" class="hover:underline">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8">
        <section class="bg-white shadow-md rounded p-6 max-w-md mx-auto">
            <h3 class="text-2xl font-bold text-gray-800 mb-4 text-center">Register</h3>
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="firstName" class="block text-gray-600">First Name</label>
                    <input type="text" id="firstName" name="firstName" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="lastName" class="block text-gray-600">Last Name</label>
                    <input type="text" id="lastName" name="lastName" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="phone" class="block text-gray-600">Phone</label>
                    <input type="tel" id="phone" name="phone" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-gray-600">Email</label>
                    <input type="email" id="email" name="email" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="userName" class="block text-gray-600">Username</label>
                    <input type="text" id="userName" name="userName" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-gray-600">Password</label>
                    <input type="password" id="password" name="password" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="confirm_password" class="block text-gray-600">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="region" class="block text-gray-600">Region</label>
                    <select id="region" name="region" required 
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" disabled selected>Select your region</option>
                        <option value="North">North</option>
                        <option value="South">South</option>
                        <option value="East">East</option>
                        <option value="West">West</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">Register</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Already have an account? <a href="app/login.php" class="text-blue-600 hover:underline">Login here</a></p>
        </section>
    </main>

    <footer class="bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 IKAR Vehicle Rental. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>