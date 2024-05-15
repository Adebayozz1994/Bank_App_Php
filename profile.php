<?php
session_start();
require("connect.php");

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Check if user is logged in
if (isset($_SESSION['userId'])) {
    $userid = $_SESSION['userId'];
    $query = "SELECT * FROM user_table WHERE user_id = $userid";
    $result = $connection->query($query);
    
    // Check if user exists
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Construct user profile data
        $userProfile = [
            'userId' => $user['user_id'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            // Add more user details as needed
        ];
        
        // Return user profile data as JSON
        echo json_encode($userProfile);
    } else {
        // User not found
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
} else {
    // User not logged in
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
}
?>
