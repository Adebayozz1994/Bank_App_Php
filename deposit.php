<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class BankAccount extends config {
    public function deposit($accountNumber, $amount) {
        if ($amount <= 0) {
            return [
                'status' => false,
                'message' => 'Invalid deposit amount.'
            ];
        }

        // Check if the account exists
        $query = "SELECT * FROM `accounts` WHERE `account_number` = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $account = $result->fetch_assoc();
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

$BankAccount = new BankAccount();
$response = $BankAccount->deposit($accountNumber, $amount);
echo json_encode($response);
?>
