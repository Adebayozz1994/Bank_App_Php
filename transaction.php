<?php
require_once("addtransaction.php");

$transactionDetails = json_decode(file_get_contents("php://input"), true);
$accountId = $transactionDetails['account_id'];
$amount = $transactionDetails['amount'];
$transactionType = $transactionDetails['transaction_type'];

$Transaction = new Transaction();
$response = $Transaction->addTransaction($accountId, $amount, $transactionType);
echo json_encode($response);
?>
