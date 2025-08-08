<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!isset($request['senderAccountNumber']) || !isset($request['receiverAccountNumber']) || !isset($request['amount'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$senderAccountNumber = $request['senderAccountNumber'];
$receiverAccountNumber = $request['receiverAccountNumber'];
$amount = $request['amount'];

class Transaction extends config {
    public function sendMoney($senderAccountNumber, $receiverAccountNumber, $amount) {
        $this->connect->begin_transaction();

        try {
            // Get sender
            $stmt1 = $this->connect->prepare("SELECT id, balance FROM accounts WHERE account_number = ?");
            $stmt1->bind_param('s', $senderAccountNumber);
            $stmt1->execute();
            $senderResult = $stmt1->get_result();

            if ($senderResult->num_rows === 0) {
                return ['status' => false, 'message' => 'Sender account not found'];
            }
            $senderData = $senderResult->fetch_assoc();
            $senderId = $senderData['id'];
            $senderBalance = $senderData['balance'];

            // Get receiver
            $stmt2 = $this->connect->prepare("SELECT id FROM accounts WHERE account_number = ?");
            $stmt2->bind_param('s', $receiverAccountNumber);
            $stmt2->execute();
            $receiverResult = $stmt2->get_result();

            if ($receiverResult->num_rows === 0) {
                return ['status' => false, 'message' => 'Receiver account not found'];
            }
            $receiverData = $receiverResult->fetch_assoc();
            $receiverId = $receiverData['id'];

            // Check funds
            if ($senderBalance < $amount) {
                return ['status' => false, 'message' => 'Insufficient funds'];
            }

            // Update balances
            $stmt3 = $this->connect->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $stmt3->bind_param('di', $amount, $senderId);
            $stmt3->execute();

            $stmt4 = $this->connect->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
            $stmt4->bind_param('di', $amount, $receiverId);
            $stmt4->execute();

            // Insert sender transaction (debit)
            $stmt5 = $this->connect->prepare("
                INSERT INTO transactions (account_id, sender_account_id, receiver_account_id, amount, transaction_type, transaction_date) 
                VALUES (?, ?, ?, ?, 'debit', NOW())
            ");
            $stmt5->bind_param('iiid', $senderId, $senderId, $receiverId, $amount);
            $stmt5->execute();

            // Insert receiver transaction (credit)
            $stmt6 = $this->connect->prepare("
                INSERT INTO transactions (account_id, sender_account_id, receiver_account_id, amount, transaction_type, transaction_date) 
                VALUES (?, ?, ?, ?, 'credit', NOW())
            ");
            $stmt6->bind_param('iiid', $receiverId, $senderId, $receiverId, $amount);
            $stmt6->execute();

            $this->connect->commit();
            return ['status' => true, 'message' => 'Transaction successful'];

        } catch (Exception $e) {
            $this->connect->rollback();
            return ['status' => false, 'message' => 'Transaction failed', 'error' => $e->getMessage()];
        }
    }
}

$transaction = new Transaction();
$response = $transaction->sendMoney($senderAccountNumber, $receiverAccountNumber, $amount);
echo json_encode($response);
