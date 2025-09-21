<?php
class LikeModel
{
    private $conn;

    public function __construct($dbConn)
    {
        $this->conn = $dbConn;
    }

    /**
     * Toggles a like or dislike for a user on a post.
     * Handles all cases: liking, unliking, and changing a vote.
     */
    public function toggle($user_id, $post_id, $type)
    {
        // Check for an existing vote by this user on this post
        $stmt = $this->conn->prepare("SELECT type FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param('ii', $user_id, $post_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            // Case 1: The user clicks the same button again (e.g., clicks 'like' on a liked post)
            if ($result['type'] === $type) {
                // Remove the vote (this is the "unlike" or "undislike" action)
                $delete_stmt = $this->conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
                $delete_stmt->bind_param('ii', $user_id, $post_id);
                return $delete_stmt->execute();
            } else {
                // Case 2: The user changes their vote (e.g., from like to dislike)
                $update_stmt = $this->conn->prepare("UPDATE likes SET type = ? WHERE user_id = ? AND post_id = ?");
                $update_stmt->bind_param('sii', $type, $user_id, $post_id);
                return $update_stmt->execute();
            }
        } else {
            // Case 3: No vote exists from this user, so insert a new one
            $insert_stmt = $this->conn->prepare("INSERT INTO likes (user_id, post_id, type) VALUES (?, ?, ?)");
            $insert_stmt->bind_param('iis', $user_id, $post_id, $type);
            return $insert_stmt->execute();
        }
    }

    /**
     * Gets the total count of likes and dislikes for a given post from all users.
     */
    public function counts($post_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                (SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = 'like') as likes,
                (SELECT COUNT(*) FROM likes WHERE post_id = ? AND type = 'dislike') as dislikes"
        );
        $stmt->bind_param('ii', $post_id, $post_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // Ensure we always return a number, even if the result is null (no likes yet)
        return [
            'likes' => $result['likes'] ?? 0,
            'dislikes' => $result['dislikes'] ?? 0,
        ];
    }
}
