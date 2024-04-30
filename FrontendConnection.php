<?php 
require('Users.php');

    header("Access-Control-Allow-Origin: http://localhost:4200");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");


    $userDetails = json_decode(file_get_contents("php://input"),true);
    
    $firstname = $userDetails['name'];
    $lastname = $userDetails['last_name'];
    $email = $userDetails['email'];
    $password = password_hash($userDetails['password'],PASSWORD_DEFAULT);
    $address =$userDetails['address'];
    //  echo json_encode($firstname);


    $User = new User();
    $response=$User->createUser($firstname, $lastname, $email, $password, $address);
    echo json_encode($response);
?>