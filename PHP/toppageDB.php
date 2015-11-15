<?php
require_once "SQLite_common.php";



/*
class toppageDB {
	// コンストラクタ
	function __construct($dbName);
	// デストラクタ
	function __destruct();
	// ページ情報の設定
	function set_pageInfo($title, $comment, $makeDay, $mail, $useNew);
	// ページ情報の取得[title, comment, makeDay, lastUpdate, mail, useNew]
	function get_pageInfo();
	// カテゴリの設定
	function set_category($title, $num);
	// カテゴリの削除
	function del_category($title);
	// カテゴリ情報の設定
	function set_catInfo($title, $jTitle, $comment);
	// カテゴリ情報の取得[Array[] | jTitle, comment]
	function get_catInfo($title=false);
	// カテゴリ内項目の設定
	function set_item($catTitle, $name, $author, $date, $path, $num, $parentNo=0);
	// カテゴリ内項目の削除
	function del_item($catTitle, $num, $parentNo=0);
	// カテゴリ内項目の取得[Array[num, name, author, date, path, haveChild]]
	function get_item($catTitle);
	// カテゴリ内詳細項目の取得[Array[num, name, author, date, path]]
	function get_itemDetail($catTitle, $num, $child=-1);
	// 新着情報の取得[Array[date, cat, name, kind]]
	function get_newItemList();
	// トランザクションを開始する
	function beginTransaction();
	// トランザクションを終了する
	function endTransaction();
}
*/
class toppageDB {
	private $m_pdo;
	private $m_isZero;
	private $m_autoTransaction;

	// コンストラクタ
	function __construct($dbName) {
		try {
			$this->m_pdo = new PDO("sqlite:".$dbName);
			$this->m_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if(!SQLiteCom::isExist($this->m_pdo, "sqlite_master", "name='master'", "name")) {
				$this->m_pdo->exec("CREATE TABLE master(number INTEGER NOT NULL, title TEXT NOT NULL, jTitle TEXT, comment TEXT, author TEXT, makeDay TEXT, lastUpdate TEXT)");
			}
			
			$this->m_isZero = (!SQLiteCom::isExist($this->m_pdo, "master", "number='0'", "number")) ? false : true;
		}
		catch(PDOException $err) {
			die("データベースへの接続を確立できませんでした。<BR>DBNAME:$dbName<BR>ERROR:".$err->getMessage()."<BR>");
		}
		$this->m_autoTransaction = false;
	}

	// デストラクタ
	function __destruct() {
		if($this->m_autoTransaction) {
			$this->m_pdo->rollBack();
		}
		unset($this->m_pdo);
	}

	// ページ情報の設定
	function set_pageInfo($title, $comment, $makeDay, $mail, $useNew) {
		
		if(!$this->m_autoTransaction) {
			$this->m_pdo->beginTransaction();
		}
		try {
			$title = SQLiteCom::excape_string($title, true);
			if(!$this->m_isZero) {
				$this->m_pdo->exec("INSERT INTO master(number, title) VALUES('0', '$title')");
				$this->m_isZero = true;
			}
			else {
				$this->m_pdo->exec("UPDATE master SET title='$title' WHERE number='0'");
			}
			if(strlen($comment)) {
				$comment = SQLiteCom::excape_string($comment, true);
				$this->m_pdo->exec("UPDATE master SET comment='$comment' WHERE number='0'");
			}
			if(strlen($makeDay)) {
				$makeDay = SQLiteCom::excape_string($makeDay);
				$this->m_pdo->exec("UPDATE master SET makeDay='$makeDay' WHERE number='0'");
			}
			if(strlen($mail)) {
				$mail = SQLiteCom::excape_string($mail);
				$this->m_pdo->exec("UPDATE master SET author='$mail' WHERE number='0'");
			}
			if($useNew && !$this->isNewCategory()) {
				$this->m_pdo->exec("CREATE TABLE newItemList(writeTime INTEGER NOT NULL, cat INTEGER NOT NULL, itemName TEXT NOT NULL, kind TEXT NOT NULL, num INTEGER NOT NULL, parent INTEGER)");
			}
			if(!$useNew && $this->isNewCategory()) {
				$this->m_pdo->exec("DROP TABLE newItemList");
			}
		}
		catch(PDOException $err) {
			$this->m_pdo->rollBack();
			die("ページ情報設定に失敗しました".$err->getMessage()."<BR>");
		}
		if(!$this->m_autoTransaction) {
			$this->m_pdo->commit();
		}
		return $ret;
	}

