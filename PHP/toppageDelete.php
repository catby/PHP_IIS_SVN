<?php
require_once "toppageDB.php";
require_once "toppageCommon.php";

function delete($db) {
	$catInfo = $db->get_catInfo($_REQUEST['cat']);
	if(!isset($_REQUEST['num'])) {
		printHeader($db, "<<Delete>> ");
		echo "<H1>\"$catInfo[jTitle]\"削除</H1>\n";
		echo "現在、この作業を行う事は出来ません。";
		return;
	}
	else {
		if(!isset($_REQUEST['confirmed'])) {
			printHeader($db, "<<Delete>> ");
			echo "<H1>\"$catInfo[jTitle]\"項目削除</H1>\n";
			echo "以下の項目の削除を実施します。<BR>\n";
			echo "よろしいですか？<BR>\n";
			if(!isset($_REQUEST['parent'])) {
				$tmp = $db->get_itemDetail($_REQUEST['cat'], $_REQUEST['num'], 0);
				$formURL = "$_SERVER[PHP_SELF]?act=del&cat=$_REQUEST[cat]&num=$_REQUEST[num]";
			}
			else {
				$tmp = $db->get_itemDetail($_REQUEST['cat'], $_REQUEST[parent], $_REQUEST['num']);
				$formURL = "$_SERVER[PHP_SELF]?act=del&cat=$_REQUEST[cat]&num=$_REQUEST[num]&parent=$_REQUEST[parent]";
			}
			$item = $tmp[0];
			printItemEntry($item, true);
			echo "<FORM  ACTION=\"$formURL\" METHOD=\"POST\" STYLE=\"margin:0;margin-bottom:10px\">";
			echo "<INPUT TYPE=\"SUBMIT\" NAME=\"confirmed\" VALUE=\"YES\"> ";
			echo "<INPUT TYPE=\"SUBMIT\" NAME=\"confirmed\" VALUE=\"NO\">";
			echo "</FORM>";
			if(isSVNFile($item['path'])) {
				echo "<TABLE><TR><TD valign=top nowrap><B>※</B></TD><TD nowrap>\n";
				echo "<B>この作業にて削除を行っても、SVN上からの削除は行われませんので、<BR>\n";
				echo "サーバー上から削除を行いたい場合には、<A href=\"$item[path]@REV\" TARGET=_BLANK>こちら</A>から削除してください。</B>\n";
				echo "</TD></TR></TABLE>\n";
			}
		}
		else {
			if($_REQUEST['confirmed'] == "YES") {
				$db->del_item($_REQUEST['cat'], $_REQUEST['num'], $_REQUEST['parent']);
			}
			header("Location: $_SERVER[PHP_SELF]?act=edit&cat=$_REQUEST[cat]"); 
		}
	}
}

?>
