<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json");

class FetchProfilePicture extends Config {
    private $userId;
    private $connection;

    public function __construct($userId) {
        parent::__construct();
        $this->userId = $userId;
        $this->connection = $this->getConnection();
    }

    public function getProfilePicture() {
        $query = "SELECT profile_picture FROM `bank_table` WHERE `user_id` = ?";
        $stmt = $this->connection->prepare($query);

        if ($stmt) {
            $stmt->bind_param('i', $this->userId);
            $stmt->execute();
            $stmt->bind_result($profilePicture);
            $stmt->fetch();
            $stmt->close();

            if ($profilePicture) {
                return [
                    "success" => true,
                    "profile_picture_url" => $profilePicture
                ];
            } else {
                return ["success" => false, "error" => "Profile picture not found."];
            }
        } else {
            return ["success" => false, "error" => "Failed to prepare statement: " . $this->connection->error];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['userId'])) {
        $userId = $_GET['userId'];
        $fetchProfilePicture = new FetchProfilePicture($userId);
        $result = $fetchProfilePicture->getProfilePicture();
        echo json_encode($result);
    } else {
        echo json_encode(["success" => false, "error" => "User ID not provided."]);
    }
}
?>
