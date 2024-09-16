<?php
require('users.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

try {
    $userDetails = json_decode(file_get_contents("php://input"), true);
    
    $first_name = $userDetails['first_name'];
    $last_name = $userDetails['last_name'];
    $email = $userDetails['email'];
    $password = $userDetails['password'];
    $address = $userDetails['address'];
    $phone_number = $userDetails['phone_number'];
    $gender = $userDetails['gender'];
    $role = $userDetails['role'];

    $User = new User();
    $response = $User->createUser($first_name, $last_name, $email, $password, $address, $phone_number, $gender, $role);
    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
