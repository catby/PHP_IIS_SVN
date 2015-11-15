<?php

function userInfoEntry($auth, $action, $title, $info, $editForm=false) {
	$errMsg = "";
	$hiddenOpt = "";
	$mailOpt = "";
	if(isset($info['reg']) && $info['reg']) {
		$info['MAIL'] = trim($info['MAIL']);
		$info['PASS'] = trim($info['PASS']);
		if(!strlen($info['MAIL'])) {
			$errMsg .= "�᡼�륢�ɥ쥹�����Ϥ���Ƥ��ޤ���<BR>";
		}
		if(strlen($info['MAIL']) != mb_strlen($info['MAIL'], "EUC-JP")) {
			$errMsg .= "�᡼�륢�ɥ쥹�����Ѥ��ޤޤ�Ƥ��ޤ���<BR>";
		}
		if(!preg_match("/\S@\S/", $info['MAIL'])) {
			$errMsg .= "�᡼�륢�ɥ쥹���᡼��η����������Ƥ��ޤ���<BR>";
		}
		if(!$editForm && $auth->isRegistered($info['MAIL'])) {
			$errMsg .= "���Υ᡼�륢�ɥ쥹�ϴ�����Ͽ����Ƥ��ޤ���<BR>";
		}
		if(!strlen($info['PASS'])) {
			$errMsg .= "�ѥ���ɤ����Ϥ���Ƥ��ޤ���<BR>";
		}
		
		if(!$errMsg) {
			if(!$editForm) {
				$auth->add($info['MAIL'], $info['PASS']);
			}
			else {
				$auth->change_password($info['PASS']);
			}
			$auth->change_userName($info['NAME']);
			$auth->change_belong($info['BELONG']);
			setcookie("auth_magicID", $auth->get_magicID(), time() + COOKIE_EXPIRE, "/");
			return true;
		}
	}
	
	if($editForm) {
		$mailOpt   = "READONLY STYLE=background-color:#D0D0D0;";
		$hiddenOpt = "<INPUT TYPE=HIDDEN NAME=old VALUE=\"$info[PASS]\">\n";
	}
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="auth.css" type="text/css"/>
  <title><?= $title ?></title>
</head>
<body>
<A href="<?= $_SESSION['AuthOrigin'] ?>">[���]</A>
<H1><?= $title ?></H1>
<hr>
<font color=#FF0000><?= $errMsg ?></font>
�᡼�륢�ɥ쥹���ѥ���ɡ�̾������°�����Ϥ�����Ͽ�ܥ���򲡤��Ƥ���������<BR>
̾��������ʸ������Ѳ�ǽ�Ǥ���<BR>
<FORM ACTION="<?= $action ?>" METHOD=POST>
<table class = "Auth">
<TR><TH>�᡼�륢�ɥ쥹</TH><TD><INPUT TYPE=TEXT NAME=MAIL SIZE=40 VALUE="<?= $info['MAIL'] ?>"<?= $mailOpt ?>></TD></TR>
<TR><TH>�ѥ����</TH><TD><INPUT TYPE=PASSWORD NAME=PASS SIZE=25></TD></TR>
<TR><TH>̾��</TH><TD><INPUT TYPE=TEXT NAME=NAME SIZE=40 VALUE="<?= $info['NAME'] ?>"></TD></TR>
<TR><TH>��°</TH><TD>
<SELECT NAME=BELONG><?= create_belongList($auth, $info['BELONG']) ?></SELECT>
</TD></TR>
</table>
<INPUT TYPE=SUBMIT VALUE="��Ͽ">
<?= $hiddenOpt ?>
</FORM>
</BODY>
</HTML>
<?php

	return false;
}

// ��°�ꥹ�Ȥ���������
function create_belongList($auth, $choise) {
	$belongList = $auth->get_belongList();
	foreach($belongList as $belong) {
		$retStr .= "<OPTION VALUE=$belong[id]".(($belong['id'] == $choise) ? " SELECTED" : "").">$belong[name]\n";
	}
	return $retStr;
}




?>
