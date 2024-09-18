<?php
require_once("config.php");

// Allow requests from the Angular app
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Define the BankAccount class which extends the config
class BankAccount extends config {
    
    // Function to retrieve account holder's name by account number
    public function getAccountName($accountNumber) {
        // Query to fetch the account holder's first and last name based on account number
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
            // If account doesn't exist, return a failure response
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
        
        // Send the response as JSON
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Account number not provided.'
        ]);
    }
} else {
    // If the request method is not GET, return a method not allowed error
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>
