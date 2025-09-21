<?php
class Database {
    private $host='localhost',$user='root',$pass='',$dbname='social_network_oop_db',$conn;
    public function __construct(){ $this->connect(); $this->initSchema(); }
    private function connect(){ $this->conn=new mysqli($this->host,$this->user,$this->pass); if($this->conn->connect_error) die('DB Conn Err:'.$this->conn->connect_error); $this->conn->set_charset('utf8mb4'); $this->conn->query("CREATE DATABASE IF NOT EXISTS `".$this->dbname."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); $this->conn->select_db($this->dbname); }
    private function initSchema(){ 
        // Updated users table to include the 'intermediate' column
        $this->conn->query("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, fullname VARCHAR(100) NOT NULL, dob DATE NOT NULL, email VARCHAR(100) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, profile_pic VARCHAR(255) DEFAULT 'uploads/default.png', intermediate VARCHAR(255) DEFAULT 'Beginner', created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); 
        
        $this->conn->query("CREATE TABLE IF NOT EXISTS posts (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, content TEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); 
        
        $this->conn->query("CREATE TABLE IF NOT EXISTS likes (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, post_id INT NOT NULL, type ENUM('like','dislike') NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_like (user_id, post_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); 
    }
    public function getConnection(){ return $this->conn; }
}
?>