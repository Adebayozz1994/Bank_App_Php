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
            // Check if sender account exists and fetch balance
            $checkSenderQuery = "SELECT balance FROM accounts WHERE account_number = ?";
            $stmt1 = $this->connect->prepare($checkSenderQuery);
            $stmt1->bind_param('s', $senderAccountNumber);
            $stmt1->execute();
            $senderResult = $stmt1->get_result();

            if ($senderResult->num_rows === 0) {
                return ['status' => false, 'message' => 'Sender account not found'];
            }

            $senderData = $senderResult->fetch_assoc();
            $senderBalance = $senderData['balance'];

            // Check if receiver account exists
            $checkReceiverQuery = "SELECT * FROM accounts WHERE account_number = ?";
            $stmt2 = $this->connect->prepare($checkReceiverQuery);
            $stmt2->bind_param('s', $receiverAccountNumber);
            $stmt2->execute();
            $receiverResult = $stmt2->get_result();

            if ($receiverResult->num_rows === 0) {
                return ['status' => false, 'message' => 'Receiver account not found'];
            }

            // Ensure sender has enough balance
            if ($senderBalance < $amount) {
                return ['status' => false, 'message' => 'Insufficient funds'];
            }

            // Proceed with the transaction
            $updateSender = "UPDATE accounts SET balance = balance - ? WHERE account_number = ?";
            $updateReceiver = "UPDATE accounts SET balance = balance + ? WHERE account_number = ?";

            $stmt3 = $this->connect->prepare($updateSender);
            $stmt3->bind_param('ds', $amount, $senderAccountNumber);
            $stmt3->execute();

            $stmt4 = $this->connect->prepare($updateReceiver);
            $stmt4->bind_param('ds', $amount, $receiverAccountNumber);
            $stmt4->execute();

            // Insert transaction record for sender (debit)
            $transactionQuerySender = "INSERT INTO transactions (account_id, amount, transaction_type) VALUES ((SELECT id FROM accounts WHERE account_number = ?), ?, 'debit')";
            $stmt5 = $this->connect->prepare($transactionQuerySender);
            $stmt5->bind_param('sd', $senderAccountNumber, $amount);
            $stmt5->execute();

            // Insert transaction record for receiver (credit)
            $transactionQueryReceiver = "INSERT INTO transactions (account_id, amount, transaction_type) VALUES ((SELECT id FROM accounts WHERE account_number = ?), ?, 'credit')";
            $stmt6 = $this->connect->prepare($transactionQueryReceiver);
            $stmt6->bind_param('sd', $receiverAccountNumber, $amount);
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
?>
