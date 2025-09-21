<?php
require_once __DIR__ . '/Database.php';
class PostModel {
    private $conn;
    public function __construct($dbConn=null){ if($dbConn) $this->conn=$dbConn; else $this->conn=(new Database())->getConnection(); }
    public function create($user_id,$content,$image=null){ $stmt=$this->conn->prepare('INSERT INTO posts (user_id,content,image) VALUES (?,?,?)'); $stmt->bind_param('iss',$user_id,$content,$image); return $stmt->execute(); }
    public function getAll(){ $sql="SELECT posts.*, users.fullname, users.profile_pic FROM posts JOIN users ON posts.user_id=users.id ORDER BY posts.created_at DESC"; return $this->conn->query($sql); }
}
?>