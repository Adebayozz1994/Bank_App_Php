<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class BankAccount extends config {
    public function createOrGetAccount($userId) {
        // Check if user already has an account
        $query = "SELECT * FROM `accounts` WHERE `user_id` = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Account already exists
            $account = $result->fetch_assoc();
            return [
                'status' => true,
                'account' => $account
            ];
        } else {
            // Create a new account
            return $this->createAccount($userId);
        }
    }

    public function createAccount($userId) {
        $accountNumber = $this->generateAccountNumber();
        $initialBalance = 0; // Set initial balance to 1000000
        $query = "INSERT INTO `accounts` (`user_id`, `account_number`, `balance`) VALUES (?, ?, ?)";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('isi', $userId, $accountNumber, $initialBalance);
        if ($stmt->execute()) {
            return [
                'status' => true,
                'message' => 'Account created successfully',
                'account' => [
                    'account_number' => $accountNumber,
                    'balance' => $initialBalance,
                    'user_id' => $userId
                ]
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to create account'
            ];
        }
    }

    private function generateAccountNumber() {
        
        return mt_rand((int)1000000000, (int)9999999999);
    }
}

$userDetails = json_decode(file_get_contents("php://input"), true);
$userId = $userDetails['user_id'];

$BankAccount = new BankAccount();
$response = $BankAccount->createOrGetAccount($userId);
echo json_encode($response);
?>
