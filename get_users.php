<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class GetUsers extends config {
    
    public function fetchUsers() {
        $connection = $this->getConnection();
        
        $query = "SELECT user_id, first_name, last_name, email FROM bank_table";
        $result = $connection->query($query);
        
        $users = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        echo json_encode($users);
    }
}

$getUsers = new GetUsers();
$getUsers->fetchUsers();
?>
