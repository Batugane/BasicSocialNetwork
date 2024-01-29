<?php
include("userDb.php");
session_start();
include("csrf.php");
// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];



if (csrf_check()) {
    // Retrieve the postId from the AJAX request
    $postId = $_POST['postId'];
    $action = $_POST['action'];
    try {


        switch ($action) {
            case 'like':
                $stmt = $db->prepare("INSERT INTO like_dislike (userId, postId, type, created_at) VALUES (:user_id, :post_id, 'like', NOW())");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':post_id', $postId);
                $stmt->execute();
                break;
            case 'dislike':
                $stmt = $db->prepare("INSERT INTO like_dislike (userId, postId, type, created_at) VALUES (:user_id, :post_id, 'dislike', NOW())");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':post_id', $postId);
                $stmt->execute();
                break;
            case 'removeLike':
                $stmt = $db->prepare("DELETE FROM like_dislike WHERE userId = :user_id AND postId = :post_id AND type = 'like'");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':post_id', $postId);
                $stmt->execute();
                break;
            case 'removeDislike':
                $stmt = $db->prepare("DELETE FROM like_dislike WHERE userId = :user_id AND postId = :post_id AND type = 'dislike'");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':post_id', $postId);
                $stmt->execute();
                break;
            case 'updateLike':
                $stmt = $db->prepare("UPDATE like_dislike SET type = 'like' WHERE userId = :user_id AND postId = :post_id AND type = 'dislike'");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':post_id', $postId);
                $stmt->execute();
                break;
            case 'updateDislike':
                $stmt = $db->prepare("UPDATE like_dislike SET type = 'dislike' WHERE userId = :user_id AND postId = :post_id AND type = 'like'");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->bindParam(':post_id', $postId);
                $stmt->execute();
                break;
        }

        // Retrieve the like count
        $stmt = $db->prepare("SELECT COUNT(*) AS likeCount FROM like_dislike WHERE postId = :post_id AND type = 'like'");
        $stmt->bindParam(':post_id', $postId);
        $stmt->execute();
        $likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['likeCount'];

        // Retrieve the dislike count
        $stmt = $db->prepare("SELECT COUNT(*) AS dislikeCount FROM like_dislike WHERE postId = :post_id AND type = 'dislike'");
        $stmt->bindParam(':post_id', $postId);
        $stmt->execute();
        $dislikeCount = $stmt->fetch(PDO::FETCH_ASSOC)['dislikeCount'];

        // Return the response with postId, likeCount, and dislikeCount
        $response = ['likeCount' => $likeCount, 'dislikeCount' => $dislikeCount, 'success' => true];
        echo json_encode($response);
    } catch (PDOException $e) {
        // Return an error response
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        echo json_encode($response);
    }
} else {
    echo "CSRF Error ";
    exit;
}
