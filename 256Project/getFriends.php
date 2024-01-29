<?php
include("userDb.php");
session_start();

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION["user"];

try {
    $stmt = $db->prepare(" SELECT u.id, u.name, u.surname, u.profilePic
      FROM friendship AS f
      JOIN user AS u ON (f.user1 = u.id OR f.user2 = u.id)
      WHERE (f.user1 = :userId OR f.user2 = :userId) AND u.id != :userId");
    $stmt->bindParam(':userId', $user['id']);
    $stmt->execute();

    // Fetch the friends data
    $friends = $stmt->fetchAll();

    echo json_encode($friends);
} catch (PDOException $e) {
    // Handle the error response
    echo "Error: " . $e->getMessage();
}
