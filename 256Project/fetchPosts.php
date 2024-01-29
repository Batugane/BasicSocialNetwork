<?php
include("userDb.php");
session_start();

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

// Prepare the SQL query
$sql = "SELECT p.*, 
    u.name AS ownerName, 
    u.surname AS ownerSurname, 
    u.profilePic AS ownerProfilePic,
    COALESCE(lc.likeCount, 0) AS likeCount,
    COALESCE(dc.dislikeCount, 0) AS dislikeCount,
    CASE WHEN ld1.type = 'like' THEN 1 ELSE 0 END AS alreadyLiked,
    CASE WHEN ld2.type = 'dislike' THEN 1 ELSE 0 END AS alreadyDisliked
FROM post AS p
LEFT JOIN friendship AS f ON (p.userId = f.user1 OR p.userId = f.user2)
INNER JOIN user AS u ON (p.userId = u.id)
LEFT JOIN (
    SELECT postId, COUNT(*) AS likeCount
    FROM like_dislike
    WHERE type = 'like'
    GROUP BY postId
) AS lc ON p.id = lc.postId
LEFT JOIN (
    SELECT postId, COUNT(*) AS dislikeCount
    FROM like_dislike
    WHERE type = 'dislike'
    GROUP BY postId
) AS dc ON p.id = dc.postId
LEFT JOIN like_dislike AS ld1 ON p.id = ld1.postId AND ld1.userId = :userId AND ld1.type = 'like'
LEFT JOIN like_dislike AS ld2 ON p.id = ld2.postId AND ld2.userId = :userId AND ld2.type = 'dislike'
WHERE (f.user1 = :userId OR f.user2 = :userId OR p.userId = :userId)
GROUP BY p.id  
ORDER BY p.creationDate DESC;";




// Prepare the statement
$stmt = $db->prepare($sql);

// Bind the parameter
$stmt->bindValue(':userId', $user['id']);

// Execute the query
$stmt->execute();

// Fetch all the rows
$posts = $stmt->fetchAll();

// Convert the result to JSON format
$jsonPosts = json_encode($posts);

// Return the JSON response
header('Content-Type: application/json');
echo $jsonPosts;
