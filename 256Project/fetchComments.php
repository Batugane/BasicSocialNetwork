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
    $stmt = $db->prepare("SELECT c.*, CONCAT(u.name, ' ', u.surname) AS fullname, u.profilePic FROM comment c INNER JOIN user u ON c.userId = u.id");

    $stmt->execute();

    $comments = $stmt->fetchAll();

    echo json_encode($comments);
} catch (PDOException $e) {
    // Handle the error response
    echo "Error: " . $e->getMessage();
}
