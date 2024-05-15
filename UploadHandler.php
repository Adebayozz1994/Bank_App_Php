<?php
require_once("config.php");

class UploadHandler extends config{
    private $userId;
    private $connection;

    public function __construct($userId, $connection) {
        $this->userId = $userId;
        $this->connection = $connection; 
    }

    public function uploadProfilePicture($file) {
        $name = $file['name'];
        $tmp = $file['tmp_name'];
        $newname = time() . $name;
        $uploadPath = "pictures/" . $newname;

        if (move_uploaded_file($tmp, $uploadPath)) {
           
            $query = "UPDATE `user_table` SET `profile_picture` = ? WHERE `user_id` = ?";
            $stmt = $this->connection->prepare($query);

            if (!$stmt) {
                return false; 
            }

            $stmt->bind_param('si', $newname, $this->userId);

            if ($stmt->execute()) {
                return true; 
            } else {
                return false; 
            }
        } else {
            return false; 
        }
    }
}
?>
