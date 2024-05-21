<?php
require('config.php');

header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

class UploadHandler extends Config {
    private $userId;
    private $connection;

    public function __construct($userId) {
        parent::__construct();
        $this->userId = $userId;
        $this->connection = $this->getConnection(); 
    }

    public function uploadProfilePicture() {

        $picture = $_FILES['file'];
        $name = $picture['name'];
        $tmp = $picture['tmp_name'];
        $newname = time() . '_' . $name;
        $uploadPath = "pictures/".$newname;
    
        if (move_uploaded_file($tmp, $uploadPath)) {
            $query = "UPDATE `bank_table` SET `profile_picture` = ? WHERE `user_id` = ?";
            $stmt = $this->connection->prepare($query);
            if($stmt){
                $stmt->bind_param('si', $newname, $this->userId);
                // $stmt->execute();
                if ($stmt->execute()) {
                    return [
                        "success" => true, 
                        "profile_picture_url" => $uploadPath,
                        'userId' => $this->userId
                    ];
                    ;
                
                } 
                else {
                    return ["success" => false, "error" => "Failed to execute statement: " . $stmt->error];
                }
            };
            // if (!$stmt) {
            //     return ["success" => false, "error" => "Failed to prepare statement: " . $this->connection->error];
            // }
    
    
            // if ($stmt->execute()) {
            //     return ["success" => true, "profile_picture_url" => $uploadPath];
            // } else {
            //     return ["success" => false, "error" => "Failed to execute statement: " . $stmt->error];
            // }
        } else {
            return ["success" => false, "error" => "Failed to move uploaded file"];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];
    $uploadHandler = new UploadHandler($userId);
    $result = $uploadHandler->uploadProfilePicture();

    echo json_encode($result);
}
?>
