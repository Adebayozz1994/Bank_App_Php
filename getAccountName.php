<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class BankAccount extends config {
    
    public function getAccountName($accountNumber) {
        $query = "SELECT u.first_name, u.last_name FROM `accounts` a 
                  JOIN `bank_table` u ON a.user_id = u.user_id 
                  WHERE a.account_number = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        // If account exists, return the combined name
        if ($result->num_rows > 0) {
            $account = $result->fetch_assoc();
            $fullName = $account['first_name'] . ' ' . $account['last_name']; // Combine first and last name
            return [
                'status' => true,
                'accountName' => $fullName
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Account not found.'
            ];
        }
    }
}

// Check if the request method is GET to retrieve the account number from URL parameters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['accountNumber'])) {
        $accountNumber = $_GET['accountNumber'];
        
        // Create BankAccount object and fetch account holder's name
        $BankAccount = new BankAccount();
        $response = $BankAccount->getAccountName($accountNumber);
        
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Account number not provided.'
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>
