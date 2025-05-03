<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['profil_id'])) {
    header("Location: ../login.php"); // Redirect to login page
    exit;
}

require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Initialize an array to hold the fields to update in the `profil` table
    $updateFields = [];
    $params = [':id' => $_SESSION['profil_id']];

    // Check each field and add it to the update array if it's not empty
    if (!empty($_POST['firstName'])) {
        $updateFields[] = 'firstName = :firstName';
        $params[':firstName'] = htmlspecialchars($_POST['firstName']);
    }
    if (!empty($_POST['lastName'])) {
        $updateFields[] = 'lastName = :lastName';
        $params[':lastName'] = htmlspecialchars($_POST['lastName']);
    }
    if (!empty($_POST['phone'])) {
        $updateFields[] = 'phone = :phone';
        $params[':phone'] = htmlspecialchars($_POST['phone']);
    }
    if (!empty($_POST['email'])) {
        $updateFields[] = 'email = :email';
        $params[':email'] = htmlspecialchars($_POST['email']);
    }
    if (!empty($_POST['userName'])) {
        $updateFields[] = 'userName = :userName';
        $params[':userName'] = htmlspecialchars($_POST['userName']);
    }
    if (!empty($_POST['password'])) {
        $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $updateFields[] = 'password = :password';
        $params[':password'] = $hashedPassword;
    }

    // Only proceed if there are fields to update in the `profil` table
    if (!empty($updateFields)) {
        // Build the SQL query dynamically
        $sql = "UPDATE profil SET " . implode(', ', $updateFields) . " WHERE profil_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Update session variables for the fields that were updated
        if (!empty($_POST['firstName'])) {
            $_SESSION['profil_firstName'] = $params[':firstName'];
        }
        if (!empty($_POST['lastName'])) {
            $_SESSION['profil_lastName'] = $params[':lastName'];
        }
        if (!empty($_POST['phone'])) {
            $_SESSION['profil_phone'] = $params[':phone'];
        }
        if (!empty($_POST['email'])) {
            $_SESSION['profil_email'] = $params[':email'];
        }
        if (!empty($_POST['userName'])) {
            $_SESSION['profil_userName'] = $params[':userName'];
        }
    }

    // Handle the `region` update based on the user's role
    if (!empty($_POST['region'])) {
        // Fetch the user's role from the session or database
        $role = $_SESSION['type']; // Assuming the role is stored in the session

        // Determine the table to update based on the role
        switch ($role) {
            case 'owner':
                $table = 'owner';
                break;
            case 'secretary':
                $table = 'secretary';
                break;
            case 'mechanic':
                $table = 'mechanic';
                break;
            case 'admin':
                $table = 'admin';
                break;
            default:
                // Invalid role, do not update
                $table = null;
                break;
        }

        if ($table) {
            // Update the `region` field in the appropriate table
            $sql = "UPDATE $table SET region = :region WHERE profil_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':region' => htmlspecialchars($_POST['region']),
                ':id' => $_SESSION['profil_id']
            ]);
        }
    }

    // Redirect with success message
    header("Location: edit_profile.php?success=1");
    exit;
} else {
    // No fields were provided to update
    header("Location: edit_profile.php?error=no_fields");
    exit;
}
?>