<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!isset($request['accountId'], $request['phoneNumber'], $request['dataPlan'], $request['amount'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$accountId = $request['accountId'];
$phoneNumber = $request['phoneNumber'];
$dataPlan = $request['dataPlan'];
$amount = $request['amount'];

class DataTransaction extends config {
    public function buyData($accountId, $phoneNumber, $dataPlan, $amount) {
        if ($amount <= 0) return ['status' => false, 'message' => 'Invalid amount'];
        if (!preg_match('/^[0-9]{10,15}$/', $phoneNumber)) return ['status' => false, 'message' => 'Invalid phone number format'];
        if (empty($dataPlan)) return ['status' => false, 'message' => 'Data plan is required'];

        $this->connect->begin_transaction();

        try {
            $stmt = $this->connect->prepare("SELECT balance FROM accounts WHERE id = ?");
            $stmt->bind_param('i', $accountId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) throw new Exception("Account not found");
            $account = $result->fetch_assoc();

            if ($account['balance'] < $amount) throw new Exception("Insufficient balance. Current balance: ₦" . number_format($account['balance'], 2));

            $stmt = $this->connect->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param('di', $amount, $accountId);
            if (!$stmt->execute()) throw new Exception("Failed to update balance");

            $stmt = $this->connect->prepare("INSERT INTO transactions (account_id, amount, transaction_type, transaction_date) VALUES (?, ?, 'debit', NOW())");
            $stmt->bind_param('id', $accountId, $amount);
            if (!$stmt->execute()) throw new Exception("Failed to record transaction");

            $stmt = $this->connect->prepare("INSERT INTO data_transactions (account_id, phone_number, data_plan, amount, transaction_date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param('issd', $accountId, $phoneNumber, $dataPlan, $amount);
            if (!$stmt->execute()) throw new Exception("Failed to record data transaction");

            $this->connect->commit();

            return ['status' => true, 'message' => 'Data purchase successful'];
        } catch (Exception $e) {
            $this->connect->rollback();
            return ['status' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
        }
    }
}

$dt = new DataTransaction();
$response = $dt->buyData($accountId, $phoneNumber, $dataPlan, $amount);
echo json_encode($response);
