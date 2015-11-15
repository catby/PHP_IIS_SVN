<?php
define("AUTH_CSS", "/userAuth/auth.css");
define("AUTHENTICATION", "/userAuth/index.php");

require_once "../PHP/svn.php";
require_once "../PHP/cacheIF.php";


function svn_commit($svn, $auth)
{
	if($_REQUEST['cache'] == "1") {
		$cache = new cacheIF("SVN", 0);
		$cache->set("referer", $_SERVER['HTTP_REFERER']);
		$tmpData = explode("&", $_SERVER['QUERY_STRING']);
		for($i = 0 ; $i < count($tmpData) ; $i++) {
			list($key, $value) = explode("=", $tmpData[$i]);
			if($key == "cache") {
				$tmpData[$i] = "$key=" . $cache->getID();
			}
		}
		$targetURL = "$_SERVER[PHP_SELF]?".implode("&", $tmpData);
		unset($tmpData);
	}
	else {
		$cache = new cacheIF("SVN", $_REQUEST['cache']);
		$targetURL = "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]";
	}

	if(isset($_REQUEST['commit'])) {
		$target = $_REQUEST['commit'];
		$isDelete = false;
	}
	else {
		$target = $_REQUEST['delete'];
		$isDelete = true;
	}

	session_set_cookie_params(60*60, "/");
	session_start();
	$_SESSION['AuthOrigin'] = $targetURL;

	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Content-Type: text/html; charset=ECU-JP");
	?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="/<?= PARENT_DIR ?>/browser.css" type="text/css"/>
  <link rel="stylesheet" href="<?= AUTH_CSS ?>" type="text/css"/>
  <title><?= "[Commit] - /".PARENT_DIR."/".$target ?></title>
</head>
<body>
<?php
	// �����ȥ�κ���
	echo "<A href=\"".$cache->get("referer")."\">[���]</A>\n";
	if(!$isDelete) {
		if($svn->isExist($target) == 1) {
			echo "<H1>[����]";
		}
		else {
			echo "<H1>[��Ͽ]";
		}
	}
	else {
		echo "<H1>[���]";
	}
	echo "$_SERVER[SERVER_NAME] - ".PARENT_DIR."/$target</H1>\n";
	echo "<hr>\n";
	
	echo "�褦����<B>";
	$authCheck = $auth->isAuth();
	switch($authCheck) {
	case 0:
		echo "������";
		break;
	case 1:
	case 2:
		$name = $auth->get_userName();
		if(!strlen($name)) {
			$name = $auth->get_address();
			$name = substr($name, 0, strpos($name, '@'));
		}
		echo $name;
		break;
	}
	echo "</B>����";
	if($authCheck) {
		echo " <font style=\"font-size:10px\"><B>[<A href=\"".AUTHENTICATION."?logout\">��������</A>]</B></font>";
	}
	echo "<BR>\n";
	
	
	//�桼����ǧ��
	if($auth->isAuth() == 0) {
		echo "�ե��������Ͽ������������ˤϡ�������ɬ�פǤ���<BR>\n";
		createAuthForm(AUTHENTICATION);
		echo "��Ͽ������ѹ����ѥ���ɤ�˺�줿����<A href=\"".AUTHENTICATION."?edit\" TARGET=_BLANK>������</A>���顣<BR>\n";
		echo "�����桼������Ͽ��<A href=\"".AUTHENTICATION."?regist\">������</A>���顣<BR>\n";
		echo "</BODY>\n";
		echo "</HTML>\n";
		return;
	}
	echo "<B>$name</B>�����̵����硢<A href=\"".AUTHENTICATION."\">������</A>����ƥ����󤷤Ƥ���������<BR><BR>\n";
	
	$err_msg = "";
	if($isDelete) {
		// �ե�������(Step.1)
		if(isset($_REQUEST['PASS'])) {
			if(!$auth->authentication($auth->get_address(), $_REQUEST['PASS'])) {
				$err_msg .= "�ѥ���ɤ����פ��ޤ���<BR>";
			}
			
			if(strlen($err_msg) == 0) {
				$result = $svn->delFile($target, $_REQUEST['COMMENT']);
				if($result) {
					echo "�ʲ��Υե�����������ޤ�����<BR>\n";
					echo "http://$_SERVER[SERVER_NAME]/".PARENT_DIR."/$target<BR>\n";
					echo "<BR>\n";
					echo "<A href=\"".$cache->get("referer")."\">[���]</A>";
				}
				else {
					echo "<font color=#FF000000>ͽ�����ʤ����顼��ȯ�������ե��������˼��Ԥ��ޤ�����<BR>\n";
					echo "�����Ԥ�Ϣ���Ƥ���������<BR></font>\n";
				}
				return;
			}
		}
		// �ե�������(Step.1)
		displayTarget($svn, $target);
		echo "<font color=#FF0000>$err_msg</font>\n";
		echo "<FORM ACTION=\"$targetURL\" METHOD=POST>\n";
		echo "<table class = \"Auth\">\n";
		echo "<TR><TH nowrap>������</TH><TD><INPUT TYPE=TEXT NAME=COMMENT SIZE=80></TD></TR>\n";
		echo "<TR><TH nowrap>�ѥ����</TH><TD><INPUT TYPE=PASSWORD NAME=PASS SIZE=25></TD></TR>\n";
		echo "</table>\n";
		echo "<INPUT TYPE=SUBMIT VALUE=\"���\">\n";
		echo "</FORM>\n";
		echo "<B>[���]</B><BR>\n";
		echo "��������ե�����ϡ��֥饦����ͳ�������Ǥ��ʤ��ʤ�ޤ���<BR>\n";
		echo "�����١��ְ㤤̵������ǧ�򤷤Ƥ���������<BR>\n";
		return;
	}
	else {
		//���ե�������Ͽ(��ϿStep.2)
		unset($err_msg);
		$err_msg = "";
		if(isset($_FILES['COMMIT_FILE'])) {
			if($svn->isExist($target) == 1) {
				$filename = $_FILES['COMMIT_FILE']['name'];
				$org_ext = substr($filename, strrpos($filename, ".")+1);
				$tgt_ext = substr($target,   strrpos($target, ".")+1);
				if($org_ext != $tgt_ext) {
					$err_msg .= "�������ե�����ȳ�ĥ�Ҥ��ۤʤ�ޤ�[now:$tgt_ext][new:$org_ext]<BR>";
				}
				else {
					$fullPath = $target;
					$exist = true;
				}
			}
			else {
				$filename = $_FILES['COMMIT_FILE']['name'];
				if(strlen($filename) != mb_strlen($filename, "EUC-JP")) {
					$err_msg .= "�ե�����̾�����ܸ����Υޥ���Х���ʸ���ϻ��ѤǤ��ޤ���<BR>";
				}
				if(mb_ereg("\s", $filename)) {
					$err_msg .= "�ե�����̾�˥��ڡ����ϻ��ѤǤ��ޤ���<BR>";
				}
				if(preg_match("/\/$/", $target)) {
					$fullPath = "$target$filename";
				}
				else {
					$fullPath = "$target/$filename";
				}
			}
			if(!$auth->authentication($auth->get_address(), $_REQUEST['PASS'])) {
				$err_msg .= "�ѥ���ɤ����פ��ޤ���<BR>";
			}
			
			if(strlen($err_msg) == 0) {
				if(isset($exist)) {
					$result = $svn->updateUploadFile($fullPath, $_FILES['COMMIT_FILE']['tmp_name'], $_REQUEST['COMMENT']);
				}
				else {
					$result = $svn->addUploadFile($fullPath, $_FILES['COMMIT_FILE']['tmp_name'], $_REQUEST['COMMENT']);
				}
				if($result) {
					echo "<B><font color=#FF0000>�ܥڡ����ǡ��֥饦�������ܥ�������Ф˲����ʤ��Ǥ���������<BR><A href=\"".$cache->get("referer")."\">[���]</A>�򥯥�å����뤫���֥饦�����Ĥ��Ƥ���������<BR><BR></font></B>\n";
					echo "�ʲ��Υե������".(isset($exist) ? "����" : "��Ͽ")."���ޤ�����<BR>\n";
					echo "<A href=\"/".PARENT_DIR."/$fullPath\" TARGET=_BLANK>";
					echo "http://$_SERVER[SERVER_NAME]/".PARENT_DIR."/$fullPath</A><BR>\n";
					echo "<BR>\n";
					echo "<A href=\"".$cache->get("referer")."\">[���]</A>";
					$_SESSION['SVN_registPath'] = "http://$_SERVER[SERVER_NAME]/".PARENT_DIR."/$fullPath";
				}
				else {
					echo "<font color=#FF000000>�ʲ��˲��줫����ͳ�ˤ�ꡢ�ե�������Ͽ�˼��Ԥ��ޤ�����<BR>\n";
					echo "<font color=#FF000000>������Ʊ̾�Υե����뤬��Ͽ����Ƥ���\n";
					echo "<A href=\"/".PARENT_DIR."/$fullPath@REV\" TARGET=_BLANK>";
					echo "(�����餫���ǧ)</A><BR>\n";
					echo "<font color=#FF000000>������¾��ͽ���������꤬ȯ������<BR>\n";
					echo "�ե������̵ͭ���ǧ��������Ǥ⸽�ݤ��������ʤ����ϡ������Ԥ�Ϣ���Ƥ���������<BR></font>\n";
				}
				return;
			}
		}
		
		//���ե�����ǡ����׵�(��ϿStep.1)
		// �ƥ��������
		$exist = displayTarget($svn, $target);
		echo "<FORM ACTION=\"$targetURL\" METHOD=POST ENCTYPE=\"multipart/form-data\">\n";
		echo ($exist ? "������" : "��Ͽ����")."�ե��������ꤷ�Ƥ���������<BR>\n";
		echo "<font color=#FF0000>$err_msg</font>\n";
		?>
<table class = "Auth">
<TR><TH nowrap>�ե�����</TH><TD><INPUT TYPE=FILE NAME=COMMIT_FILE SIZE=80></TD></TR>
<TR><TH nowrap>������</TH><TD><INPUT TYPE=TEXT NAME=COMMENT SIZE=80></TD></TR>
<TR><TH nowrap>�ѥ����</TH><TD><INPUT TYPE=PASSWORD NAME=PASS SIZE=25></TD></TR>
</table>
<INPUT TYPE=SUBMIT VALUE="����">
</FORM>
<?php
		
		if(!$exist) {
			echo "<B>[���]</B><BR>\n";
			echo "������Ͽ�ξ�硢���ꤵ�줿�ե�����̾�����Τޤ���Ͽ�ե�����̾�ˤʤ�ޤ���<BR>\n";
			echo "(��Ǽ�ե�����ϴط�����ޤ���)<BR>\n";
			echo "���ܸ����Υޥ���Х���ʸ�������ڡ����ϻ��ѤǤ��ޤ���Τǡ�����դ���������<BR>\n";
		}
	}
	echo "</BODY>";
	echo "</HTML>";
}


function displayTarget($svn, $target) {
	$exist = false;
	if($svn->isExist($target) == 1) {
		$exist = true;
		$info = $svn->getFileRev($target, 1);
		echo "<table class = \"Auth\">\n";
		echo "<TR><TH nowrap>�ե�����</TH><TD nowrap>/".PARENT_DIR."/$target</TD></TR>\n";
		echo "<TR><TH nowrap>��ӥ����</TH><TD nowrap>".$info[0]['rev']."</TD></TR>\n";
		echo "<TR><TH nowrap>�ǽ�������</TH><TD nowrap>".$info[0]['author']."</TD></TR>\n";
		echo "<TR><TH nowrap>�ǽ�������</TH><TD nowrap>".$info[0]['date']."</TD></TR>\n";
		echo "</table>\n";
		echo "<BR>\n";
	}
	return $exist;
}

?>
