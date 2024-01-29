<?php
session_start();
require "userDb.php";

// check if the user is already authed.
if (isset($_SESSION["user"])) {
  header("Location: main.php");
  exit;
}

if (!empty($_POST)) {
  extract($_POST); // $email, $pass


  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  $pass = filter_var($pass, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  if (checkUser($email, $pass)) {
    // authenticated
    $_SESSION["user"] = getUser($email); // email, hashpassword, namesurname
    header("Location: main.php");
    exit;
  }
  $authError = true;
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>Login Page</title>
  <link rel="stylesheet" href="login.css" />
</head>

<body>
  <div class="container">
    <h1>Login</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="text" name="email" value="<?= $email ?? '' ?>" required />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="pass" required />
      </div>
      <div class="form-group">
        <input type="submit" value="Login" />
      </div>
    </form>
    <?php

    if (isset($authError))
      echo " <p class='message'>Incorrect username or password.</p>";

    ?>

    <div class="register-link">
      <p>Don't have an account? <a href="register.php">Register!</a></p>
    </div>
  </div>
</body>

</html>