	// ページ情報の取得[title, comment, makeDay, lastUpdate, mail, useNew]
	function get_pageInfo() {
		if(!$this->m_isZero) {
			return false;
		}
		$rs = $this->m_pdo->query("SELECT * FROM master WHERE number='0'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		$ret['title']      = SQLiteCom::unexcape_string($data[0]['title'], true);
		$ret['comment']    = SQLiteCom::unexcape_string($data[0]['comment'], true);
		$ret['makeDay']    = SQLiteCom::unexcape_string($data[0]['makeDay']);
		$ret['lastUpdate'] = $data[0]['lastUpdate'];
		$ret['mail']       = SQLiteCom::unexcape_string($data[0]['author']);

		$ret['useNew'] = $this->isNewCategory();
		return $ret;
	}

	// カテゴリの設定
	function set_category($title, $num) {
		if(!$this->m_autoTransaction) {
			$this->m_pdo->beginTransaction();
		}

		$title = SQLiteCom::excape_string($title);
		if(!SQLiteCom::isExist($this->m_pdo, "master", "title='$title'")) {
			$this->m_pdo->exec("INSERT INTO master(number, title) VALUES('$num', '$title')");
			$this->m_pdo->exec("CREATE TABLE $title(number INTEGER NOT NULL, childNo INTEGER, haveChild INTEGER, name TEXT NOT NULL, author TEXT, date TEXT, path TEXT, writeTime INTEGER NOT NULL)");
		}
		else {
			$this->m_pdo->exec("UPDATE master SET number='$num', title='$title' WHERE title='$title'");
		}
		$this->set_lastUpdate();

		if(!$this->m_autoTransaction) {
			$this->m_pdo->commit();
		}
	}

	// カテゴリの削除
	function del_category($title) {
		$title = SQLiteCom::excape_string($title);
		$this->m_pdo->exec("DROP TABLE $title");
		$this->m_pdo->exec("DELETE FROM master WHERE title='$title'");
		$this->set_lastUpdate();
	}

	// カテゴリ情報の設定
	function set_catInfo($title, $jTitle, $comment) {
		$title = SQLiteCom::excape_string($title);
		if(!SQLiteCom::isExist($this->m_pdo, "master", "title='$title'")) {
			return false;
		}
		if(strlen($jTitle)) {
			$jTitle = SQLiteCom::excape_string($jTitle, true);
			$this->m_pdo->exec("UPDATE master SET jTitle='$jTitle' WHERE title='$title'");
		}

		if(strlen($comment)) {
			$comment = SQLiteCom::excape_string($comment, true);
			$this->m_pdo->exec("UPDATE master SET comment='$comment' WHERE title='$title'");
		}
		else {
			$this->m_pdo->exec("UPDATE master SET comment=NULL WHERE title='$title'");
		}
		$this->set_lastUpdate();
		return true;
	}

	// カテゴリ情報の取得[Array[] | jTitle, comment]
	function get_catInfo($title=false) {
		if($title) {
			$title = SQLiteCom::excape_string($title);
			$rs = $this->m_pdo->query("SELECT * FROM master WHERE title='$title'");
			$data = $rs->fetchAll(PDO::FETCH_ASSOC);
			$ret['jTitle']  = SQLiteCom::unexcape_string($data[0]['jTitle'], true);
			$ret['comment'] = SQLiteCom::unexcape_string($data[0]['comment'], true);
		}
		else {
			$ret = array();
			$rs = $this->m_pdo->query("SELECT title FROM master WHERE number > 0 ORDER BY number ASC");
			$data = $rs->fetchAll(PDO::FETCH_ASSOC);
			foreach($data as $item) {
				array_push($ret, SQLiteCom::unexcape_string($item['title']));
			}
		}
		return $ret;
	}

	// カテゴリ内項目の設定
	function set_item($catTitle, $name, $author, $date, $path, $num=0, $parentNo=0) {
		$catTitle = SQLiteCom::excape_string($catTitle);
		if(!SQLiteCom::isExist($this->m_pdo, "master", "title='$catTitle'", "title")) {
			return false;
		}
		$name   = SQLiteCom::excape_string($name, true);
		$author = SQLiteCom::excape_string($author, true);
		$path   = SQLiteCom::excape_string($path);
		$writeTime = time();
		
		// 新規登録(親)
		if(!$num && !$parentNo) {
			$rs = $this->m_pdo->query("SELECT number FROM $catTitle ORDER BY number DESC LIMIT 1");
			$data = $rs->fetchAll(PDO::FETCH_ASSOC);
			$num = $data[0]['number'] + 1;
			$this->m_pdo->exec("INSERT INTO $catTitle(number, childNo, haveChild, name, author, date, path, writeTime)".
						"VALUES($num, '0', '0', '$name', '$author', '$date', '$path', '$writeTime')");
			$kind="add";
		}
		// 新規登録(親→子)
		else if(!$num) {
			$rs = $this->m_pdo->query("SELECT childNo FROM $catTitle WHERE number='$parentNo' ".
									  "ORDER BY childNo DESC LIMIT 1");
			$data = $rs->fetchAll(PDO::FETCH_ASSOC);
			$childNo = $data[0]['childNo'] + 1;
			$this->m_pdo->exec("UPDATE $catTitle SET childNo='$childNo', haveChild='0' WHERE number='$parentNo' AND childNo='0'");
			$this->m_pdo->exec("INSERT INTO $catTitle(number, childNo, haveChild, name, author, date, path, writeTime)".
						"VALUES($parentNo, '0', '1', '$name', '$author', '$date', '$path', '$writeTime')");
			$kind="add";
			if($this->isNewCategory()) {
				if(SQLiteCom::isExist($this->m_pdo, "newItemList", "num='0' AND parent='$parentNo'")) {
					$this->m_pdo->exec("UPDATE newItemList SET num='$childNo', parent='$parentNo' WHERE num='0' AND parent='$parentNo'");
				}
			}

		}
		// データ更新(親)
		else if(!$parentNo) {
			if(!SQLiteCom::isExist($this->m_pdo, $catTitle, "number='$num' AND childNo='0'", "number")) {
				return false;
			}
			$this->m_pdo->exec("UPDATE $catTitle SET name='$name', author='$author', date='$date', path='$path', writeTime='$writeTime' ".
								"WHERE number='$num' AND childNo='0'");
			$kind="edit";
		}
		// データ更新(子)
		else {
			if(!SQLiteCom::isExist($this->m_pdo, $catTitle, "number='$parentNo' AND childNo='$num'", "number")) {
				return false;
			}
			$this->m_pdo->exec("UPDATE $catTitle SET name='$name', author='$author', date='$date', path='$path', writeTime='$writeTime' ".
								"WHERE number='$parentNo' AND childNo='$num'");
			$kind="edit";
		}
		$this->set_newItemList($catTitle, $num, $parentNo, $name, $kind);
		
		$this->set_lastUpdate();
		return true;
	}

	// カテゴリ内項目の削除
	function del_item($catTitle, $num, $parentNo=0) {
		if(!$parentNo) {
			if(!($line = SQLiteCom::isExist($this->m_pdo, $catTitle, "number='$num' AND childNo='0'", "name"))) {
				return false;
			}
			$this->m_pdo->exec("DELETE FROM $catTitle WHERE number='$num' AND childNo='0'");
			$delNo = $num;
			$delName = $line['name'];
		}
		else {
			if(!($line = SQLiteCom::isExist($this->m_pdo, $catTitle, "number='$parentNo' AND childNo='$num'", "name"))) {
				return false;
			}
			$this->m_pdo->exec("DELETE FROM $catTitle WHERE number='$parentNo' AND childNo='$num'");
			$delNo = $num;
			$delName = $line['name'];
		}

		$rs = $this->m_pdo->query("SELECT childNo FROM $catTitle WHERE number='$delNo' ".
								  "ORDER BY childNo DESC LIMIT 2");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($data)) {
			$childNo = $data[0]['childNo'];
			$haveChild = (count($data) > 1) ? 1 : 0;
			$this->m_pdo->exec("UPDATE $catTitle SET childNo=0, haveChild='$haveChild' ".
								"WHERE number='$delNo' AND childNo='$childNo'");
		}
		$this->set_newItemList($catTitle, $num, $parentNo, $delName, "delete");

		$this->set_lastUpdate();
		return true;
	}

