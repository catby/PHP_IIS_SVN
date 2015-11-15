<?php
require_once "../PHP/userAuth.php";
require_once "userAuth_common.php";


function userRegistration($auth) {
	if(isset($_REQUEST['MAIL']) || (isset($_REQUEST['PASS']) && strlen($_REQUEST['PASS']))) {
		$info['reg'] = true;
	}
	$info['MAIL']   = (isset($_REQUEST['MAIL']) ? $_REQUEST['MAIL'] : "");
	$info['PASS']   = (isset($_REQUEST['PASS']) ? $_REQUEST['PASS'] : "");
	$info['NAME']   = (isset($_REQUEST['NAME']) ? $_REQUEST['NAME'] : "");
	$info['BELONG'] = (isset($_REQUEST['BELONG']) ? $_REQUEST['BELONG'] : "");
	if(userInfoEntry($auth, "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]", "ユーザー登録", $info)) {
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="auth.css" type="text/css"/>
  <title>ユーザー登録</title>
</head>
<body>
<H1>ユーザー登録</H1>
<hr>
登録に成功しました。<BR>
<A href="<?= $_SESSION['AuthOrigin'] ?>">[戻る]</A>
<?php
	}
}


?>
