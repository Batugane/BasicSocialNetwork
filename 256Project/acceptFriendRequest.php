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

if (isset($_POST['input'])) {

    if (csrf_check()) {
        $input = $_POST['input'];

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE friendRequest SET status = 'accepted' WHERE senderId = ? AND receiverId = ?");
            $stmt->execute([$input, $user['id']]);

            $stmt = $db->prepare("INSERT INTO friendship (user1, user2, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$input, $user['id']]);

            $db->commit();

            echo "Friendship recorded successfully!";
        } catch (PDOException $e) {
            $db->rollBack();

            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "CSRF Error ";
        exit;
    }
}
