<?php

if (isset($_SESSION['uuid'])) {
    header("Location: /index.php");
}
session_start();
ob_start();

require_once('db.php');


if ($_SERVER['REQUEST_METHOD'] === "POST" && !empty($_POST['username']) && !empty($_POST['password'])){
    $login = stripslashes(strip_tags($_POST['username']));
    $password = hash('sha256', $_POST['password']);
    $db = ConnectDatabase();
    $stmt = $db->prepare("SELECT uuid FROM users WHERE username=:username AND password=:password");
    $stmt->bindValue(':username', $login);
    $stmt->bindValue(':password', $password);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $_SESSION['uuid'] = $result['uuid'];
        ob_clean();
        header("Location: /index.php");
        die();
    } else {
            $message = "Incorrect user or password.";
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
    <img src="static/welcome.jpg" id="icon"/>
    </div>

    <form method="POST">
        <center>
        <input required type="text" id="username" class="fadeIn second" name="username" placeholder="username">
        <input required type="password" id="password" class="fadeIn third" name="password" placeholder="password">
        <input type="submit" class="fadeIn bg-dark" value="Sign in">
    </form>

    <div id="formFooter">
        <a> 
            <?php 
                if (!empty($message)) {
                    echo $message;
                }
            ?> 
        </a>
    </div>
    <div id="formFooter">
        <a class="underlineHover" href="/register.php">Sign up</a>
      </div>
  </div>
</div>
