<?php
require_once "toppageDB.php";
require_once "toppageCommon.php";

function delete($db) {
	$catInfo = $db->get_catInfo($_REQUEST['cat']);
	if(!isset($_REQUEST['num'])) {
		printHeader($db, "<<Delete>> ");
		echo "<H1>\"$catInfo[jTitle]\"���</H1>\n";
		echo "���ߡ����κ�Ȥ�Ԥ����Ͻ���ޤ���";
		return;
	}
	else {
		if(!isset($_REQUEST['confirmed'])) {
			printHeader($db, "<<Delete>> ");
			echo "<H1>\"$catInfo[jTitle]\"���ܺ��</H1>\n";
			echo "�ʲ��ι��ܤκ����»ܤ��ޤ���<BR>\n";
			echo "������Ǥ�����<BR>\n";
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
				echo "<TABLE><TR><TD valign=top nowrap><B>��</B></TD><TD nowrap>\n";
				echo "<B>���κ�Ȥˤƺ����ԤäƤ⡢SVN�夫��κ���ϹԤ��ޤ���Τǡ�<BR>\n";
				echo "�����С��夫������Ԥ��������ˤϡ�<A href=\"$item[path]@REV\" TARGET=_BLANK>������</A>���������Ƥ���������</B>\n";
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
