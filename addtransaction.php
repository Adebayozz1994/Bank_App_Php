// add_transaction.php
require_once("config.php");

class Transaction extends config {
    public function addTransaction($accountId, $amount, $transactionType) {
        $query = "INSERT INTO `transactions` (`account_id`, `amount`, `transaction_type`, `timestamp`) VALUES (?, ?, ?, NOW())";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('ids', $accountId, $amount, $transactionType);
        if ($stmt->execute()) {
            return [
                'status' => true,
                'message' => 'Transaction recorded successfully'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to record transaction'
            ];
        }
    }
}

// Create instance and call the method
$transactionDetails = json_decode(file_get_contents("php://input"), true);
$accountId = $transactionDetails['account_id'];
$amount = $transactionDetails['amount'];
$transactionType = $transactionDetails['transaction_type'];
$Transaction = new Transaction();
$response = $Transaction->addTransaction($accountId, $amount, $transactionType);
echo json_encode($response);
