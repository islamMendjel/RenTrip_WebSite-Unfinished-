<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['profil_id'])){
    $profil_id = $_SESSION['profil_id'];
    $stmt = $conn->prepare("SELECT profil_id, userName, password, type FROM profil WHERE profil_id = :profil_id AND status != 'Inactive'");
    $stmt->bindParam(':profil_id', $profil_id, PDO::PARAM_STR); // Binding parameters securely
    $stmt->execute();
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$profil) {
        $_SESSION['error'] = "Invalid user.";
        header("Location: ../login.php"); // Redirect to login page
        exit;
    }
}
// Check if the user is logged in and is an admin
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'admin') {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

// Validate CSRF token
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid CSRF token.";
    header("Location: ../admin/manage_users.php");
    exit;
}

// Delete user
if (isset($_GET['id'])) {
    $profil_id = $_GET['id'];
    try {
        // Start a transaction
        $conn->beginTransaction();

        // Delete from child tables first
        $tables = ['admin', 'tenant', 'owner', 'secretary', 'mechanic'];
        foreach ($tables as $table) {
            $stmt = $conn->prepare("UPDATE $table SET status = 'Inactive' WHERE profil_id = :profil_id");
            $stmt->execute(['profil_id' => $profil_id]);
        }

        // Delete from the profil table
        $stmt = $conn->prepare("UPDATE profil SET status = 'Inactive' WHERE profil_id = :profil_id");
        $stmt->execute(['profil_id' => $profil_id]);

        // Commit the transaction
        $conn->commit();

        $_SESSION['success'] = "User deleted successfully.";
    } catch (PDOException $e) {
        // Roll back the transaction on error
        $conn->rollBack();
        $_SESSION['error'] = "Failed to delete user: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "User ID not provided.";
}

header("Location: ../admin/manage_users.php");
exit;
?>