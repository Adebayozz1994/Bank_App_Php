// add_account.php
require_once("config.php");

class BankAccount extends config {
    public function createAccount($userId) {
        $accountNumber = $this->generateAccountNumber();
        $query = "INSERT INTO `accounts` (`user_id`, `account_number`, `balance`) VALUES (?, ?, 0)";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('is', $userId, $accountNumber);
        if ($stmt->execute()) {
            return [
                'status' => true,
                'message' => 'Account created successfully',
                'account_number' => $accountNumber
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to create account'
            ];
        }
    }

    private function generateAccountNumber() {
        return 'ACC' . rand(1000000000, 9999999999); // Simple example, adjust as needed
    }
}

// Create instance and call the method
$userDetails = json_decode(file_get_contents("php://input"), true);
$userId = $userDetails['user_id'];
$BankAccount = new BankAccount();
$response = $BankAccount->createAccount($userId);
echo json_encode($response);
