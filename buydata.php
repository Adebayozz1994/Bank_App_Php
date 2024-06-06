<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!isset($request['accountId']) || !isset($request['phoneNumber']) || !isset($request['dataPlan']) || !isset($request['amount'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$accountId = $request['accountId'];
$phoneNumber = $request['phoneNumber'];
$dataPlan = $request['dataPlan'];
$amount = $request['amount'];

class DataTransaction extends config {
    public function buyData($accountId, $phoneNumber, $dataPlan, $amount) {
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

            // Insert data transaction
            $insertTransactionQuery = "INSERT INTO data_transactions (account_id, phone_number, data_plan, amount) VALUES (?, ?, ?, ?)";
            $stmt = $this->connect->prepare($insertTransactionQuery);
            $stmt->bind_param('isds', $accountId, $phoneNumber, $dataPlan, $amount);
            $stmt->execute();

            $this->connect->commit();
            return ['status' => true, 'message' => 'Data purchase successful'];
        } catch (Exception $e) {
            $this->connect->rollback();
            return ['status' => false, 'message' => 'Transaction failed', 'error' => $e->getMessage()];
        }
    }
}

$dataTransaction = new DataTransaction();
$response = $dataTransaction->buyData($accountId, $phoneNumber, $dataPlan, $amount);
echo json_encode($response);
?>

