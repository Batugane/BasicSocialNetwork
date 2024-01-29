<?php

// connection string
const DSN = "mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4";
const USER = "root";
const PASSWORD = "";
date_default_timezone_set('Europe/Istanbul');
// connect to database, $db represents mysql dbms
$db = new PDO(DSN, USER, PASSWORD);
// $db->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
function checkUser($email, $rawPass)
{
   global $db;
   $stmt = $db->prepare("select * from user where email = ?");
   $stmt->execute([$email]);
   if ($stmt->rowCount()) {
      // email exists
      $user = $stmt->fetch();
      return password_verify($rawPass, $user["password"]);
   }
   return false;
}

function getUser($email)
{
   global $db;
   $stmt = $db->prepare("select * from user where email = ?");
   $stmt->execute([$email]);
   return $stmt->fetch();
}
