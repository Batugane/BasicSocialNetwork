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
    // Fetch friend requests
    $friendRequestsQry = $db->prepare("SELECT * FROM friendRequest WHERE receiverId = :user_id AND status='pending'");
    $friendRequestsQry->execute(array(':user_id' => $user['id']));
    $friendRequestsResult = $friendRequestsQry->fetchAll();

    $friendRequestsCount = count($friendRequestsResult);
    $friendRequests = array();
    if ($friendRequestsCount != 0) {
        foreach ($friendRequestsResult as $row) {
            $senderQry = $db->prepare("SELECT * FROM user WHERE id = :id");
            $senderQry->execute(array(':id' => $row['senderId']));
            $senderResult = $senderQry->fetchAll();
            $friendRequests[] = array(
                'type' => 'friendRequest',
                'sender' => $senderResult[0],
                'creationDate' => $friendRequestsResult[0][3]
            );
        }
    }

    // Fetch friend removal notifications
    $friendRemovalsQry = $db->prepare("SELECT * FROM friendRemovalNotification WHERE removedUserId = :user_id");
    $friendRemovalsQry->execute(array(':user_id' => $user['id']));
    $friendRemovalsResult = $friendRemovalsQry->fetchAll();

    $friendRemovalsCount = count($friendRemovalsResult);
    $friendRemovals = array();
    if ($friendRemovalsCount != 0) {
        foreach ($friendRemovalsResult as $row) {
            $removerQry = $db->prepare("SELECT * FROM user WHERE id = :id");
            $removerQry->execute(array(':id' => $row['removerUserId']));
            $removerResult = $removerQry->fetchAll();
            $friendRemovals[] = array(
                'type' => 'friendRemoval',
                'remover' => $removerResult[0],
                'creationDate' => $friendRemovalsResult[0][3]


            );
        }
    }

    // Combine friend requests and friend removals
    $notifications = array_merge($friendRequests, $friendRemovals);

    // Return the result as JSON
    echo json_encode($notifications);
} catch (PDOException $e) {
    // Handle database connection error
    echo json_encode(['error' => 'Database connection error']);
}
