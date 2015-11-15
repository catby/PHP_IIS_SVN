<?php
require_once("Cache/Lite.php");


/*
class cacheIF
{
	// コンストラクタ
	function __construct($mnemonic, $id = 0);
	// デストラクタ
	function __destruct();
	// キャッシュに設定する
	function set($key, $value);
	// キャッシュから取得する
	function get($key);
	// キャッシュをクリアする
	function clear();
	// 識別子を取得
	function getID();
}
*/
class cacheIF
{
	private $m_hCache;
	private $m_id;
	private $mnemonic;

	// コンストラクタ
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
	
	// デストラクタ
	function __destruct() {
		unset($this->m_hCache);
	}

	// キャッシュに設定する
	function set($key, $value) {
		return $this->m_hCache->save($value, $key, $this->mnemonic);
	}
	
	// キャッシュから取得する
	function get($key) {
		return $this->m_hCache->get($key, $this->mnemonic);
	}
	
	// キャッシュをクリアする
	function clear() {
		$this->m_hCache->clean($this->mnemonic);
	}

	// 識別子を取得
	function getID() {
		return $this->m_id;
	}


	// キャッシュIDを生成する
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
