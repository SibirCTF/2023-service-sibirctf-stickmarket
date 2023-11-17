<?php

session_start();
if(empty($_SESSION["uuid"])) {
	header('Location: login.php');
	session_destroy();
	die();
}

?>
