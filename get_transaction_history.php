<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if (!isset($_GET['account_id'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$account_id = $_GET['account_id'];

class TransactionHistory extends config {
    public function getTransactionHistory($account_id) {
        try {
            $query = "SELECT * FROM transactions WHERE account_id = ?";
            $stmt = $this->connect->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            $stmt->bind_param('i', $account_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }

            return ['status' => true, 'transactions' => $transactions];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Failed to fetch transaction history', 'error' => $e->getMessage()];
        }
    }
}

$transactionHistory = new TransactionHistory();
$response = $transactionHistory->getTransactionHistory($account_id);
echo json_encode($response);
?>
