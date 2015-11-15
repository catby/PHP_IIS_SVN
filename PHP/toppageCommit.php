<?php
require_once "toppageDB.php";
require_once "toppageCommon.php";


function commit($db) {
	// ページ情報の更新
	if($_REQUEST['cat'] == "Root") {
		$makeDay = mktime(0, 0, 0, $_REQUEST['month'], 1, $_REQUEST['year']);
		$db->beginTransaction();
		$db->set_pageInfo($_REQUEST['title'], $_REQUEST['comment'], $makeDay, $_REQUEST['mail'], isset($_REQUEST['useNewCat']) ? true : false);
		$catInfos = preg_split("/\r\n/", $_REQUEST['catInfo']);
		$catNum = 1;
		foreach($catInfos as $catInfo) {
			$data = explode("|", $catInfo);
			if(count($data) != 3) {
				continue;
			}
			$db->set_category($data[0], $catNum++);
			$db->set_catInfo($data[0], $data[1], $data[2]);
		}
		$db->endTransaction();
		header("Location: ".$_SERVER['PHP_SELF']); 
		return;
	}
	// 新規データ登録＆登録済みデータ情報の更新
	else {
		if(!isSVNFile($_REQUEST['path'])) {
			$dateStr = sprintf("%04d", $_REQUEST['year']).sprintf("%02d", $_REQUEST['month']).sprintf("%02d", $_REQUEST['day']);
		}
		$title = preg_replace("/\\\\\\\\/",  "\\",  $_REQUEST['title']);
		
		if(!isset($_REQUEST['child'])) {
			$db->set_item($_REQUEST['cat'], $title, $_REQUEST['author'], $dateStr, $_REQUEST['path'], $_REQUEST['num']);
		}
		else {
			$db->set_item($_REQUEST['cat'], $title, $_REQUEST['author'], $dateStr, $_REQUEST['path'], $_REQUEST['child'], $_REQUEST['num']);
		}
		header("Location: $_SERVER[PHP_SELF]?act=edit&cat=$_REQUEST[cat]"); 
	}
}

?>
