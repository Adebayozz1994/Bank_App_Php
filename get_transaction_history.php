<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if (!isset($_GET['account_id']) || !is_numeric($_GET['account_id'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid or missing account_id']);
    exit;
}

$account_id = (int) $_GET['account_id'];

class TransactionHistory extends config {
    public function getTransactionHistory($account_id) {
        try {
            if (!$this->connect) {
                throw new Exception("Database connection not established");
            }

            $query = "
                SELECT 
                    t.id,
                    t.amount,
                    t.transaction_type,
                    t.transaction_date,
                    sa.account_number AS sender_account_number,
                    CONCAT(sb.first_name, ' ', sb.last_name) AS sender_name,
                    ra.account_number AS receiver_account_number,
                    CONCAT(rb.first_name, ' ', rb.last_name) AS receiver_name
                FROM transactions t
                JOIN accounts sa ON t.sender_account_id = sa.id
                JOIN bank_table sb ON sa.user_id = sb.user_id
                JOIN accounts ra ON t.receiver_account_id = ra.id
                JOIN bank_table rb ON ra.user_id = rb.user_id
                WHERE 
                    (t.sender_account_id = ? AND t.transaction_type = 'debit')
                    OR 
                    (t.receiver_account_id = ? AND t.transaction_type = 'credit')
                ORDER BY t.transaction_date DESC
            ";

            $stmt = $this->connect->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $this->connect->error);
            }

            // Bind twice: once for sender, once for receiver
            $stmt->bind_param('ii', $account_id, $account_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                // Add a direction for frontend convenience
                $row['direction'] = ($row['transaction_type'] === 'debit') ? 'sent' : 'received';
                $transactions[] = $row;
            }

            return ['status' => true, 'transactions' => $transactions];
        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}

$transactionHistory = new TransactionHistory();
$response = $transactionHistory->getTransactionHistory($account_id);
echo json_encode($response);
