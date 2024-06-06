<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!isset($request['accountId']) || !isset($request['phoneNumber']) || !isset($request['amount'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$accountId = $request['accountId'];
$phoneNumber = $request['phoneNumber'];
$amount = $request['amount'];

class AirtimeTransaction extends config {
    public function buyAirtime($accountId, $phoneNumber, $amount) {
        $this->connect->begin_transaction();

        try {
            // Check if account exists and has sufficient balance
            $checkAccountQuery = "SELECT balance FROM accounts WHERE id = ?";
            $stmt = $this->connect->prepare($checkAccountQuery);
            $stmt->bind_param('i', $accountId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['status' => false, 'message' => 'Account not found'];
            }

            $account = $result->fetch_assoc();
            if ($account['balance'] < $amount) {
                return ['status' => false, 'message' => 'Insufficient balance'];
            }

            // Deduct balance
            $updateBalanceQuery = "UPDATE accounts SET balance = balance - ? WHERE id = ?";
            $stmt = $this->connect->prepare($updateBalanceQuery);
            $stmt->bind_param('di', $amount, $accountId);
            $stmt->execute();

            // Insert airtime transaction
            $insertTransactionQuery = "INSERT INTO airtime_transactions (account_id, phone_number, amount) VALUES (?, ?, ?)";
            $stmt = $this->connect->prepare($insertTransactionQuery);
            $stmt->bind_param('isd', $accountId, $phoneNumber, $amount);
            $stmt->execute();

            $this->connect->commit();
            return ['status' => true, 'message' => 'Airtime purchase successful'];
        } catch (Exception $e) {
            $this->connect->rollback();
            return ['status' => false, 'message' => 'Transaction failed', 'error' => $e->getMessage()];
        }
    }
}

$airtimeTransaction = new AirtimeTransaction();
$response = $airtimeTransaction->buyAirtime($accountId, $phoneNumber, $amount);
echo json_encode($response);
?>
