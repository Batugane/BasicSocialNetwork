<?php
const SALTING = "_CTIS_SALT_9082766230482601";

function csrf_check()
{
    if (isset($_POST["csrf_token"]) && isset($_COOKIE["secret"])) {
        return password_verify($_COOKIE["secret"] . SALTING, $_POST["csrf_token"]);
    }
    return false;
}

function create_csrf_token()
{
    $secret = bin2hex(random_bytes(10));
    setcookie("secret", $secret);
    return password_hash($secret . SALTING, PASSWORD_BCRYPT);
}
