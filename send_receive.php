<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!isset($request['senderId']) || !isset($request['receiverId']) || !isset($request['amount'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$senderId = $request['senderId'];
$receiverId = $request['receiverId'];
$amount = $request['amount'];

class Transaction extends config {
    public function sendMoney($senderId, $receiverId, $amount) {
        $this->connect->begin_transaction();

        try {
            $updateSender = "UPDATE bank_table SET balance = balance - ? WHERE user_id = ?";
            $updateReceiver = "UPDATE bank_table SET balance = balance + ? WHERE user_id = ?";

            $stmt1 = $this->connect->prepare($updateSender);
            $stmt1->bind_param('di', $amount, $senderId);
            $stmt1->execute();

            $stmt2 = $this->connect->prepare($updateReceiver);
            $stmt2->bind_param('di', $amount, $receiverId);
            $stmt2->execute();

            $transactionQuery = "INSERT INTO transactions (sender_id, receiver_id, amount) VALUES (?, ?, ?)";
            $stmt3 = $this->connect->prepare($transactionQuery);
            $stmt3->bind_param('iid', $senderId, $receiverId, $amount);
            $stmt3->execute();

            $this->connect->commit();
            return ['status' => true, 'message' => 'Transaction successful'];
        } catch (Exception $e) {
            $this->connect->rollback();
            return ['status' => false, 'message' => 'Transaction failed'];
        }
    }
}

$transaction = new Transaction();
$response = $transaction->sendMoney($senderId, $receiverId, $amount);
echo json_encode($response);
?>
