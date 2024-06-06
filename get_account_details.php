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

class AccountBalance extends config {
    public function getBalance($account_id) {
        $query = "SELECT balance FROM accounts WHERE id = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('i', $account_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'Account not found'];
        }
        
        $account = $result->fetch_assoc();
        return ['status' => true, 'balance' => $account['balance']];
    }
}

$balanceChecker = new AccountBalance();
$response = $balanceChecker->getBalance($account_id);
echo json_encode($response);
?>
