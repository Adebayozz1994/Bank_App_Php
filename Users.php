<?php
require ("config.php");
class User extends config{
    public function __construct()
    {
        parent::__construct();
    }
    public function createUser( $first_name, $last_name , $email, $password, $address,$phone_number,$gender){
    $query = "INSERT INTO  `bank_table` (`first_name`, `last_name`, `email`, `password`, `address`,`phone_number`,`gender`) VALUES (?,?,?,?,?,?,?)";
    $hashpassword = password_hash($password, PASSWORD_DEFAULT);
    $binder = array('sssssss', $first_name, $last_name, $email, $hashpassword, $address,$phone_number,$gender);
    // parent::create($query, $binder);

    $emailQuery = "SELECT * FROM `bank_table` WHERE `email` = ?";
    $emailBinder = array('s', $email);
    $emailResult = $this->checkIfExist($emailQuery, $emailBinder);
    if($emailResult) {
        return [
            'status' => false,
            'message' => 'email already exist'
        ];

    }else {
        $result = $this->create($query, $binder);
        if($result){
            
          return  [
                'status' => true,
                'message' => 'user save successfully'
            ];
        }else{
           
          return  [
                'status' => false,
                'message' => 'error occured'
            ];
        }

    }


    }
    
}

$newUser =  new User();
?>