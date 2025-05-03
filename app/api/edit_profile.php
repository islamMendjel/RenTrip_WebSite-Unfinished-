<?php
session_start();

// Include the database connection file
require_once '../config/db.php'; // Corrected: Added the database connection file inclusion

if (isset($_SESSION['profil_id'])){
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT profil_id, userName, password, type FROM profil WHERE profil_id = :profil_id  AND status != 'Inactive'");
    $stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_STR); // Binding parameters securely
    $stmt->execute();
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$profil) {
        $_SESSION['error'] = "Invalid user.";
        header("Location: ../login.php"); // Redirect to login page
        exit;
    }
}
// Check if the user is logged in
if (!isset($_SESSION['profil_id'])) {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

$profil_id = $_SESSION['profil_id'];

// Display success or error messages
if (isset($_GET['success'])) {
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4'>Profile updated successfully!</div>";
}
if (isset($_GET['error']) && $_GET['error'] === 'no_fields') {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4'>No fields were provided for updating.</div>";
}

// Corrected: Prepare and execute the database query securely
$stmt = $conn->prepare("SELECT firstName, lastName, phone, email, userName, password, type FROM profil WHERE profil_id = :profil_id  AND status != 'Inactive'");
$stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_INT); // Corrected: Use the correct parameter binding
$stmt->execute();
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

if ($profil) {
    // Regenerate session ID for security
    session_regenerate_id(true);

    // Store user data in session
    $_SESSION['firstName'] = $profil['firstName'];
    $_SESSION['lastName'] = $profil['lastName'];
    $_SESSION['phone'] = $profil['phone'];
    $_SESSION['email'] = $profil['email'];
    $_SESSION['userName'] = $profil['userName'];
    $_SESSION['password'] = $profil['password'];
    $_SESSION['type'] = $profil['type'];
} else {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4'>Profile data could not be loaded.</div>";
    exit;
}

// Initialize $redirectUrl with a default value
$redirectUrl = "../{$profil['type']}/{$profil['type']}.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Edit Profile</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="<?php echo $redirectUrl ?>" class="hover:underline">Dashboard</li>
                    <li><a href="../api/logout.php" class="hover:underline">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8">
        <section class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Edit Your Profile</h2>
            <p class="text-gray-600">Update your personal information here.</p>
        </section>

        <section class="bg-white shadow-md rounded p-6">
        <!-- Corrected: Escaped values for security -->
        <form action="update_profile.php" method="POST" class="space-y-4" enctype="multipart/form-data">
            <div>
                <label for="firstName" class="block text-gray-600">First Name</label>
                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($_SESSION['firstName'] ?? '', ENT_QUOTES); ?>"  
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="lastName" class="block text-gray-600">Last Name</label>
                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($_SESSION['lastName'] ?? '', ENT_QUOTES); ?>"  
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="phone" class="block text-gray-600">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? '', ENT_QUOTES); ?>" 
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="email" class="block text-gray-600">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES); ?>"  
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="userName" class="block text-gray-600">Username</label>
                <input type="text" id="userName" name="userName" value="<?php echo htmlspecialchars($_SESSION['userName'] ?? '', ENT_QUOTES); ?>"  
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="block text-gray-600">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password" 
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <?php if ($_SESSION['type'] != 'tenant') : ?>
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
            <?php endif; ?>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Profile</button>
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