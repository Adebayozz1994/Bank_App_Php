<?php
require_once("createaccount.php");

$userDetails = json_decode(file_get_contents("php://input"), true);
$userId = $userDetails['user_id'];

$BankAccount = new BankAccount();
$response = $BankAccount->createAccount($userId);
echo json_encode($response);
?>
