<?php
session_start(); // Start the session

// Include the database connection file
require '../config/db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Sanitize email input
    $password = $_POST['password']; // Password does not need sanitization

    // Validate inputs
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: ../login.php"); // Redirect back to the login page
        exit;
    }

    // Fetch profile from the database
    $stmt = $conn->prepare("SELECT profil_id, userName, password, type FROM profil WHERE email = :email AND status != 'Inactive'");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR); // Binding parameters securely
    $stmt->execute();
    $profil = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($profil && password_verify($password, $profil['password'])) {
        // Login successful
        session_regenerate_id(true); // Regenerate session ID for security
        $_SESSION['profil_id'] = $profil['profil_id'];
        $_SESSION['profil_name'] = $profil['userName'];
        $_SESSION['type'] = $profil['type'];

        // Redirect based on user type
        $redirectUrl = '';
        switch ($profil['type']) {
            case 'admin':
                $redirectUrl = '../admin/admin.php';
                break;
            case 'secretary':
                $redirectUrl = '../secretary/secretary.php';
                break;
            case 'owner':
                $redirectUrl = '../owner/owner.php';
                break;
            case 'mechanic':
                $redirectUrl = '../mechanic/mechanic.php';
                break;
            case 'tenant':
                $redirectUrl = '../tenant/tenant.php';
                break;
            default:
                $_SESSION['error'] = "Invalid user type.";
                header("Location: ../login.php"); // Redirect back to the login page
                exit;
        }

        // Check if there's a stored redirect URL
        if (!empty($_SESSION['redirect_url'])) { // Added an explicit check for the redirect URL
            $redirectUrl = $_SESSION['redirect_url'];
            unset($_SESSION['redirect_url']); // Clear the redirect URL
        }

        // Ensure the redirect URL starts with '../' only if necessary
        if (!str_starts_with($redirectUrl, '../')) {
            $redirectUrl = '../' . $redirectUrl;
        }

        // Redirect the user
        header("Location: $redirectUrl");
        exit;
    } else {
        // Login failed
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../login.php"); // Redirect back to the login page
        exit;
    }
}
?>
