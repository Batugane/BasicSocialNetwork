<?php
session_start();
include("userDb.php");
include("csrf.php");

// Logical verification
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}




$user = $_SESSION["user"];



if (isset($_POST["postId"]) && isset($_POST["input"])) {

    if (csrf_check()) {
        extract($_POST);
        $currentDate = date("Y-m-d H:i:s");
        $input = filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $stmt = $db->prepare("INSERT INTO comment (postId, userId, content,	creationDate ) VALUES (?, ?, ?, ?)");
        $stmt->execute([$postId, $user['id'], $input, $currentDate]);
    } else {
        echo "CSRF Error ";
        exit;
    }
}
