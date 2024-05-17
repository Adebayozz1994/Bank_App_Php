<?php
require_once("config.php");
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
        $tmp= $picture['tmp_name'];
        $newname=time().$name;
        $move=move_uploaded_file($tmp,"pictures/".$newname);

        if ($move) {
            $query = "UPDATE `bank_table` SET `profile_picture` = ? WHERE `user_id` = ?";
            $stmt = $this->connection->prepare($query);

            $stmt->bind_param('si', $newname, $this->userId);

          
        } 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];
    $file = $_FILES['file'];

    $uploadHandler = new UploadHandler($userId);
    $result = $uploadHandler->uploadProfilePicture($file);

    echo json_encode($result);
}
?>
