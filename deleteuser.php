<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

class DeleteUser extends config {
    public function deleteUser() {
        $userId = $_POST['user_id'];

        // Delete from users table
        $query = "DELETE FROM users WHERE id = ?";
        $binder = ['i', $userId]; // 'i' for integer
        $userDeleted = $this->create($query, $binder);

        // Delete from accounts table
        if ($userDeleted) {
            $accountQuery = "DELETE FROM accounts WHERE user_id = ?";
            $binder = ['i', $userId];
            $accountDeleted = $this->create($accountQuery, $binder);
        }

        if ($userDeleted && $accountDeleted) {
            echo json_encode(['message' => 'User deleted successfully']);
        } else {
            echo json_encode(['message' => 'Error deleting user']);
        }
    }
}

// Instantiate the class and call the method
$deleteUser = new DeleteUser();
$deleteUser->deleteUser();
?>
