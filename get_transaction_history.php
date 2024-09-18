<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Check if account_id is provided
if (!isset($_GET['account_id']) || !is_numeric($_GET['account_id'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid or missing account_id']);
    exit;
}

$account_id = (int)$_GET['account_id']; // Sanitize input
error_log("Received account_id: " . $account_id);

class TransactionHistory extends config {
    public function getTransactionHistory($account_id) {
        try {
            if (!$this->connect) {
                throw new Exception("Database connection not established");
            }

            // Join with the accounts and bank_table to get sender and receiver details
            $query = "SELECT t.id, t.account_id, t.amount, t.transaction_type, t.transaction_date,
                             a.account_number, CONCAT(u.first_name, ' ', u.last_name) AS account_name
                      FROM transactions t
                      JOIN accounts a ON t.account_id = a.id
                      JOIN bank_table u ON a.user_id = u.user_id
                      WHERE t.account_id = ?";
            $stmt = $this->connect->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $this->connect->error);
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
            error_log("Error in getTransactionHistory: " . $e->getMessage());
            return ['status' => false, 'message' => 'Failed to fetch transaction history', 'error' => $e->getMessage()];
        }
    }
}

try {
    $transactionHistory = new TransactionHistory();
    $response = $transactionHistory->getTransactionHistory($account_id);
    echo json_encode($response);
} catch (Exception $e) {
    error_log("Error initializing TransactionHistory: " . $e->getMessage());
    echo json_encode(['status' => false, 'message' => 'Internal server error']);
}
?>
