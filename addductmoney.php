<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class AddDeductUser extends config {

    public function processRequest() {
        global $pdo; // Assuming $pdo is defined in config.php

        $accountNumber = $_POST['account_number'];
        $amount = $_POST['amount'];
        $operation = $_POST['operation']; // 'add' or 'deduct'

        if (!$accountNumber || !$amount || !in_array($operation, ['add', 'deduct'])) {
            echo json_encode(['message' => 'Invalid input']);
            exit;
        }

        // Fetch the account
        $query = $pdo->prepare("SELECT * FROM accounts WHERE account_number = ?");
        $query->execute([$accountNumber]);
        $account = $query->fetch();

        if (!$account) {
            echo json_encode(['message' => 'Account not found']);
            exit;
        }

        if ($operation === 'deduct' && $account['balance'] < $amount) {
            echo json_encode(['message' => 'Insufficient balance']);
            exit;
        }

        // Update the account balance
        $newBalance = ($operation === 'add') ? $account['balance'] + $amount : $account['balance'] - $amount;
        $updateQuery = $pdo->prepare("UPDATE accounts SET balance = ? WHERE account_number = ?");
        $updateQuery->execute([$newBalance, $accountNumber]);

        echo json_encode(['message' => 'Operation successful', 'new_balance' => $newBalance]);
    }
}

$addDeductUser = new AddDeductUser();
$addDeductUser->processRequest();
