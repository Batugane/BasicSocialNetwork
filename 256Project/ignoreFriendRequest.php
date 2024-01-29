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
            // Begin a transaction
            $db->beginTransaction();

            // Update the friend request status to 'ignored'
            $stmt = $db->prepare("UPDATE friendRequest SET status = 'ignored' WHERE senderId = ? AND receiverId = ?");
            $stmt->execute([$input, $user['id']]);


            $db->commit();

            // Handle the success response
            echo "Request ignored!";
        } catch (PDOException $e) {
            // Rollback the transaction if there's an error
            $db->rollBack();

            // Handle the error response
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "CSRF Error ";
        exit;
    }
}
