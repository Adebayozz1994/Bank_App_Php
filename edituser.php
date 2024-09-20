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

        $query = "UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?";
        $binder = ['sssi', $firstName, $lastName, $email, $userId]; // 's' for string, 'i' for integer
        $updateSuccess = $this->create($query, $binder);

        if ($updateSuccess) {
            echo json_encode(['message' => 'User updated successfully']);
        } else {
            echo json_encode(['message' => 'Error updating user']);
        }
    }
}

// Instantiate the class and call the method
$editUser = new EditUser();
$editUser->editUser();
?>
