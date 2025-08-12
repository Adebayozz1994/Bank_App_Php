<?php
require_once("config.php");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Content-Type: application/json");

$user_id = $_GET['user_id'];

$conn = new mysqli('localhost', 'root', '', 'bank_app');
$query = "SELECT * FROM data_transactions WHERE account_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
echo json_encode($history);

$stmt->close();
$conn->close();
?>