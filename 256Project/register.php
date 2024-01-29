<?php

$error = [];
if (!empty($_POST)) {
  // var_dump($_POST) ;
  // var_dump($_FILES) ;
  require "userDb.php"; // db connection
  extract($_POST); // email, pass, username
  $pass = filter_var($pass, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $surname = filter_var($surname, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  $length = mb_strlen(trim($pass));
  if ($length < 8)
    $error['pass'] = "Passwords must be at least 8 characters long.";

  if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $error['email'] = "Wrong Email format.";

  if (!$error) {


    require "Upload.php";
    $profile = new Upload("profilePic", "images");
    $hashPassw = password_hash($pass, PASSWORD_BCRYPT);
    $sql = "insert into user (email,password,name,surname,profilePic,birthdate) values (?,?,?,?,?,?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email, $hashPassw, $name, $surname, $profile->filename, $birthdate]);
    $error = [];
    // redirect to the login page
    header("Location: index.php?register=ok");
    exit;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Registration Page</title>
  <link rel="stylesheet" href="register.css" />
</head>

<body>
  <div class="container">
    <h1>Register</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" class="border" value="<?= $name ?? '' ?>" required />
      </div>
      <div class="form-group">
        <label for="surname">Surname</label>
        <input type="text" class="border" name="surname" value="<?= $surname ?? '' ?>" required />
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="text" name="email" class="<?= isset($error['email']) ? 'err' : 'border' ?>" value="<?= $email ?? '' ?>" required />
      </div>
      <div class="form-group">
        <label for="birthdate">Birth Date</label>
        <input type="date" max='<?= $currentDate = date("Y-m-d") ?>' min='1907-07-19' name="birthdate" class="border" value="<?= $birthdate ?? '' ?>" required />
      </div>
      <div class="form-group">
        <label for="profilePic">Profile Picture</label>
        <input type="file" name="profilePic" accept="image/png, image/gif, image/jpeg" />
      </div>
      <div class="form-group">
        <label for="pass">Password</label>
        <input type="password" id="pass" name="pass" class="<?= isset($error['pass']) ? 'err' : 'border' ?>" required />
        <input type="checkbox" onclick="myFunction()">Show Password
        <script>
          function myFunction() {
            var x = document.getElementById("pass");
            if (x.type === "password") {
              x.type = "text";
            } else {
              x.type = "password";
            }
          }
        </script>
      </div>
      <div class="form-group">
        <button>Register</button>
      </div>
    </form>
    <?php
    if (!empty($error)) {
      foreach ($error as $k => $errMsg) {
        echo "<p class='message'>" . $errMsg . "</p> ";
      }
    }


    ?>
    <!-- <p class="message">Passwords must be at least 8 characters long.</p> -->

    <div class="login-link">
      <p>Already have an account? <a href="index.php">Login</a></p>
    </div>
  </div>
</body>

</html>