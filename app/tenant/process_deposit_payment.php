<?php
session_start();
require '../config/db.php';

// Check if the user is logged in and is a tenant
if (!isset($_SESSION['profil_id']) || $_SESSION['type'] !== 'tenant') {
    $_SESSION['error'] = "You must be logged in as a tenant to access this page.";
    header("Location: ../login.php");
    exit;
}

// Get the contract ID from the form submission
$contract_id = isset($_POST['contract_id']) ? intval($_POST['contract_id']) : 0;

if (!$contract_id) {
    $_SESSION['error'] = "Invalid contract ID.";
    header("Location: tenant.php");
    exit;
}

try {
    // Start a transaction
    $conn->beginTransaction();

    // Fetch the contract details
    $stmt = $conn->prepare("
        SELECT 
            c.contract_id,
            c.deposit,
            c.tenant_id
        FROM 
            contracts c
        WHERE 
            c.contract_id = :contract_id
    ");
    $stmt->execute([':contract_id' => $contract_id]);
    $contract = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        throw new Exception("Contract not found.");
    }

    // Record the deposit payment in the payment table
    $stmt = $conn->prepare("
        INSERT INTO payment (contract_id, tenant_id, creationDate, amount, type) 
        VALUES (:contract_id, :tenant_id, NOW(), :amount, 'Deposit')
    ");
    $stmt->execute([
        ':contract_id' => $contract_id,
        ':tenant_id' => $contract['tenant_id'],
        ':amount' => $contract['deposit']
    ]);

    // Commit the transaction
    $conn->commit();

    $_SESSION['success'] = "Deposit payment confirmed successfully. Contract status updated to 'Pending'.";
    header("Location: tenant.php");
    exit;
} catch (PDOException $e) {
    // Roll back the transaction on error
    $conn->rollBack();
    $_SESSION['error'] = "Failed to process deposit payment: " . $e->getMessage();
    header("Location: deposit_payment.php?contract_id=" . $contract_id);
    exit;
} catch (Exception $e) {
    // Roll back the transaction on error
    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header("Location: deposit_payment.php?contract_id=" . $contract_id);
    exit;
}
?>