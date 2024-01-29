<?php
include("userDb.php");
session_start();

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

// Retrieve the postId from the AJAX request
$postId = $_POST['postId'];

// Insert the like record into the database
try {
    // Insert the like record into the database
    $stmt = $db->prepare("INSERT INTO like_dislike (userId, postId, type) VALUES (:user_id, :post_id, 'dislike')");
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->bindParam(':post_id', $postId);
    $stmt->execute();

    // Return a success response with the alreadyLiked status
    $response = ['success' => true];
    echo json_encode($response);
} catch (PDOException $e) {
    // Return an error response
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    echo json_encode($response);
}
