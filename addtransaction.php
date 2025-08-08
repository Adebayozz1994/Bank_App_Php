<?php
require_once("Transaction.php");

// Get JSON request body
$transactionDetails = json_decode(file_get_contents("php://input"), true);

$accountId = $transactionDetails['account_id'];
$amount = $transactionDetails['amount'];
$transactionType = $transactionDetails['transaction_type'];
$receiverAccountId = isset($transactionDetails['receiver_account_id']) ? $transactionDetails['receiver_account_id'] : null;

// Create transaction instance
$Transaction = new Transaction();
$response = $Transaction->addTransaction($accountId, $amount, $transactionType, $receiverAccountId);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
