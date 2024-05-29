<?php
require_once("createaccount.php");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
$userDetails = json_decode(file_get_contents("php://input"), true);
$userId = $userDetails['user_id'];

$BankAccount = new BankAccount();
$response = $BankAccount->createAccount($userId);
echo json_encode($response);
?>
