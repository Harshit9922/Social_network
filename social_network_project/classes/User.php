<?php
require_once __DIR__ . '/Database.php';

class User
{
    private $conn;

    public function __construct($dbConn = null)
    {
        if ($dbConn) $this->conn = $dbConn;
        else $this->conn = (new Database())->getConnection();
    }

    public function register($fullname, $dob, $email, $password, $profile_pic)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['success' => false, 'error' => 'Invalid email'];
        if (strlen($password) < 6) return ['success' => false, 'error' => 'Password too short'];
        
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $intermediate = ''; // Default value for new users
        
        $stmt = $this->conn->prepare('INSERT INTO users (fullname,dob,email,password,profile_pic,intermediate) VALUES (?,?,?,?,?,?)');
        if (!$stmt) return ['success' => false, 'error' => $this->conn->error];
        
        $stmt->bind_param('ssssss', $fullname, $dob, $email, $hash, $profile_pic, $intermediate);
        
        if ($stmt->execute()) return ['success' => true];
        else return ['success' => false, 'error' => $stmt->error ?: $this->conn->error];
    }

    public function getByEmail($email)
    {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfilePic($user_id, $profile_pic)
    {
        $stmt = $this->conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
        $stmt->bind_param("si", $profile_pic, $user_id);
        return $stmt->execute();
    }

    /**
     * Updates a user's profile information (fullname, email, intermediate).
     * CRITICAL FIX: Only updates fields if a non-empty value is provided.
     */
    public function updateProfile($user_id, $fullname, $email, $intermediate)
    {
        $queryParts = [];
        $params = [];
        $types = '';

        if (!empty($fullname)) {
            $queryParts[] = "fullname = ?";
            $params[] = $fullname;
            $types .= 's';
        }
        if (!empty($email)) {
            $queryParts[] = "email = ?";
            $params[] = $email;
            $types .= 's';
        }
        if (!empty($intermediate)) {
            $queryParts[] = "intermediate = ?";
            $params[] = $intermediate;
            $types .= 's';
        }

        // If no new information was provided, do nothing. This prevents the error.
        if (empty($queryParts)) {
            return true;
        }

        $params[] = $user_id;
        $types .= 'i';

        $sql = "UPDATE users SET " . implode(', ', $queryParts) . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();

        // If the email was successfully updated, also update the session email
        if ($success && !empty($email)) {
            $_SESSION['email'] = $email;
        }

        return $success;
    }

    public function resetPassword($email, $newPassword)
    {
        $user = $this->getByEmail($email);
        if (!$user) return ['success' => false, 'error' => 'No such user'];
        
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hash, $email);

        if ($stmt->execute()) return ['success' => true];
        
        return ['success' => false, 'error' => $this->conn->error];
    }
}

