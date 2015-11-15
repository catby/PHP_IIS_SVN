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
		userInfoEntry($auth, "?edit", "ユーザー情報更新", $info, true);
		print_editHeader();
		echo "<H1>ユーザー登録</H1>\n";
		echo "<hr>\n";
		echo "登録に成功しました。<BR>\n";
		return;
	}
	else if(isset($_REQUEST['MAIL']) && isset($_REQUEST['PASS'])) {
		// 認証成功
		if($auth->authentication($_REQUEST['MAIL'], $_REQUEST['PASS'])) {
			$info['MAIL']   = $auth->get_address();
			$info['NAME']   = $auth->get_userName();
			$belong       = $auth->get_belong();
			$info['BELONG'] = $belong['id'];
			$info['PASS'] = $_REQUEST['PASS'];
			userInfoEntry($auth, "?edit", "ユーザー情報更新", $info, true);
			return;
		}
		// 認証失敗(メールアドレス登録有り)
		else if($auth->isRegistered($_REQUEST['MAIL'])) {
			$errMsg = "パスワードが間違っています。";
		}
		else {
			$errMsg = "登録されていないメールアドレスが指定されました。";
		}
	}
	print_editHeader();
	echo "<H1>ユーザー認証</H1>\n";
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
  <title>登録情報更新</title>
</head>
<body>
<?php
}

?>
