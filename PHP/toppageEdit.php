<?php
require_once "toppageDB.php";
require_once "toppageCommon.php";

function edit($db) {
	session_set_cookie_params(60*60, "/");
	session_start();
	unset($_SESSION['SVN_registPath']);
	
	printHeader($db, "<<Edit>> ");

	// ページ情報の更新
	if($_REQUEST['cat'] == "Root") {
		$info = $db->get_pageInfo();
		$year  = date("Y", $info['makeDay'] ? $info['makeDay'] : time());
		$month = date("n", $info['makeDay'] ? $info['makeDay'] : time());
		?>
<H1>ページ情報編集</H1>
<FORM ACTION="<?= $_SERVER['PHP_SELF'] ?>?act=commit&cat=Root" METHOD="POST">
<INPUT TYPE="submit" VALUE="更新"> <INPUT TYPE="reset" VALUE="クリア"><BR>
<H2>ページヘッダ</H2>
<TABLE class="Entry">
	<TR><TH>ページタイトル</TH><TD><INPUT TYPE="TEXT" NAME="title" SIZE="50" VALUE="<?= $info['title'] ?>"></TD></TR>
	<TR><TH>ページ作成日</TH><TD><INPUT TYPE="TEXT" NAME="year" SIZE="4" VALUE="<?= $year ?>">年<INPUT TYPE="TEXT" NAME="month" SIZE="2" VALUE="<?= $month ?>">月</TD></TR>
	<TR><TH>コメント</TH><TD><INPUT TYPE="TEXT" NAME="comment" SIZE="100" VALUE="<?= $info['comment'] ?>"></TD></TR>
	<TR><TH>連絡先</TH><TD><INPUT TYPE="TEXT" NAME="mail" SIZE="50" VALUE="<?= $info['mail'] ?>"></TD></TR>
</TABLE>
<INPUT TYPE="checkbox" NAME="useNewCat" VALUE="enable" <?= $info['useNew'] ? "CHECKED" : "" ?>>新着情報を表示する
<BR>
<H2>カテゴリリスト</H2>
以下の書式で記述した順番に表示されます。<BR>
<B>カテゴリ名(英語)|カテゴリ名(日本語)|コメント</B><BR>
<TEXTAREA NAME="catInfo" ROWS="8" COLS="80" WRAP="off">
<?php
		$catTitle = $db->get_catInfo();
		foreach($catTitle as $title) {
			$detail = $db->get_catInfo($title);
			echo "$title|$detail[jTitle]|$detail[comment]\n";
		}
		?>
</TEXTAREA><BR>
<small><B>※HTMLタグはそのまま使用できます。</B></small><BR>
<small><B>※コメント項目を「Attach:FileName」とすると、FileNameで指定(indexからの相対パス)したファイルの内容をそのまま出力します</B></small><BR>

</FORM>

<?php
	}
	
	// カテゴリ内情報の表示
	else if(!isset($_REQUEST['num'])){
		$catInfo = $db->get_catInfo($_REQUEST['cat']);
		echo "<H1>\n";
		echo "&lt;&lt;編集&gt;&gt; $catInfo[jTitle] <span class=\"h2small\">$_REQUEST[cat]</span>\n";
		echo "</H1>\n";
		echo "[<B><A href=\"./\">戻る</A></B>]";
		echo "<TABLE width=\"100%\"><TR>";
		echo "<TD>[<A href=\"?act=add&cat=$_REQUEST[cat]&SVN\">SVN登録</A>] [<A href=\"?act=add&cat=$_REQUEST[cat]\">URL登録</A>]</TD>";
		echo "<TD align=\"right\">[<A href=\"?act=del&cat=$_REQUEST[cat]\">削除</A>]</TD>";
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
				$dateStr = $year."年".$month."月".$day."日";
				
				echo "<TR class=\"".(($cnt % 2) ? "odd" : "even")."\">\n";
				echo "<TD nowrap width=\"1%\" valign=\"top\">";
				if($first_Loop) {
					if(isSVNFile($item['path'])){
						echo "[<A href=\"?act=add&cat=$_REQUEST[cat]&num=$item[num]&SVN\">SVN更新</A>]";
					}
					else {
						echo "[<A href=\"?act=add&cat=$_REQUEST[cat]&num=$item[num]\">URL更新</A>]";
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
					echo "[<A href=\"?act=edit&cat=$_REQUEST[cat]&num=$item[num]\">編集</A>][<A href=\"?act=del&cat=$_REQUEST[cat]&num=$item[num]\">削除</A>]";
				}
				else {
					echo "[<A href=\"?act=edit&cat=$_REQUEST[cat]&num=$item[num]&parent=$parentNum\">編集</A>][<A href=\"?act=del&cat=$_REQUEST[cat]&num=$item[num]&parent=$parentNum\">削除</A>]";
				}
				echo "</TD></TR>\n";
				$first_Loop = false;
			}
			$cnt++;
		}
		echo "</tbody>\n";
		echo "</table>\n";
	}
	
	// カテゴリ内アイテム表示
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
			echo "<H1>\"$catInfo[jTitle]\"項目編集</H1>\n";
			if(isSVNFile($target['path'])) {
				echo "SVN管理のファイルでは、タイトル、作者の情報のみ修正する事が可能です。<BR>\n";
			}
			if(!$childNum) {
				echo "<FORM ACTION=\"$_SERVER[PHP_SELF]?act=commit&cat=$_REQUEST[cat]&num=$num\" METHOD=\"POST\">\n";
			}
			else {
				echo "<FORM ACTION=\"$_SERVER[PHP_SELF]?act=commit&cat=$_REQUEST[cat]&num=$num&child=$childNum\" METHOD=\"POST\">\n";
			}
			echo "<INPUT TYPE=\"submit\" VALUE=\"更新\"> <INPUT TYPE=\"reset\" VALUE=\"クリア\"><BR>\n";
			printItemEntry($target);
			break;
		}
	}


}


?>
