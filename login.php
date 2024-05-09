<?php
require_once('loginconnect.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$userDetails = json_decode(file_get_contents("php://input"), true);

$email = $userDetails['email'];
$password = $userDetails['password'];

$User = new loginconnect();
$response = $User->loginUser($email, $password);

// Return the response as JSON
echo json_encode($response);
?>
