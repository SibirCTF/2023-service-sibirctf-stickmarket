<?php
require '../vendor/autoload.php';
require_once('db.php');

use Ramsey\Uuid\Uuid;

if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST['username']) && !empty($_POST['password'])) {
    $login = stripslashes(strip_tags($_POST['username']));
    $password = hash('sha256', $_POST['password']);
    if (UserExists($login)) {
        $message="Error: User already exists";
    } else {
        $db = ConnectDatabase();
        $uuid = Uuid::uuid4()->toString();
        $stmt = $db->prepare("INSERT INTO users(username, password, uuid) VALUES (?, ?, ?)");        
        $result = $stmt -> execute(array($login, $password, $uuid));
        if ($result) {
            ob_clean();
            header("Location: /login.php");
            die();
        } else {
            $message = "Error. Try again.";
       }
    }
} else {
    $message = implode(';', $_POST);
}

?>

<!DOCTYPE html>
<html lang="en">

<link rel="icon" type="image/x-icon" href="static/assets/favicon.ico" />
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" href="static/css/style_login.css">
<div class="wrapper fadeInDown">
  <div id="formContent">
    <div class="fadeIn first">
      <img src="/static/welcome.jpg" id="icon"/>
    </div>

    <form method="POST">
      <center>
      <input required type="text" id="username" class="fadeIn second" name="username" placeholder="username">
      <input required type="password" id="password" class="fadeIn third" name="password" placeholder="password">
      <input type="submit" class="fadeIn bg-dark" value="Sign up">
    </form>

    <div id="formFooter">
        <a> 
            <?php 
                if (isset($message) ) {
                    echo $message;
                }
            ?> 
        </a>
    </div>
    <div id="formFooter">
        <a class="underlineHover" href="/index.php">Sign in</a>
      </div>
  </div>
</div>