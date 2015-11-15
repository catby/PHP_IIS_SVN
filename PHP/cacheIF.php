<?php
require_once("Cache/Lite.php");


/*
class cacheIF
{
	// �R���X�g���N�^
	function __construct($mnemonic, $id = 0);
	// �f�X�g���N�^
	function __destruct();
	// �L���b�V���ɐݒ肷��
	function set($key, $value);
	// �L���b�V������擾����
	function get($key);
	// �L���b�V�����N���A����
	function clear();
	// ���ʎq���擾
	function getID();
}
*/
class cacheIF
{
	private $m_hCache;
	private $m_id;
	private $mnemonic;

	// �R���X�g���N�^
	function __construct($mnemonic, $id = 0) {
		if(!$id) {
			$id = $this->make_cacheID();
		}
		$this->m_id     = "$id";
		$this->mnemonic = "$mnemonic$id";
		
		$param = array(
			"cacheDir"               => "C:/tmp/",
			"lifeTime"               => "3600",
			"automaticCleaningFactor"=> "20",
		);
		$this->m_hCache = new Cache_Lite($param);
	}
	
	// �f�X�g���N�^
	function __destruct() {
		unset($this->m_hCache);
	}

	// �L���b�V���ɐݒ肷��
	function set($key, $value) {
		return $this->m_hCache->save($value, $key, $this->mnemonic);
	}
	
	// �L���b�V������擾����
	function get($key) {
		return $this->m_hCache->get($key, $this->mnemonic);
	}
	
	// �L���b�V�����N���A����
	function clear() {
		$this->m_hCache->clean($this->mnemonic);
	}

	// ���ʎq���擾
	function getID() {
		return $this->m_id;
	}


	// �L���b�V��ID�𐶐�����
	private function make_cacheID() {
		$ipAddr = explode(".", $_SERVER['REMOTE_ADDR']);
		$id = mt_rand(10000, 30000);
		for($i = 0 ; $i < count($ipAddr) ; $i++) {
			$id = $id . sprintf("%02X", $ipAddr[$i]);
		}
		return $id;
	}
}

?>
