<?php
require '../config/db.php'; // Ensure the database connection is available

// Check if the action and ID are set
if (isset($_GET['action']) && (isset($_GET['tenant_id']) || isset($_GET['reservation_id']))) {
    $action = $_GET['action'];

    // Get tenant_id or reservation_id from the GET parameters
    $tenant_id = isset($_GET['tenant_id']) ? intval($_GET['tenant_id']) : null;
    $reservation_id = isset($_GET['reservation_id']) ? intval($_GET['reservation_id']) : null;

    // Make sure the tenant_id or reservation_id is set before proceeding
    if (!$tenant_id && !$reservation_id) {
        $message = "Invalid tenant or reservation ID.";
        header("Location: ../secretary/manage_tenants.php?error=" . urlencode($message));
        exit;
    }

    try {
        switch ($action) {
            case 'exclude':
                // Query to mark the tenant as excluded
                $query = "UPDATE tenants SET excluded = 1 WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $tenant_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $message = "Tenant excluded successfully";
                } else {
                    $message = "Failed to exclude tenant";
                }
                break;

            case 'fetch':
                // Query to fetch reservations for a tenant that don't have a contract
                if (!$tenant_id) {
                    $message = "Tenant ID is required.";
                    echo json_encode(['error' => $message]);
                    exit;
                }
                $query = "SELECT * FROM reservations WHERE tenant_id = :tenant_id AND contract_id IS NULL";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
                $stmt->execute();
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($reservations);
                exit;

            case 'contract':
                // Query to create a contract for a reservation
                if (!$reservation_id) {
                    $message = "Reservation ID is required.";
                    echo json_encode(['error' => $message]);
                    exit;
                }
                $query = "INSERT INTO contracts (reservation_id, created_at) VALUES (:reservation_id, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $message = "Contract established successfully";
                } else {
                    $message = "Failed to establish contract";
                }
                break;

            case 'fetch_invoice_reservations':
                // Query to fetch reservations that don't have an invoice
                if (!$tenant_id) {
                    $message = "Tenant ID is required.";
                    echo json_encode(['error' => $message]);
                    exit;
                }
                $query = "SELECT * FROM reservations WHERE tenant_id = :tenant_id AND invoice_id IS NULL";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
                $stmt->execute();
                $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($reservations);
                exit;

            case 'invoice':
                // Query to create an invoice for a reservation
                if (!$reservation_id) {
                    $message = "Reservation ID is required.";
                    echo json_encode(['error' => $message]);
                    exit;
                }
                $query = "INSERT INTO invoices (reservation_id, created_at) VALUES (:reservation_id, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $message = "Invoice created successfully";
                } else {
                    $message = "Failed to create invoice";
                }
                break;

            case 'revoke_exclude':
                // Revoke exclusion by setting `excluded` to 0
                if (!$tenant_id) {
                    $message = "Tenant ID is required.";
                    header("Location: ../secretary/manage_tenants.php?error=" . urlencode($message));
                    exit;
                }
                $query = "UPDATE tenants SET excluded = 0 WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $tenant_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Tenant exclusion revoked successfully!";
                break;

            case 'accept':
                // Accept tenant by setting `validated` to 1
                if (!$tenant_id) {
                    $message = "Tenant ID is required.";
                    header("Location: ../secretary/manage_tenants.php?error=" . urlencode($message));
                    exit;
                }
                $query = "UPDATE tenant SET isValidated = 1 WHERE tenants_id = :tenants_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':tenants_id', $tenant_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Tenant accepted successfully!";
                break;

            case 'refuse':
                // Refuse tenant by deleting their record
                if (!$tenant_id) {
                    $message = "Tenant ID is required.";
                    header("Location: ../secretary/manage_tenants.php?error=" . urlencode($message));
                    exit;
                }
                $query = "DELETE FROM tenant WHERE id = :id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $tenant_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Tenant refused and removed successfully!";
                break;

            default:
                // Invalid action
                $message = "Invalid action!";
                break;
        }

        // Redirect back with the appropriate message
        header("Location: ../secretary/manage_tenants.php?success=" . urlencode($message));
        exit;

    } catch (PDOException $e) {
        // Handle database errors
        $error_message = "Database error: " . $e->getMessage();
        header("Location: ../secretary/manage_tenants.php?error=" . urlencode($error_message));
        exit;
    }
} else {
    // If the required action or tenant_id/reservation_id is not set, redirect
    header("Location: ../secretary/manage_tenants.php");
    exit;
}