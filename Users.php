<?php
require ("config.php");
class User extends config{
    public function __construct()
    {
        parent::__construct();
    }
    public function createUser( $firstname, $lastname , $email, $password, $address){
    $query = "INSERT INTO  `user_table` (`firstname`, `lastname`, `email`, `password`, `address`) VALUES (?,?,?,?,?)";
    $hashpassword = password_hash($password, PASSWORD_DEFAULT);
    $binder = array('sssss', $firstname, $lastname, $email, $hashpassword, $address);
    // parent::create($query, $binder);

    $emailQuery = "SELECT * FROM `user_table` WHERE `email` = ?";
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
            // echo 'user save successfully';
          return  [
                'status' => true,
                'message' => 'user save successfully'
            ];
        }else{
            // echo 'user not save';
          return  [
                'status' => false,
                'message' => 'error occured'
            ];
        }

    }



    //  $this->connect->prepare($query);
    //  $hashpassword = password_hash($password, PASSWORD_DEFAULT);
    //  $stmt = $this->connect->prepare($query);
    //  $stmt->bind_param("sssss",$firstname, $lastname, $email, $hashpassword,$address);
    //  $safeUser  = $stmt->execute();
    //  if($safeUser){
    //     echo "user saved successfully";
    //  }else{
    //     echo "user not saved";
    //  }

    }

}

$newUser =  new User();
?>