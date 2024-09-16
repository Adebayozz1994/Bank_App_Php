<?php
require_once("config.php");

class loginconnect extends config {
    function __construct() {
        parent::__construct();
    }

    public function loginUser($email, $password) {
        $query = "SELECT * FROM `bank_table` WHERE `email` = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashedPassword = $user['password'];
            $userId = $user['user_id'];
            $userRole = $user['role']; // Assuming you have a 'role' column

            if (password_verify($password, $hashedPassword)) {
                return [
                    'status' => true,
                    'message' => 'User login successful',
                    'user' => [
                        'userId' => $userId,
                        'email' => $email,
                        'role' => $userRole, 
                        'userId' => $user
                        
                    ]
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Incorrect password'
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }
    }
}
?>
