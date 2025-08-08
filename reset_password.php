<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

class ResetPassword extends config {
    public function __construct() {
        parent::__construct();
    }

    public function resetPassword($token, $newPassword) {
        if (empty($token)) {
            return ['status' => false, 'message' => 'Reset token is required'];
        }
        
        if (empty($newPassword)) {
            return ['status' => false, 'message' => 'New password is required'];
        }
        
        // Validate password length
        if (strlen($newPassword) < 6) {
            return ['status' => false, 'message' => 'Password must be at least 6 characters long'];
        }
        
        // Check if token exists and is not expired
        $query = "SELECT `email` FROM `password_resets` WHERE `token` = ? AND `expires_at` > NOW()";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'Invalid or expired reset token'];
        }
        
        $reset_record = $result->fetch_assoc();
        $email = $reset_record['email'];
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update the user's password
        $updateQuery = "UPDATE `bank_table` SET `password` = ? WHERE `email` = ?";
        $updateStmt = $this->connect->prepare($updateQuery);
        $updateStmt->bind_param('ss', $hashedPassword, $email);
        
        if ($updateStmt->execute()) {
            // Delete the used reset token
            $deleteQuery = "DELETE FROM `password_resets` WHERE `token` = ?";
            $deleteStmt = $this->connect->prepare($deleteQuery);
            $deleteStmt->bind_param('s', $token);
            $deleteStmt->execute();
            
            return ['status' => true, 'message' => 'Password reset successfully'];
        } else {
            return ['status' => false, 'message' => 'Failed to update password'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $resetPassword = new ResetPassword();
    $response = $resetPassword->resetPassword($input['token'] ?? '', $input['newPassword'] ?? '');
    echo json_encode($response);
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
}
?>
