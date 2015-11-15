<?php
require_once "toppageDB.php";
require_once "toppageCommon.php";

function edit($db) {
	session_set_cookie_params(60*60, "/");
	session_start();
	unset($_SESSION['SVN_registPath']);
	
	printHeader($db, "<<Edit>> ");

	// �ڡ�������ι���
	if($_REQUEST['cat'] == "Root") {
		$info = $db->get_pageInfo();
		$year  = date("Y", $info['makeDay'] ? $info['makeDay'] : time());
		$month = date("n", $info['makeDay'] ? $info['makeDay'] : time());
		?>
<H1>�ڡ��������Խ�</H1>
<FORM ACTION="<?= $_SERVER['PHP_SELF'] ?>?act=commit&cat=Root" METHOD="POST">
<INPUT TYPE="submit" VALUE="����"> <INPUT TYPE="reset" VALUE="���ꥢ"><BR>
<H2>�ڡ����إå�</H2>
<TABLE class="Entry">
	<TR><TH>�ڡ��������ȥ�</TH><TD><INPUT TYPE="TEXT" NAME="title" SIZE="50" VALUE="<?= $info['title'] ?>"></TD></TR>
	<TR><TH>�ڡ���������</TH><TD><INPUT TYPE="TEXT" NAME="year" SIZE="4" VALUE="<?= $year ?>">ǯ<INPUT TYPE="TEXT" NAME="month" SIZE="2" VALUE="<?= $month ?>">��</TD></TR>
	<TR><TH>������</TH><TD><INPUT TYPE="TEXT" NAME="comment" SIZE="100" VALUE="<?= $info['comment'] ?>"></TD></TR>
	<TR><TH>Ϣ����</TH><TD><INPUT TYPE="TEXT" NAME="mail" SIZE="50" VALUE="<?= $info['mail'] ?>"></TD></TR>
</TABLE>
<INPUT TYPE="checkbox" NAME="useNewCat" VALUE="enable" <?= $info['useNew'] ? "CHECKED" : "" ?>>��������ɽ������
<BR>
<H2>���ƥ���ꥹ��</H2>
�ʲ��ν񼰤ǵ��Ҥ������֤�ɽ������ޤ���<BR>
<B>���ƥ���̾(�Ѹ�)|���ƥ���̾(���ܸ�)|������</B><BR>
<TEXTAREA NAME="catInfo" ROWS="8" COLS="80" WRAP="off">
<?php
		$catTitle = $db->get_catInfo();
		foreach($catTitle as $title) {
			$detail = $db->get_catInfo($title);
			echo "$title|$detail[jTitle]|$detail[comment]\n";
		}
		?>
</TEXTAREA><BR>
<small><B>��HTML�����Ϥ��Τޤ޻��ѤǤ��ޤ���</B></small><BR>
<small><B>�������ȹ��ܤ��Attach:FileName�פȤ���ȡ�FileName�ǻ���(index��������Хѥ�)�����ե���������Ƥ򤽤Τޤ޽��Ϥ��ޤ�</B></small><BR>

</FORM>

<?php
	}
	
	// ���ƥ���������ɽ��
	else if(!isset($_REQUEST['num'])){
		$catInfo = $db->get_catInfo($_REQUEST['cat']);
		echo "<H1>\n";
		echo "&lt;&lt;�Խ�&gt;&gt; $catInfo[jTitle] <span class=\"h2small\">$_REQUEST[cat]</span>\n";
		echo "</H1>\n";
		echo "[<B><A href=\"./\">���</A></B>]";
		echo "<TABLE width=\"100%\"><TR>";
		echo "<TD>[<A href=\"?act=add&cat=$_REQUEST[cat]&SVN\">SVN��Ͽ</A>] [<A href=\"?act=add&cat=$_REQUEST[cat]\">URL��Ͽ</A>]</TD>";
		echo "<TD align=\"right\">[<A href=\"?act=del&cat=$_REQUEST[cat]\">���</A>]</TD>";
		echo "</TR></TABLE>\n";
		echo "<HR>";
		echo "<table width=\"100%\">\n";
		echo "<tbody>\n";
		$cnt = 1;
		$parentItems = $db->get_item($_REQUEST['cat']);
		sort_margeSVN($parentItems);
		foreach($parentItems as $parentItem) {
			$items = array();
			if($parentItem['haveChild']) {
				$items = $db->get_itemDetail($_REQUEST['cat'], $parentItem['num']);
			}
			else {
				array_unshift($items, $parentItem);
			}
			$parentNum = $parentItem['num'];
			$first_Loop = true;
			foreach($items as $item) {
				if(!$item['num']) {
					$item['num'] = $parentNum;
				}
				$year  = substr($item['date'], 0, 4);
				$month = substr($item['date'], 4, 2);
				$day   = substr($item['date'], 6, 2);
				$dateStr = $year."ǯ".$month."��".$day."��";
				
				echo "<TR class=\"".(($cnt % 2) ? "odd" : "even")."\">\n";
				echo "<TD nowrap width=\"1%\" valign=\"top\">";
				if($first_Loop) {
					if(isSVNFile($item['path'])){
						echo "[<A href=\"?act=add&cat=$_REQUEST[cat]&num=$item[num]&SVN\">SVN����</A>]";
					}
					else {
						echo "[<A href=\"?act=add&cat=$_REQUEST[cat]&num=$item[num]\">URL����</A>]";
					}
				}
				echo "</TD>\n";
				echo "  <TD nowrap width=\"1%\" valign=\"top\">$dateStr</TD>\n";
				if(!$first_Loop) {
					echo "  <TD class=\"child\">";
				}
				else {
					echo "  <TD>";
				}
				$outputs = split_outputLinkData($item['name'], $item['path']);
				$firstLoop = true;
				foreach($outputs as $output) {
					if(!$firstLoop) {
						echo "<BR>";
					}
					echo (strlen($output['url'])) ? "<A href=\"$output[url]\">" : "";
					if(preg_match("/^\\\\s/", $output['title'])) {
						echo "<S>".preg_replace("/^\\\\s/", "", $output['title'])."</S>";
					}
					else {
						echo "$output[title]";
					}
					echo (strlen($output['url'])) ? "</A>" : "";
					$firstLoop = false;
				}
				echo " by $item[author]";
				echo "</TD>";
				echo "<TD nowrap width=\"1%\" valign=\"top\">";
				if($first_Loop) {
					echo "[<A href=\"?act=edit&cat=$_REQUEST[cat]&num=$item[num]\">�Խ�</A>][<A href=\"?act=del&cat=$_REQUEST[cat]&num=$item[num]\">���</A>]";
				}
				else {
					echo "[<A href=\"?act=edit&cat=$_REQUEST[cat]&num=$item[num]&parent=$parentNum\">�Խ�</A>][<A href=\"?act=del&cat=$_REQUEST[cat]&num=$item[num]&parent=$parentNum\">���</A>]";
				}
				echo "</TD></TR>\n";
				$first_Loop = false;
			}
			$cnt++;
		}
		echo "</tbody>\n";
		echo "</table>\n";
	}
	
	// ���ƥ����⥢���ƥ�ɽ��
	else {
		if(!isset($_REQUEST['parent'])) {
			$num      = $_REQUEST['num'];
			$childNum = 0;
		}
		else {
			$num      = $_REQUEST['parent'];
			$childNum = $_REQUEST['num'];
		}
		$info = $db->get_itemDetail($_REQUEST['cat'], $num);
		foreach($info as $target) {
			if($target['num'] != $childNum) {
				continue;
			}
			$catInfo = $db->get_catInfo($_REQUEST['cat']);
			echo "<H1>\"$catInfo[jTitle]\"�����Խ�</H1>\n";
			if(isSVNFile($target['path'])) {
				echo "SVN�����Υե�����Ǥϡ������ȥ롢��Ԥξ���Τ߽������������ǽ�Ǥ���<BR>\n";
			}
			if(!$childNum) {
				echo "<FORM ACTION=\"$_SERVER[PHP_SELF]?act=commit&cat=$_REQUEST[cat]&num=$num\" METHOD=\"POST\">\n";
			}
			else {
				echo "<FORM ACTION=\"$_SERVER[PHP_SELF]?act=commit&cat=$_REQUEST[cat]&num=$num&child=$childNum\" METHOD=\"POST\">\n";
			}
			echo "<INPUT TYPE=\"submit\" VALUE=\"����\"> <INPUT TYPE=\"reset\" VALUE=\"���ꥢ\"><BR>\n";
			printItemEntry($target);
			break;
		}
	}


}


?>
