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

        try {
            $input = $_POST['input'];


            $db->beginTransaction();

            $stmt = $db->prepare("DELETE FROM friendship WHERE (user1 = :input1 AND user2 = :input2) OR (user1 = :input2 AND user2 = :input1)");

            $values = array(
                ':input1' => $input,
                ':input2' => $user['id']
            );


            $stmt->execute($values);

            $insertStmt = $db->prepare("INSERT INTO friendremovalnotification (removerUserId, removedUserId,creationDate) VALUES (?, ?,NOW())");



            $insertStmt->execute([$user['id'], $input]);

            $db->commit();

            echo "success";
        } catch (PDOException $e) {
            $db->rollBack();

            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "CSRF Error ";
        exit;
    }
}
