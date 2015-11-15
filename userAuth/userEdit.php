<?php
require_once "../PHP/userAuth.php";

function userEdit($auth) {
	if(isset($_REQUEST['old'])) {
		$auth->authentication($_REQUEST['MAIL'], $_REQUEST['old']);
		$info['reg']    = true;
		$info['MAIL']   = $_REQUEST['MAIL'];
		$info['PASS']   = $_REQUEST['PASS'];
		$info['NAME']   = $_REQUEST['NAME'];
		$info['BELONG'] = $_REQUEST['BELONG'];
		userInfoEntry($auth, "?edit", "�桼�������󹹿�", $info, true);
		print_editHeader();
		echo "<H1>�桼������Ͽ</H1>\n";
		echo "<hr>\n";
		echo "��Ͽ���������ޤ�����<BR>\n";
		return;
	}
	else if(isset($_REQUEST['MAIL']) && isset($_REQUEST['PASS'])) {
		// ǧ������
		if($auth->authentication($_REQUEST['MAIL'], $_REQUEST['PASS'])) {
			$info['MAIL']   = $auth->get_address();
			$info['NAME']   = $auth->get_userName();
			$belong       = $auth->get_belong();
			$info['BELONG'] = $belong['id'];
			$info['PASS'] = $_REQUEST['PASS'];
			userInfoEntry($auth, "?edit", "�桼�������󹹿�", $info, true);
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
	print_editHeader();
	echo "<H1>�桼����ǧ��</H1>\n";
	echo "<hr>\n";
	if($errMsg) {
		echo "<font color=#FF0000>$errMsg</font><BR>\n";
	}
	createAuthForm("$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]");
}


function print_editHeader() {
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="auth.css" type="text/css"/>
  <title>��Ͽ���󹹿�</title>
</head>
<body>
<?php
}

?>
