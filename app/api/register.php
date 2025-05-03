<?php
// Include the database connection file
require '../config/db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $userName = $_POST['userName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $lienceNumber = $_POST['lienceNumber'];

    // Validate inputs
    if (empty($userName) || empty($email) || empty($password) || empty($confirm_password)) {
        die("Please fill in all fields.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT profil_id FROM profil WHERE email = :email  AND status != 'Inactive'");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        die("Email already registered.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $type = 'tenant';
    // Insert profil into the database
    $stmt = $conn->prepare("INSERT INTO profil (userName, email, password, type) VALUES (:userName, :email, :password, :type)");
    $stmt->execute([
        'userName' => $userName,
        'email' => $email,
        'password' => $hashed_password,
        'type' => $type
    ]);

    // Get the last inserted profile ID
    $profil_id = $conn->lastInsertId();

    // Now you can use $profil_id in another query
    // Example: Insert into the tenant table
    $stmt = $conn->prepare("INSERT INTO tenant (lienceNumber, profil_id) VALUES (:lienceNumber, :profil_id)");
    $stmt->execute([
        'profil_id' => $profil_id,
        'lienceNumber' => $lienceNumber
    ]);
    // Redirect to login page after successful registration
    header("Location: ../login.php");
    exit;
}
?>