<?php

class SQLiteCom
{
	// SQL��˻��ꤷ�����󤬤��뤫��ǧ����
	static function isExist($pdo, $table, $where, $items="*") {
		$rs = $pdo->query("SELECT $items FROM $table WHERE $where");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		return count($data) ? $data[0] : false;
	}

	// SQL��Ͽʸ����Υ��������׽���
	static function excape_string($str, $isJ = false) {
		if($isJ) {
			$str = mb_convert_encoding($str, "UTF-8", "EUC-JP");
		}
		$str = preg_replace("/'/", "&60;", $str);
		return $str;
	}
	
	// SQL��Ͽʸ����Υ��󥨥������׽���
	static function unexcape_string($str, $isJ = false) {
		$str = preg_replace("/&60;/", "'", $str);
		if($isJ) {
			$str = mb_convert_encoding($str, "EUC-JP", "UTF-8");
		}
		return $str;
	}
}


?>
