<?php

include("userDb.php");

session_start();

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}
$user = $_SESSION["user"];


if (isset($_POST['receiverId'])) {
    $receiverId = $_POST['receiverId'];
    var_dump($receiverId);

    $stmt = $db->prepare("INSERT INTO friendRequest (senderId, receiverId, creationDate,status) VALUES (?,?,?,?)");
    $stmt->execute([$user['id'], $receiverId, date("Y-m-d H:i:s"), 'pending']);
}
