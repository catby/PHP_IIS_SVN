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
	// タイトルの作成
	echo "<A href=\"".$cache->get("referer")."\">[戻る]</A>\n";
	if(!$isDelete) {
		if($svn->isExist($target) == 1) {
			echo "<H1>[更新]";
		}
		else {
			echo "<H1>[登録]";
		}
	}
	else {
		echo "<H1>[削除]";
	}
	echo "$_SERVER[SERVER_NAME] - ".PARENT_DIR."/$target</H1>\n";
	echo "<hr>\n";
	
	echo "ようこそ<B>";
	$authCheck = $auth->isAuth();
	switch($authCheck) {
	case 0:
		echo "ゲスト";
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
	echo "</B>さん。";
	if($authCheck) {
		echo " <font style=\"font-size:10px\"><B>[<A href=\"".AUTHENTICATION."?logout\">ログアウト</A>]</B></font>";
	}
	echo "<BR>\n";
	
	
	//ユーザー認証
	if($auth->isAuth() == 0) {
		echo "ファイルの登録、更新、削除には、ログインが必要です。<BR>\n";
		createAuthForm(AUTHENTICATION);
		echo "登録情報の変更、パスワードを忘れた場合は<A href=\"".AUTHENTICATION."?edit\" TARGET=_BLANK>こちら</A>から。<BR>\n";
		echo "新規ユーザー登録は<A href=\"".AUTHENTICATION."?regist\">こちら</A>から。<BR>\n";
		echo "</BODY>\n";
		echo "</HTML>\n";
		return;
	}
	echo "<B>$name</B>さんで無い場合、<A href=\"".AUTHENTICATION."\">こちら</A>から再ログインしてください。<BR><BR>\n";
	
	$err_msg = "";
	if($isDelete) {
		// ファイル削除(Step.1)
		if(isset($_REQUEST['PASS'])) {
			if(!$auth->authentication($auth->get_address(), $_REQUEST['PASS'])) {
				$err_msg .= "パスワードが一致しません。<BR>";
			}
			
			if(strlen($err_msg) == 0) {
				$result = $svn->delFile($target, $_REQUEST['COMMENT']);
				if($result) {
					echo "以下のファイルを削除しました。<BR>\n";
					echo "http://$_SERVER[SERVER_NAME]/".PARENT_DIR."/$target<BR>\n";
					echo "<BR>\n";
					echo "<A href=\"".$cache->get("referer")."\">[戻る]</A>";
				}
				else {
					echo "<font color=#FF000000>予期しないエラーが発生し、ファイル削除に失敗しました。<BR>\n";
					echo "管理者に連絡してください。<BR></font>\n";
				}
				return;
			}
		}
		// ファイル削除(Step.1)
		displayTarget($svn, $target);
		echo "<font color=#FF0000>$err_msg</font>\n";
		echo "<FORM ACTION=\"$targetURL\" METHOD=POST>\n";
		echo "<table class = \"Auth\">\n";
		echo "<TR><TH nowrap>コメント</TH><TD><INPUT TYPE=TEXT NAME=COMMENT SIZE=80></TD></TR>\n";
		echo "<TR><TH nowrap>パスワード</TH><TD><INPUT TYPE=PASSWORD NAME=PASS SIZE=25></TD></TR>\n";
		echo "</table>\n";
		echo "<INPUT TYPE=SUBMIT VALUE=\"削除\">\n";
		echo "</FORM>\n";
		echo "<B>[注意]</B><BR>\n";
		echo "削除したファイルは、ブラウザ経由から操作できなくなります。<BR>\n";
		echo "今一度、間違い無いか確認をしてください。<BR>\n";
		return;
	}
	else {
		//新ファイル登録(登録Step.2)
		unset($err_msg);
		$err_msg = "";
		if(isset($_FILES['COMMIT_FILE'])) {
			if($svn->isExist($target) == 1) {
				$filename = $_FILES['COMMIT_FILE']['name'];
				$org_ext = substr($filename, strrpos($filename, ".")+1);
				$tgt_ext = substr($target,   strrpos($target, ".")+1);
				if($org_ext != $tgt_ext) {
					$err_msg .= "更新元ファイルと拡張子が異なります[now:$tgt_ext][new:$org_ext]<BR>";
				}
				else {
					$fullPath = $target;
					$exist = true;
				}
			}
			else {
				$filename = $_FILES['COMMIT_FILE']['name'];
				if(strlen($filename) != mb_strlen($filename, "EUC-JP")) {
					$err_msg .= "ファイル名に日本語等のマルチバイト文字は使用できません。<BR>";
				}
				if(mb_ereg("\s", $filename)) {
					$err_msg .= "ファイル名にスペースは使用できません。<BR>";
				}
				if(preg_match("/\/$/", $target)) {
					$fullPath = "$target$filename";
				}
				else {
					$fullPath = "$target/$filename";
				}
			}
			if(!$auth->authentication($auth->get_address(), $_REQUEST['PASS'])) {
				$err_msg .= "パスワードが一致しません。<BR>";
			}
			
			if(strlen($err_msg) == 0) {
				if(isset($exist)) {
					$result = $svn->updateUploadFile($fullPath, $_FILES['COMMIT_FILE']['tmp_name'], $_REQUEST['COMMENT']);
				}
				else {
					$result = $svn->addUploadFile($fullPath, $_FILES['COMMIT_FILE']['tmp_name'], $_REQUEST['COMMENT']);
				}
				if($result) {
					echo "<B><font color=#FF0000>本ページで、ブラウザの戻るボタンは絶対に押さないでください。<BR><A href=\"".$cache->get("referer")."\">[戻る]</A>をクリックするか、ブラウザを閉じてください。<BR><BR></font></B>\n";
					echo "以下のファイルを".(isset($exist) ? "更新" : "登録")."しました。<BR>\n";
					echo "<A href=\"/".PARENT_DIR."/$fullPath\" TARGET=_BLANK>";
					echo "http://$_SERVER[SERVER_NAME]/".PARENT_DIR."/$fullPath</A><BR>\n";
					echo "<BR>\n";
					echo "<A href=\"".$cache->get("referer")."\">[戻る]</A>";
					$_SESSION['SVN_registPath'] = "http://$_SERVER[SERVER_NAME]/".PARENT_DIR."/$fullPath";
				}
				else {
					echo "<font color=#FF000000>以下に何れかの理由により、ファイル登録に失敗しました。<BR>\n";
					echo "<font color=#FF000000>・既に同名のファイルが登録されている\n";
					echo "<A href=\"/".PARENT_DIR."/$fullPath@REV\" TARGET=_BLANK>";
					echo "(こちらから確認)</A><BR>\n";
					echo "<font color=#FF000000>・その他、予期せぬ問題が発生した<BR>\n";
					echo "ファイルの有無を確認し、それでも現象が改善しない場合は、管理者に連絡してください。<BR></font>\n";
				}
				return;
			}
		}
		
		//新ファイルデータ要求(登録Step.1)
		// 再ログイン案内
		$exist = displayTarget($svn, $target);
		echo "<FORM ACTION=\"$targetURL\" METHOD=POST ENCTYPE=\"multipart/form-data\">\n";
		echo ($exist ? "新しい" : "登録する")."ファイルを指定してください。<BR>\n";
		echo "<font color=#FF0000>$err_msg</font>\n";
		?>
<table class = "Auth">
<TR><TH nowrap>ファイル</TH><TD><INPUT TYPE=FILE NAME=COMMIT_FILE SIZE=80></TD></TR>
<TR><TH nowrap>コメント</TH><TD><INPUT TYPE=TEXT NAME=COMMENT SIZE=80></TD></TR>
<TR><TH nowrap>パスワード</TH><TD><INPUT TYPE=PASSWORD NAME=PASS SIZE=25></TD></TR>
</table>
<INPUT TYPE=SUBMIT VALUE="送信">
</FORM>
<?php
		
		if(!$exist) {
			echo "<B>[注意]</B><BR>\n";
			echo "新規登録の場合、指定されたファイル名がそのまま登録ファイル名になります。<BR>\n";
			echo "(格納フォルダは関係ありません。)<BR>\n";
			echo "日本語等のマルチバイト文字、スペースは使用できませんので、ご注意ください。<BR>\n";
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
		echo "<TR><TH nowrap>ファイル</TH><TD nowrap>/".PARENT_DIR."/$target</TD></TR>\n";
		echo "<TR><TH nowrap>リビジョン</TH><TD nowrap>".$info[0]['rev']."</TD></TR>\n";
		echo "<TR><TH nowrap>最終更新者</TH><TD nowrap>".$info[0]['author']."</TD></TR>\n";
		echo "<TR><TH nowrap>最終更新日</TH><TD nowrap>".$info[0]['date']."</TD></TR>\n";
		echo "</table>\n";
		echo "<BR>\n";
	}
	return $exist;
}

?>
