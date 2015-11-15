<?php
require_once "../PHP/userAuth.php";


function userLogin($auth) {
	if(isset($_REQUEST['MAIL']) && isset($_REQUEST['PASS'])) {
		// 認証成功
		if($auth->authentication($_REQUEST['MAIL'], $_REQUEST['PASS'])) {
			setcookie("auth_magicID", $auth->get_magicID(), time() + COOKIE_EXPIRE, "/");
			$url = $_SESSION['AuthOrigin'];
			unset($_SESSION['AuthOrigin']);
			header("Location: $url");
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
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="auth.css" type="text/css"/>
  <title>ログイン画面</title>
</head>
<body>
<H1>ユーザー認証</H1>
<hr>
<?php
	if($errMsg) {
		echo "<font color=#FF0000>$errMsg</font><BR>\n";
	}
	createAuthForm($url);
	echo "登録情報の変更、パスワードを忘れた場合は<A href=\"$url?edit\" TARGET=_BLANK>こちら</A>から。<BR>\n";
	echo "新規ユーザー登録は<A href=\"$url?regist\">こちら</A>から。<BR>\n";
	echo "</BODY>\n";
	echo "</HTML>\n";
}



?>
