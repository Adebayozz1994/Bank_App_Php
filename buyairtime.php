<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (!isset($request['accountId']) || !isset($request['phoneNumber']) || !isset($request['amount'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid request']);
    exit;
}

$accountId = $request['accountId'];
$phoneNumber = $request['phoneNumber'];
$amount = $request['amount'];

class AirtimeTransaction extends config {
    public function buyAirtime($accountId, $phoneNumber, $amount) {
        // Validate inputs
        if ($amount <= 0) {
            return ['status' => false, 'message' => 'Invalid amount'];
        }
        
        if (!preg_match('/^[0-9]{10,15}$/', $phoneNumber)) {
            return ['status' => false, 'message' => 'Invalid phone number format'];
        }
        
        $this->connect->begin_transaction();

        try {
            // Check if account exists and has sufficient balance
            $checkAccountQuery = "SELECT balance FROM accounts WHERE id = ?";
            $stmt = $this->connect->prepare($checkAccountQuery);
            $stmt->bind_param('i', $accountId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $this->connect->rollback();
                return ['status' => false, 'message' => 'Account not found'];
            }

            $account = $result->fetch_assoc();
            $currentBalance = $account['balance'];
            
            if ($currentBalance < $amount) {
                $this->connect->rollback();
                return ['status' => false, 'message' => "Insufficient balance. Current balance: ₦" . number_format($currentBalance, 2)];
            }

            // Deduct balance from accounts table
            $updateBalanceQuery = "UPDATE accounts SET balance = balance - ? WHERE id = ?";
            $balanceStmt = $this->connect->prepare($updateBalanceQuery);
            $balanceStmt->bind_param('di', $amount, $accountId);
            
            if (!$balanceStmt->execute()) {
                $this->connect->rollback();
                return ['status' => false, 'message' => 'Failed to update account balance'];
            }

            // Insert into main transactions table for tracking
            $insertMainTransactionQuery = "INSERT INTO transactions (account_id, amount, transaction_type, description) VALUES (?, ?, 'debit', ?)";
            $description = "Airtime purchase for " . $phoneNumber;
            $mainTransStmt = $this->connect->prepare($insertMainTransactionQuery);
            $mainTransStmt->bind_param('ids', $accountId, $amount, $description);
            
            if (!$mainTransStmt->execute()) {
                $this->connect->rollback();
                return ['status' => false, 'message' => 'Failed to record main transaction'];
            }

            // Insert into airtime transactions table for detailed tracking
            $insertAirtimeQuery = "INSERT INTO airtime_transactions (account_id, phone_number, amount) VALUES (?, ?, ?)";
            $airtimeStmt = $this->connect->prepare($insertAirtimeQuery);
            $airtimeStmt->bind_param('isd', $accountId, $phoneNumber, $amount);
            
            if (!$airtimeStmt->execute()) {
                $this->connect->rollback();
                return ['status' => false, 'message' => 'Failed to record airtime transaction'];
            }

            $this->connect->commit();
            
            // Get new balance
            $newBalance = $currentBalance - $amount;
            
            return [
                'status' => true, 
                'message' => 'Airtime purchase successful',
                'transaction_details' => [
                    'phone_number' => $phoneNumber,
                    'amount' => $amount,
                    'previous_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'transaction_id' => $this->connect->insert_id
                ]
            ];
        } catch (Exception $e) {
            $this->connect->rollback();
            return ['status' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
        }
    }
}

$airtimeTransaction = new AirtimeTransaction();
$response = $airtimeTransaction->buyAirtime($accountId, $phoneNumber, $amount);
echo json_encode($response);
?>
