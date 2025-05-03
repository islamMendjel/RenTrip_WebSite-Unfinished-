<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    $_SESSION['error'] = "You must be logged in as a tenant to access this page.";
    header("Location: ../login.php");
    exit;
}

// Get the bill ID from the form submission
$bill_id = isset($_POST['bill_id']) ? intval($_POST['bill_id']) : 0;

if (!$bill_id) {
    $_SESSION['error'] = "Invalid bill ID.";
    header("Location: tenant.php");
    exit;
}

try {
    // Start a transaction
    $conn->beginTransaction();

    // Fetch the bill details
    $stmt = $conn->prepare("
        SELECT b.totalAmount, r.tenant_id 
        FROM bill b
        JOIN reservation r ON b.bill_id = r.bill_id
        WHERE b.bill_id = :bill_id AND r.tenant_id = :tenant_id
    ");
    $stmt->execute([
        ':bill_id' => $bill_id,
        ':tenant_id' => $_SESSION['profil_id']
    ]);
    $bill = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bill) {
        throw new Exception("Bill not found or you do not have permission to access it.");
    }

    // Update the bill status to 'Paid'
    $stmt = $conn->prepare("
        UPDATE bill 
        SET status = 'Paid' 
        WHERE bill_id = :bill_id
    ");
    $stmt->execute([':bill_id' => $bill_id]);

    // Record the payment in the payment table
    $stmt = $conn->prepare("
        INSERT INTO payment (bill_id, tenant_id, creationDate, amount, type) 
        VALUES (:bill_id, :tenant_id, NOW(), :amount, 'Credit Card')
    ");
    $stmt->execute([
        ':bill_id' => $bill_id,
        ':tenant_id' => $_SESSION['profil_id'],
        ':amount' => $bill['totalAmount']
    ]);

    // Commit the transaction
    $conn->commit();

    $_SESSION['success'] = "Payment confirmed successfully.";
    header("Location: tenant.php");
    exit;
} catch (PDOException $e) {
    // Roll back the transaction on error
    $conn->rollBack();
    $_SESSION['error'] = "Failed to process payment: " . $e->getMessage();
    header("Location: payment.php?bill_id=" . $bill_id);
    exit;
} catch (Exception $e) {
    // Roll back the transaction on error
    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header("Location: payment.php?bill_id=" . $bill_id);
    exit;
}
?>