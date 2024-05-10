<?php 
require('users.php');

    header("Access-Control-Allow-Origin: http://localhost:4200");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");


    $userDetails = json_decode(file_get_contents("php://input"),true);
    
    $first_name = $userDetails['first_name'];
    $last_name = $userDetails['last_name'];
    $email = $userDetails['email'];
    $password = $userDetails['password'];
    // $password = password_hash($userDetails['password'],PASSWORD_DEFAULT);
    $address =$userDetails['address'];
    $phone_number =$userDetails['phone_number'];
    $gender =$userDetails['gender'];


    //  echo json_encode($userDetails);


    $User = new User();
    $response=$User->createUser($first_name, $last_name, $email, $password, $address,$phone_number,$gender);
    echo json_encode($response);
?>