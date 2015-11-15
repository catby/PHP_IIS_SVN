<?php

/*
class UserAuth
{
	// コンストラクタ
	function __construct($dbFile);
	// デストラクタ
	function __destruct();
	// 新規登録
	function add($mail, $pass);
	// 削除
	function delete();
	// 認証
	function authentication($mail, $pass);
	// 認証確認
	function isAuth();
	// メール登録確認
	function isRegistered($mail);
	// マジックID設定
	function set_magicID($id);
	// マジックID取得
	function get_magicID();
	// メールアドレス取得
	function get_address();
	// ユーザー名変更
	function change_userName($name);
	// ユーザー名取得
	function get_userName();
	// パスワード変更
	function change_password($pass);
	// 所属変更
	function change_belong($belongID);
	// 所属取得
	function get_belong();
	// 所属リスト取得
	function get_belongList();
}
*/
class UserAuth
{
	private $m_pdo;
	private $m_auth;
	private $m_dbName;
	private $m_mail;
	private $m_name;

	// コンストラクタ
	function __construct($dbFile) {
		$this->m_pdo = new PDO("sqlite:".$dbFile);
		$this->m_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->m_auth = false;
	}

	// デストラクタ
	function __destruct() {
		unset($this->m_pdo);
	}

	// 新規登録
	function add($mail, $pass) {
		$mail = trim($mail);
		$pass = trim($pass);
		if(!strlen($mail) || !strlen($pass)) {
			return false;
		}
		$mail = strtolower($mail);
		$this->m_auth = false;
		$this->m_mail = "";
		$this->m_name = "";
		$rs = $this->m_pdo->query("SELECT * FROM user_info WHERE mail='".$mail."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($data)) {
			return false;
		}
		$this->m_pdo->exec("INSERT INTO user_info(mail, pass, magicID) ".
					"VALUES('$mail', '".sha1($pass, false)."', '".md5($mail, false)."')");
		$this->m_auth = true;
		$this->m_mail = $mail;
		return true;
	}

	// 削除
	function delete() {
		if(!$this->m_auth) {
			return false;
		}
		$this->m_pdo->exec("DELETE FROM user_info WHERE mail='".$this->m_mail."'");
		$this->m_auth = false;
		$this->m_mail = "";
		$this->m_name = "";
		return true;
	}

	// 認証
	function authentication($mail, $pass) {
		$mail = trim($mail);
		$pass = trim($pass);
		$mail = strtolower($mail);
		$this->m_auth = false;
		$this->m_mail = "";
		$this->m_name = "";
		$rs = $this->m_pdo->query("SELECT * FROM user_info WHERE mail='".$mail."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($data)) {
			if(sha1($pass, false) == $data[0]['pass']) {
				$this->m_auth = true;
				$this->m_mail = $mail;
				$this->m_name = mb_convert_encoding($data[0]['name'], "EUC-JP", "UTF-8");
			}
		}
		return $this->m_auth;
	}

	// 認証確認
	function isAuth() {
		if($this->m_auth) {
			return 2;
		}
		else if(isset($this->m_mail) && strlen($this->m_mail)) {
			return 1;
		}
		return 0;
	}

	// メール登録確認
	function isRegistered($mail) {
		$mail = trim($mail);
		$mail = strtolower($mail);
		$rs = $this->m_pdo->query("SELECT * FROM user_info WHERE mail='".$mail."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($data)) {
			return true;
		}
		return false;
	}

	// マジックID設定
	function set_magicID($id) {
		$ret = false;
		$this->m_auth = false;
		$this->m_mail = "";
		$this->m_name = "";
		$rs = $this->m_pdo->query("SELECT * FROM user_info WHERE magicID='".$id."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($data)) {
			$this->m_mail = $data[0]['mail'];
			$this->m_name = mb_convert_encoding($data[0]['name'], "EUC-JP", "UTF-8");
			$ret = true;
		}
		return $ret;
	}

	// マジックID取得
	function get_magicID() {
		$rs = $this->m_pdo->query("SELECT * FROM user_info WHERE mail='".$this->m_mail."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(!count($data)) {
			return false;
		}
		return $data[0]['magicID'];
	}

	// メールアドレス取得
	function get_address() {
		return $this->m_mail;
	}

	// ユーザー名変更
	function change_userName($name) {
		if(!$this->m_auth) {
			return false;
		}
		$this->m_name = $name;
		$name = mb_convert_encoding($name, "UTF-8", "EUC-JP");
		$this->m_pdo->exec("UPDATE user_info SET name='$name' WHERE mail='".$this->m_mail."'");
		return true;
	}

	// ユーザー名取得
	function get_userName() {
		return $this->m_name;
	}

	// パスワード変更
	function change_password($pass)
	{
		if(!$this->m_auth) {
			return false;
		}
		$this->m_pdo->exec("UPDATE user_info SET pass='".sha1($pass, false)."' WHERE mail='".$this->m_mail."'");
		return true;
	}

	// 所属変更
	function change_belong($belongID)
	{
		if(!$this->m_auth) {
			return false;
		}
		$this->m_pdo->exec("UPDATE user_info SET belong='$belongID' WHERE mail='".$this->m_mail."'");
		return true;
	}

	// 所属取得
	function get_belong()
	{
		$rs = $this->m_pdo->query("SELECT belong FROM user_info WHERE mail='".$this->m_mail."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(!count($data)) {
			echo "not entry";
			return false;
		}
		$rs = $this->m_pdo->query("SELECT * FROM belong WHERE id='".$data[0]['belong']."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(!count($data)) {
			return false;
		}
		return $data[0];
	}

	// 所属リスト取得
	function get_belongList()
	{
		$rs = $this->m_pdo->query("SELECT * FROM belong ORDER BY id");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		for($i = 0 ; $i < count($data) ; $i++) {
			$data[$i]['name'] = mb_convert_encoding($data[$i]['name'], "EUC-JP", "UTF-8");
		}
		return $data;
	}

}


function createAuthForm($actionURL) {
	echo "メールアドレス、パスワードを入力後、ログインボタンを押してください。<BR>\n";
	echo "<FORM ACTION=\"$actionURL\" METHOD=POST>\n";
	echo "<table class = \"Auth\">\n";
	echo "<TR><TH>メールアドレス</TH><TD><INPUT TYPE=TEXT NAME=MAIL SIZE=40 VALUE=\"" . (isset($_REQUEST['MAIL']) ? $_REQUEST['MAIL'] : "") . "\"></TD></TR>\n";
	echo "<TR><TH>パスワード</TH><TD><INPUT TYPE=PASSWORD NAME=PASS SIZE=25></TD></TR>\n";
	echo "</table>\n";
	echo "<P class=\"Auth\"><INPUT TYPE=SUBMIT VALUE=\"ログイン\"></P>\n";
	echo "</FORM>\n";
}

?>
