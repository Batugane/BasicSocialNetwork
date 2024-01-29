<?php
include("userDb.php");
session_start();

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$postIds = $_POST['postIds'];
$user = $_SESSION["user"];

// Fetch the like and dislike counts for the specified postIds
try {
    // Prepare the SQL statement to fetch the like and dislike counts
    $stmt = $db->prepare("SELECT postId, SUM(CASE WHEN type = 'like' THEN 1 ELSE 0 END) AS likeCount, SUM(CASE WHEN type = 'dislike' THEN 1 ELSE 0 END) AS dislikeCount FROM like_dislike WHERE postId IN (" . implode(',', $postIds) . ") GROUP BY postId");
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll();

    // Fetch the user's like and dislike status for the specified postIds
    $stmt = $db->prepare("SELECT postId, type FROM like_dislike WHERE userId = :user_id AND postId IN (" . implode(',', $postIds) . ")");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->execute();

    // Fetch the user's like and dislike statuses
    $userLikes = [];
    $userDislikes = [];
    while ($row = $stmt->fetch()) {
        $postId = $row['postId'];
        $type = $row['type'];
        if ($type === 'like') {
            $userLikes[] = $postId;
        } elseif ($type === 'dislike') {
            $userDislikes[] = $postId;
        }
    }

    // Prepare the final response array
    $response = [];
    foreach ($results as $result) {
        $postId = $result['postId'];
        $likeCount = $result['likeCount'];
        $dislikeCount = $result['dislikeCount'];

        $alreadyLiked = in_array($postId, $userLikes);
        $alreadyDisliked = in_array($postId, $userDislikes);

        $response[] = [
            'postId' => $postId,
            'likeCount' => $likeCount,
            'dislikeCount' => $dislikeCount,
            'alreadyLiked' => $alreadyLiked,
            'alreadyDisliked' => $alreadyDisliked
        ];
    }

    // Return the response as JSON
    echo json_encode($response);
} catch (PDOException $e) {
    // Return an error response
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    echo json_encode($response);
}
