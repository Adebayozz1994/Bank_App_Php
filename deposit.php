<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class BankAccount extends config {
    // Verify user password and deposit money into their account
    public function deposit($accountNumber, $amount, $password) {
        if ($amount <= 0) {
            return [
                'status' => false,
                'message' => 'Invalid deposit amount.'
            ];
        }

        // Check if the account exists
        $query = "SELECT a.*, u.password FROM `accounts` a 
                  JOIN `bank_table` u ON a.user_id = u.user_id 
                  WHERE a.account_number = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $account = $result->fetch_assoc();

            // Verify the password
            if (!password_verify($password, $account['password'])) {
                return [
                    'status' => false,
                    'message' => 'Invalid password.'
                ];
            }

            $newBalance = $account['balance'] + $amount;

            // Update the account balance
            $updateQuery = "UPDATE `accounts` SET `balance` = ? WHERE `account_number` = ?";
            $updateStmt = $this->connect->prepare($updateQuery);
            $updateStmt->bind_param('ds', $newBalance, $accountNumber);

            if ($updateStmt->execute()) {
                return [
                    'status' => true,
                    'message' => 'Deposit successful.'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Failed to update balance.'
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'Account not found.'
            ];
        }
    }
}

$userDetails = json_decode(file_get_contents("php://input"), true);
$accountNumber = $userDetails['accountNumber'];
$amount = $userDetails['amount'];
$password = $userDetails['password'];

$BankAccount = new BankAccount();
$response = $BankAccount->deposit($accountNumber, $amount, $password);
echo json_encode($response);
?>
