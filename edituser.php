<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class EditUser extends config {
    public function editUser() {
        $userId = $_POST['user_id'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];

        $query = "UPDATE bank_table SET first_name = ?, last_name = ?, email = ? WHERE id = ?";
        $stmt = $this->connect->prepare($query); 

        // Bind the parameters and execute the statement
        $stmt->bind_param("sssi", $firstName, $lastName, $email, $userId);

        if($stmt->execute()) {
            echo json_encode(['message' => 'User updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating user']);
        }
        
        $stmt->close();
    }
}

$editUser = new EditUser();
$editUser->editUser();
?>
