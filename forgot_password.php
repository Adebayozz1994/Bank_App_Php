<?php
require_once("config.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

class ForgotPassword extends config {
    public function __construct() {
        parent::__construct();
    }

    public function requestPasswordReset($email) {
        if (empty($email)) {
            return ['status' => false, 'message' => 'Email is required'];
        }
        
        $query = "SELECT user_id, first_name FROM `bank_table` WHERE `email` = ?";
        $stmt = $this->connect->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'Email not found'];
        }
        
        $user = $result->fetch_assoc();
        
        // Generate a unique reset token
        $reset_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
        
        // First, delete any existing reset tokens for this email
        $deleteQuery = "DELETE FROM `password_resets` WHERE `email` = ?";
        $deleteStmt = $this->connect->prepare($deleteQuery);
        if (!$deleteStmt) {
            return ['status' => false, 'message' => 'Database error: ' . $this->connect->error];
        }
        $deleteStmt->bind_param('s', $email);
        $deleteStmt->execute();
        
        // Store the new reset token in the database
        $insertQuery = "INSERT INTO `password_resets` (`email`, `token`, `expires_at`) VALUES (?, ?, ?)";
        $insertStmt = $this->connect->prepare($insertQuery);
        if (!$insertStmt) {
            return ['status' => false, 'message' => 'Database prepare error: ' . $this->connect->error];
        }
        $insertStmt->bind_param('sss', $email, $reset_token, $expires_at);
        
        if ($insertStmt->execute()) {
            // Verify the record was actually inserted
            $verifyQuery = "SELECT COUNT(*) as count FROM `password_resets` WHERE `email` = ? AND `token` = ?";
            $verifyStmt = $this->connect->prepare($verifyQuery);
            $verifyStmt->bind_param('ss', $email, $reset_token);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            $count = $verifyResult->fetch_assoc()['count'];
            
            if ($count > 0) {
                // Create reset link
                $reset_link = "http://localhost:4200/reset-password?token=" . $reset_token;
                
                return [
                    'status' => true, 
                    'message' => 'Password reset instructions sent to your email',
                    'debug_reset_link' => $reset_link,
                    'debug_info' => [
                        'email' => $email,
                        'token' => $reset_token,
                        'expires_at' => $expires_at,
                        'records_inserted' => $count
                    ]
                ];
            } else {
                return ['status' => false, 'message' => 'Record was not inserted properly'];
            }
        } else {
            return ['status' => false, 'message' => 'Failed to insert: ' . $insertStmt->error];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $forgotPassword = new ForgotPassword();
    $response = $forgotPassword->requestPasswordReset($input['email'] ?? '');
    echo json_encode($response);
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
}
?>
