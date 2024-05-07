<?php
   header("Access-Control-Allow-Origin: http://localhost:4200");
   header("Access-Control-Allow-Headers: Content-Type");
   header("Content-Type: application/json");


   $input = json_decode(file_get_contents('php://input'), true);

   $email = $input['username'];
   $password = password_hash($password, PASSWORD_DEFAULT);

   $conn = new mysqli("localhost", "root", "", "login");
   $sql = "SELECT * FROM bank_table WHERE email = '$email' AND password = '$password'";
   $result = $conn->query($sql);
   $row = $result->fetch_assoc();

   if ($result->num_rows > 0) {
       $response = array(
           'status' => 'success',
           'message' => 'Login successful',
           'data' => $row
       );
   } else {
       $response = array(
           'status' => 'error',
           'message' => 'Invalid username or password'
       );
   }

   echo json_encode($response);
?>