	// カテゴリ内項目の取得[Array[num, name, author, date, path, haveChild, writeTime]]
	function get_item($catTitle) {
		$catTitle = SQLiteCom::excape_string($catTitle);
		$rs = $this->m_pdo->query("SELECT * FROM $catTitle WHERE childNo='0'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();
		foreach($data as $item) {
			$tmp['num']       = $item['number'];
			$tmp['name']      = SQLiteCom::unexcape_string($item['name'], true);
			$tmp['author']    = SQLiteCom::unexcape_string($item['author'], true);
			$tmp['date']      = $item['date'];
			$tmp['path']      = SQLiteCom::unexcape_string($item['path']);
			$tmp['haveChild'] = $item['haveChild'];
			$tmp['writeTime'] = $item['writeTime'];
			array_push($ret, $tmp);
		}
		return $ret;
	}

	// カテゴリ内詳細項目の取得[Array[num, name, author, date, path, writeTime]]
	function get_itemDetail($catTitle, $num, $child=-1) {
		$catTitle = SQLiteCom::excape_string($catTitle);
		if($child == -1) {
			$rs = $this->m_pdo->query("SELECT * FROM $catTitle WHERE number='$num' ORDER BY childNo DESC");
		}
		else {
			$rs = $this->m_pdo->query("SELECT * FROM $catTitle WHERE number='$num' AND childNo='$child' ORDER BY childNo DESC");
		}
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();
		foreach($data as $item) {
			$tmp['num']       = $item['childNo'];
			$tmp['name']      = SQLiteCom::unexcape_string($item['name'], true);
			$tmp['author']    = SQLiteCom::unexcape_string($item['author'], true);
			$tmp['date']      = $item['date'];
			$tmp['path']      = SQLiteCom::unexcape_string($item['path']);
			$tmp['writeTime'] = $item['writeTime'];
			array_push($ret, $tmp);
		}
		$tmp = array_pop($ret);
		array_unshift($ret, $tmp);
		return $ret;
	}

	// 新着情報の取得[Array[date, cat, name, kind]]
	function get_newItemList() {
		$ret = array();
		$rs = $this->m_pdo->query("SELECT * FROM newItemList ORDER BY writeTime DESC");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		foreach($data as $item) {
			$tmp['date'] = $item['writeTime'];
			$tmp['cat']  = $item['cat'];
			$tmp['name'] = SQLiteCom::unexcape_string($item['itemName'], true);
			$tmp['kind'] = $item['kind'];
			array_push($ret, $tmp);
		}
		return $ret;
	}

	// トランザクションを開始する
	function beginTransaction() {
		$this->m_autoTransaction = true;
		$this->m_pdo->beginTransaction();
	}

	// トランザクションを終了する
	function endTransaction() {
		$this->m_pdo->commit();
		$this->m_autoTransaction = false;
	}



	// 最終更新日を更新
	private function set_lastUpdate() {
		if(!$this->m_isZero) {
			return false;
		}
		$this->m_pdo->exec("UPDATE master SET lastUpdate='".time()."' WHERE number='0'");
		return true;
	}

	// 新着情報の有無チェック
	private function isNewCategory() {
		return (!SQLiteCom::isExist($this->m_pdo, "sqlite_master", "name='newItemList'", "name")) ? false : true;
	}
	
	// 新着情報に登録
	private function set_newItemList($cat, $num, $parentNo, $itemName, $kind) {
		if(!$this->isNewCategory()) {
			return;
		}
		if($kind == "edit") {
			$rs = $this->m_pdo->query("SELECT * FROM newItemList ORDER BY writeTime DESC LIMIT 1");
			$data = $rs->fetchAll(PDO::FETCH_ASSOC);
			if(count($data) && $num == $data[0]['num'] && $parentNo == $data[0]['parent'] && $data[0]['kind'] == "edit") {
				$this->m_pdo->exec("DELETE FROM newItemList WHERE num='$num' AND parent='$parentNo' AND writeTime='".$data[0]['writeTime']."'");
			}
		}
		$itemNames = explode("|", encoding_outputStr($itemName));
		if(count($itemNames) > 1) {
			$itemNames[0] = "$itemNames[0] ...etc";
		}

		$this->m_pdo->exec("INSERT INTO newItemList(writeTime, cat, itemName, kind, num, parent)".
						   "VALUES('".time()."', '$cat', '$itemNames[0]', '$kind', '$num', '$parentNo')");
		
		$rs = $this->m_pdo->query("SELECT writeTime FROM newItemList ORDER BY writeTime");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		for($i = 0 ; $i < (count($data) - $GLOBALS[newItemMax]) ; $i++) {
			$this->m_pdo->exec("DELETE FROM newItemList WHERE writeTime='".$data[$i]['writeTime']."'");
		}
	}
}

?>
