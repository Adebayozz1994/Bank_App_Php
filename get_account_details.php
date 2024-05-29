<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$userId = $_GET['user_id'];

class AccountDetails extends config {
    public function getAccountDetails($userId) {
        $query = "SELECT * FROM bank_table WHERE user_id = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

$accountDetails = new AccountDetails();
$response = $accountDetails->getAccountDetails($userId);
echo json_encode($response);
?>
