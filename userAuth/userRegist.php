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
	if(userInfoEntry($auth, "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]", "�桼������Ͽ", $info)) {
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="auth.css" type="text/css"/>
  <title>�桼������Ͽ</title>
</head>
<body>
<H1>�桼������Ͽ</H1>
<hr>
��Ͽ���������ޤ�����<BR>
<A href="<?= $_SESSION['AuthOrigin'] ?>">[���]</A>
<?php
	}
}


?>
