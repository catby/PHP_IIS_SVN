<?php

class SQLiteCom
{
	// SQL内に指定した情報があるか確認する
	static function isExist($pdo, $table, $where, $items="*") {
		$rs = $pdo->query("SELECT $items FROM $table WHERE $where");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		return count($data) ? $data[0] : false;
	}

	// SQL登録文字列のエスケープ処理
	static function excape_string($str, $isJ = false) {
		if($isJ) {
			$str = mb_convert_encoding($str, "UTF-8", "EUC-JP");
		}
		$str = preg_replace("/'/", "&60;", $str);
		return $str;
	}
	
	// SQL登録文字列のアンエスケープ処理
	static function unexcape_string($str, $isJ = false) {
		$str = preg_replace("/&60;/", "'", $str);
		if($isJ) {
			$str = mb_convert_encoding($str, "EUC-JP", "UTF-8");
		}
		return $str;
	}
}


?>
