<?php
session_start();
require '../config/db.php';

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: add_user.php");
        exit;
    }

    // Sanitize and validate inputs
    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $type = htmlspecialchars(trim($_POST['type']));

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: add_user.php");
        exit;
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

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
// Check if email already exists
    $stmt = $conn->prepare("SELECT profil_id FROM profil WHERE email = :email  AND status != 'Inactive'");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: add_user.php");
        exit;
    }

    try {
        // Insert into the profil table
        $stmt = $conn->prepare("INSERT INTO profil (firstName, lastName, email, password, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $passwordHash, $type]);
        $profil_id = $conn->lastInsertId();

        // Insert into the corresponding role-specific table
        $roleTables = [
            'tenant' => 'tenant',
            'owner' => 'owner',
            'admin' => 'admin',
            'secretary' => 'secretary',
            'mechanic' => 'mechanic',
        ];
        if (isset($roleTables[$type])) {
            $stmt = $conn->prepare("INSERT INTO {$roleTables[$type]} (profil_id) VALUES (:profil_id)");
            $stmt->execute(['profil_id' => $profil_id]);
        }

        $_SESSION['success'] = "User added successfully.";
        header("Location: ../admin/manage_users.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to add user: " . $e->getMessage();
        header("Location: add_user.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">RenTriP Vehicle Rental - Add User</h1>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="../admin/admin.php" class="hover:underline">Dashboard</a></li>
                    <li><a href="../admin/manage_users.php" class="hover:underline">Users</a></li>
                    <li><a href="../admin/manage_vehicles.php" class="hover:underline">Vehicles</a></li>
                    <li><a href="../admin/statistics.php" class="hover:underline">Statistics</a></li>
                    <li><a href="logout.php" class="hover:underline">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8">
        <!-- Display error messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Display success messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success']); ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <section class="bg-white shadow-md rounded p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Add User</h2>
            <form action="add_user.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <div class="mb-4">
                    <label for="firstName" class="block text-gray-600">First Name</label>
                    <input type="text" id="firstName" name="firstName" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="lastName" class="block text-gray-600">Last Name</label>
                    <input type="text" id="lastName" name="lastName" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-600">Email</label>
                    <input type="email" id="email" name="email" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-600">Password</label>
                    <input type="password" id="password" name="password" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="type" class="block text-gray-600">Role</label>
                    <select id="type" name="type" required 
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="admin">Admin</option>
                        <option value="secretary">Secretary</option>
                        <option value="owner">Owner</option>
                        <option value="tenant">Tenant</option>
                        <option value="mechanic">Mechanic</option>
                    </select>
                </div>
                <button type="submit" 
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add User
                </button>
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