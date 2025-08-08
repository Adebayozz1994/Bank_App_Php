<?php
require_once("config.php");

class Transaction extends config {
    public function addTransaction($accountId, $amount, $transactionType, $receiverAccountId = null) {
        // Updated table to also store receiver account id
        $query = "INSERT INTO `transactions` (`account_id`, `receiver_account_id`, `amount`, `transaction_type`, `timestamp`) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('iids', $accountId, $receiverAccountId, $amount, $transactionType);

        if ($stmt->execute()) {
            return [
                'status' => true,
                'message' => 'Transaction recorded successfully'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Failed to record transaction',
                'error' => $stmt->error
            ];
        }
    }
}
?>
