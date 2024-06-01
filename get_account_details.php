<?php
require('config.php');

// Set CORS headers
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight request
    exit;
}

if (!isset($_GET['accountNumber'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$accountNumber = $_GET['accountNumber'];

class AccountHolder extends config {
    public function getAccountHolderName($accountNumber) {
        $query = "SELECT name FROM accounts WHERE account_number = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['name'];
        } else {
            return null;
        }
    }
}

$accountHolder = new AccountHolder();
$accountName = $accountHolder->getAccountHolderName($accountNumber);
if ($accountName !== null) {
    echo json_encode(['accountName' => $accountName]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Account not found']);
}
?>
