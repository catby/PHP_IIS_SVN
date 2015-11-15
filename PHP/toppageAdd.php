<?php
require_once "toppageDB.php";
require_once "toppageCommon.php";
require_once "cacheIF.php";

function add($db) {
	$SVN_registPath = "";
	$catInfo = $db->get_catInfo($_REQUEST['cat']);
	if(isset($_REQUEST['SVN'])) {
		if(isset($_REQUEST['num'])) {
			$tmp = $db->get_itemDetail($_REQUEST['cat'], $_REQUEST['num'], 0);
			$target = $tmp[0];
		}
		session_set_cookie_params(60*60, "/");
		session_start();
		if(!isset($_SESSION['SVN_registPath'])) {
			$cache = new cacheIF("SVN", 0);
			$cache->set("referer", "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]");
			if(!isset($target['path'])) {
				$commitPath = substr($_SERVER['PHP_SELF'], 1, strrpos($_SERVER['PHP_SELF'], "/")).$_REQUEST['cat'];
			}
			else {
				$commitPath = preg_replace("/^\/SVN\//", "", $target['path']);
			}
			header("Location: /SVN/svn_mgr.php?commit=$commitPath&cache=".$cache->getID());
			unset($cache);
			return;
		}
		$compTarget = "http://$_SERVER[SERVER_NAME]";
		if(substr_compare($_SESSION['SVN_registPath'], $compTarget, 0, strlen($compTarget), TRUE) == 0) {
			$SVN_registPath = substr($_SESSION['SVN_registPath'], strlen($compTarget));
		}
		else {
			$SVN_registPath = $_SESSION['SVN_registPath'];
		}
		unset($_SESSION['SVN_registPath']);
	}
	printHeader($db, "<<Add>> ");
	echo "<H1>\"$catInfo[jTitle]\"項目追加</H1>\n";
	// 新規
	if(!isset($_REQUEST['num'])) {
		echo "以下に入力されたファイルを登録します。<BR>\n";
		$target['date'] = date("Y").date("m").date("d");
		$target['path'] = $SVN_registPath;
		echo "<FORM ACTION=\"$_SERVER[PHP_SELF]?act=commit&cat=$_REQUEST[cat]\" METHOD=\"POST\">\n";
		echo "<INPUT TYPE=\"submit\" VALUE=\"更新\"> <INPUT TYPE=\"reset\" VALUE=\"クリア\"><BR>\n";
		printItemEntry($target);
		echo "</FORM>\n";
	}
	// 更新
	else {
		$target['date'] = date("Y").date("m").date("d");
		if(isset($_REQUEST['SVN'])) {
			echo "SVN管理のファイルでは、タイトル、作者の情報のみ修正する事が可能です。<BR>\n";
			echo "履歴情報は、SVNのコメントとしてのみ記録されます。<BR>\n";
			echo "<FORM ACTION=\"$_SERVER[PHP_SELF]?act=commit&cat=$_REQUEST[cat]&num=$_REQUEST[num]\" METHOD=\"POST\">\n";
		}
		else {
			echo "以下の項目群の最新のファイルとして以下に入力されたファイルを登録します。<BR>\n";
			echo "登録したファイルは、項目群の一番上に表示されます。<BR>\n";
			echo "<FORM ACTION=\"$_SERVER[PHP_SELF]?act=commit&cat=$_REQUEST[cat]&child=0&num=$_REQUEST[num]\" METHOD=\"POST\">\n";
		}
		echo "<INPUT TYPE=\"submit\" VALUE=\"更新\"> <INPUT TYPE=\"reset\" VALUE=\"クリア\"><BR>\n";
		printItemEntry($target);
		echo "</FORM>\n";
		echo "<HR>\n";
		echo "<B>[更新する項目群]</B>";
		$items = $db->get_itemDetail($_REQUEST['cat'], $_REQUEST['num']);
		sort_margeSVN($items);
		printItemList($items, $_REQUEST['cat']);
	}
}


?>
