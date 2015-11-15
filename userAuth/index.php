<?php
define("COOKIE_EXPIRE", 60*60*24*30);

require_once "userRegist.php";
require_once "userLogin.php";
require_once "userEdit.php";
require_once "../PHP/userAuth.php";


session_set_cookie_params(60*60, "/");
session_start();
setcookie("auth_magicID", "", time(), "/");

if(isset($_REQUEST['logout'])) {
	$url = $_SESSION['AuthOrigin'];
	unset($_SESSION['AuthOrigin']);
	header("Location: $url"); 
	exit();
}

$auth = new UserAuth("C:\\inetpub\\wwwroot\\userInfo\\user.db");

if(isset($_REQUEST['regist'])) {
	userRegistration($auth);
}
else if(isset($_REQUEST['edit'])) {
	userEdit($auth);
}
else {
	userLogin($auth);
}
unset($auth);


?>
