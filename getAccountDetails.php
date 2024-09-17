<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$request = json_decode(file_get_contents("php://input"), true);

if (isset($request['action'])) {
    if ($request['action'] === 'getAccountDetails' && isset($request['accountNumber'])) {
        $accountNumber = $request['accountNumber'];

        $query = "SELECT account_name FROM accounts WHERE account_number = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $account = $result->fetch_assoc();
            echo json_encode(['status' => true, 'accountName' => $account['account_name']]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Account not found']);
        }
    } elseif ($request['action'] === 'sendMoney' && isset($request['senderAccountNumber']) && isset($request['receiverAccountNumber']) && isset($request['amount']) && isset($request['password'])) {
        // Your existing sendMoney logic
    } else {
        echo json_encode(['status' => false, 'message' => 'Invalid request']);
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Action not specified']);
}
?>
