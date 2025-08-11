<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: text/plain"); 

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

class GetAccountDetails extends config {
    public function __construct() {
        parent::__construct();
    }

    public function getBalance($accountId) {
        if (empty($accountId)) {
            
            echo 0;
            return;
        }
        $query = "SELECT balance FROM accounts WHERE id = ?";
        $stmt = $this->connect->prepare($query);
        if (!$stmt) {
            echo 0;
            return;
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $account = $result->fetch_assoc();
            echo $account['balance'];
        } else {
            echo 0;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['account_id'])) {
    $accountId = intval($_GET['account_id']);
    $details = new GetAccountDetails();
    $details->getBalance($accountId);
    exit;
} else {
    echo 0;
}
?>