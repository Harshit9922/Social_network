<?php
require_once __DIR__ . '/Database.php';
class PostModel {
    private $conn;
    public function __construct($dbConn=null){ if($dbConn) $this->conn=$dbConn; else $this->conn=(new Database())->getConnection(); }
    public function create($user_id,$content,$image=null){ $stmt=$this->conn->prepare("INSERT INTO posts (user_id,content,image) VALUES (?,?,?)"); $stmt->bind_param('iss',$user_id,$content,$image); return $stmt->execute(); }
    public function getAll(){ $sql="SELECT posts.*, users.fullname, users.profile_pic FROM posts JOIN users ON users.id=posts.user_id ORDER BY posts.created_at DESC"; return $this->conn->query($sql); }
    public function delete($post_id, $user_id){
        $stmt = $this->conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $post_id, $user_id);
        return $stmt->execute();
    }

    public function getByUser($user_id){
        // FIXED: Added users.email to the SELECT statement to fix the warning on user.php
        $stmt = $this->conn->prepare("SELECT posts.*, users.fullname, users.profile_pic, users.email FROM posts JOIN users ON posts.user_id = users.id WHERE posts.user_id = ? ORDER BY posts.created_at DESC");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }

}
?>