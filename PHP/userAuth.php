<?php

/*
class UserAuth
{
	// ���󥹥ȥ饯��
	function __construct($dbFile);
	// �ǥ��ȥ饯��
	function __destruct();
	// ������Ͽ
	function add($mail, $pass);
	// ���
	function delete();
	// ǧ��
	function authentication($mail, $pass);
	// ǧ�ڳ�ǧ
	function isAuth();
	// �᡼����Ͽ��ǧ
	function isRegistered($mail);
	// �ޥ��å�ID����
	function set_magicID($id);
	// �ޥ��å�ID����
	function get_magicID();
	// �᡼�륢�ɥ쥹����
	function get_address();
	// �桼����̾�ѹ�
	function change_userName($name);
	// �桼����̾����
	function get_userName();
	// �ѥ�����ѹ�
	function change_password($pass);
	// ��°�ѹ�
	function change_belong($belongID);
	// ��°����
	function get_belong();
	// ��°�ꥹ�ȼ���
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

	// ���󥹥ȥ饯��
	function __construct($dbFile) {
		$this->m_pdo = new PDO("sqlite:".$dbFile);
		$this->m_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->m_auth = false;
	}

	// �ǥ��ȥ饯��
	function __destruct() {
		unset($this->m_pdo);
	}

	// ������Ͽ
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

	// ���
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

	// ǧ��
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

	// ǧ�ڳ�ǧ
	function isAuth() {
		if($this->m_auth) {
			return 2;
		}
		else if(isset($this->m_mail) && strlen($this->m_mail)) {
			return 1;
		}
		return 0;
	}

	// �᡼����Ͽ��ǧ
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

	// �ޥ��å�ID����
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

	// �ޥ��å�ID����
	function get_magicID() {
		$rs = $this->m_pdo->query("SELECT * FROM user_info WHERE mail='".$this->m_mail."'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(!count($data)) {
			return false;
		}
		return $data[0]['magicID'];
	}

	// �᡼�륢�ɥ쥹����
	function get_address() {
		return $this->m_mail;
	}

	// �桼����̾�ѹ�
	function change_userName($name) {
		if(!$this->m_auth) {
			return false;
		}
		$this->m_name = $name;
		$name = mb_convert_encoding($name, "UTF-8", "EUC-JP");
		$this->m_pdo->exec("UPDATE user_info SET name='$name' WHERE mail='".$this->m_mail."'");
		return true;
	}

	// �桼����̾����
	function get_userName() {
		return $this->m_name;
	}

	// �ѥ�����ѹ�
	function change_password($pass)
	{
		if(!$this->m_auth) {
			return false;
		}
		$this->m_pdo->exec("UPDATE user_info SET pass='".sha1($pass, false)."' WHERE mail='".$this->m_mail."'");
		return true;
	}

	// ��°�ѹ�
	function change_belong($belongID)
	{
		if(!$this->m_auth) {
			return false;
		}
		$this->m_pdo->exec("UPDATE user_info SET belong='$belongID' WHERE mail='".$this->m_mail."'");
		return true;
	}

	// ��°����
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

	// ��°�ꥹ�ȼ���
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
	echo "�᡼�륢�ɥ쥹���ѥ���ɤ����ϸ塢������ܥ���򲡤��Ƥ���������<BR>\n";
	echo "<FORM ACTION=\"$actionURL\" METHOD=POST>\n";
	echo "<table class = \"Auth\">\n";
	echo "<TR><TH>�᡼�륢�ɥ쥹</TH><TD><INPUT TYPE=TEXT NAME=MAIL SIZE=40 VALUE=\"" . (isset($_REQUEST['MAIL']) ? $_REQUEST['MAIL'] : "") . "\"></TD></TR>\n";
	echo "<TR><TH>�ѥ����</TH><TD><INPUT TYPE=PASSWORD NAME=PASS SIZE=25></TD></TR>\n";
	echo "</table>\n";
	echo "<P class=\"Auth\"><INPUT TYPE=SUBMIT VALUE=\"������\"></P>\n";
	echo "</FORM>\n";
}

?>
