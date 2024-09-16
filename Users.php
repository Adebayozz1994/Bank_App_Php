<?php
require ("config.php");
class User extends config{
    public function __construct()
    {
        parent::__construct();
    }
    public function createUser( $first_name, $last_name , $email, $password, $address,$phone_number,$gender,$role){
    $query = "INSERT INTO  `bank_table` (`first_name`, `last_name`, `email`, `password`, `address`,`phone_number`,`gender`,`role`) VALUES (?,?,?,?,?,?,?,?)";
    $hashpassword = password_hash($password, PASSWORD_DEFAULT);
    $binder = array('ssssssss', $first_name, $last_name, $email, $hashpassword, $address,$phone_number,$gender,$role);
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