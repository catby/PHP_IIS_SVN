<?php
require_once "../PHP/userAuth.php";


function userLogin($auth) {
	if(isset($_REQUEST['MAIL']) && isset($_REQUEST['PASS'])) {
		// ǧ������
		if($auth->authentication($_REQUEST['MAIL'], $_REQUEST['PASS'])) {
			setcookie("auth_magicID", $auth->get_magicID(), time() + COOKIE_EXPIRE, "/");
			$url = $_SESSION['AuthOrigin'];
			unset($_SESSION['AuthOrigin']);
			header("Location: $url");
			return;
		}
		// ǧ�ڼ���(�᡼�륢�ɥ쥹��Ͽͭ��)
		else if($auth->isRegistered($_REQUEST['MAIL'])) {
			$errMsg = "�ѥ���ɤ��ְ�äƤ��ޤ���";
		}
		else {
			$errMsg = "��Ͽ����Ƥ��ʤ��᡼�륢�ɥ쥹�����ꤵ��ޤ�����";
		}
	}
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="auth.css" type="text/css"/>
  <title>���������</title>
</head>
<body>
<H1>�桼����ǧ��</H1>
<hr>
<?php
	if($errMsg) {
		echo "<font color=#FF0000>$errMsg</font><BR>\n";
	}
	createAuthForm($url);
	echo "��Ͽ������ѹ����ѥ���ɤ�˺�줿����<A href=\"$url?edit\" TARGET=_BLANK>������</A>���顣<BR>\n";
	echo "�����桼������Ͽ��<A href=\"$url?regist\">������</A>���顣<BR>\n";
	echo "</BODY>\n";
	echo "</HTML>\n";
}



?>
