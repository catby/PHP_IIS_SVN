<?php
require_once "toppageDB.php";
require_once "toppageCommon.php";

function view($db) {
	printHeader($db);
	$info = $db->get_pageInfo();
	$makeDay    = date("F Y", $info['makeDay']);
	$lastUpdate = date("Y/m/d G:i:s", $info['lastUpdate']);
	?>
<h1><?= $info['title'] ?></h1>

<table border="0" cellpadding="4" cellspacing="1" width="100%">
<tbody>
  <tr>
    <td valign="top" width="70%" rowspan=2><small><p>
    <?= $info['comment'] ?>
    </p></small></td>

    <td valign="top" width="30%" nowrap>
      <div class="copyright">since <?= $makeDay ?><br>
      <STRONG>last update</STRONG> <?= $lastUpdate ?><BR>
    </div></td>
  </tr>
  <TR><TD align=right><span class="EditMark"><A href="?act=edit&cat=Root">[編集]</A></span></TD></TR>
</tbody>
</table>
[<B><A href="<?= $GLOBALS['backPage'] ?>">戻る</A></B>]
<?php

	if(!isset($_REQUEST['cat'])) {
		if($info['useNew']) {
			view_newItems($db);
		}
		$cats = $db->get_catInfo();
		foreach($cats as $cat) {
			view_category($db, $cat);
		}
	}
	else {
		view_category($db, $_REQUEST['cat'], $_REQUEST['no']);
	}
	
	printFooter($db);
}


function view_category($db, $catName, $itemNo=0) {
	$catInfo = $db->get_catInfo($catName);
	?>
<A Name="<?= $catName ?>">
<H2>
<?= $catInfo['jTitle'] ?><span class="h2small"><?= $catName ?><?= ($itemNo) ? " [No.$itemNo Detail]" : "" ?></span>
<span class="EditMark"> <A href="?act=edit&cat=<?= $catName ?>">編集</A></span>
</H2>
</A>
<?php
	if(strlen($catInfo['comment'])) {
		if(preg_match("/^[Aa]ttach:/", $catInfo['comment'])) {
			view_AttachFile(substr($catInfo['comment'], 7));
		}
		else {
			echo "<small>$catInfo[comment]</small><BR><BR>\n";
		}
	}

	$items = array();
	if(!$itemNo) {
		$items = $db->get_item($catName);
	}
	else {
		$items = $db->get_itemDetail($catName, $itemNo);
	}
	sort_margeSVN($items);
	printItemList($items, $catName);
}

function view_newItems($db) {
	echo "<H2>新着情報<span class=\"h2small\">what's new </span></H2>\n";
	echo "<font size=\"2\">最新の$GLOBALS[newItemMax]件を表示します。<BR><BR></font>";
	$items = $db->get_newItemList();
	echo "<table>\n";
	echo "<tbody>\n";
	foreach($items as $item) {
		$info = $db->get_catInfo($item['cat']);
		echo "<TR>";
		echo "<TD nowrap>".date("Y",$item['date'])."年".date("m",$item['date'])."月".date("d",$item['date'])."日</TD>";
		echo "<TD><A href=\"#$item[cat]\">$info[jTitle]</A><B> : \"".preg_replace("/^\\\\s/", "", $item['name'])."\" を";
		if($item['kind'] == "add") {
			echo "追加";
		}
		else if($item['kind'] == "edit") {
			echo "修正";
		}
		else {
			echo "削除";
		}
		echo "しました.</B></TD></TR>\n";
	}
	echo "</tbody>\n";
	echo "</table>\n";
}

function view_AttachFile($filename) {
	$attachFile = fopen($filename, "r");
	if ($attachFile) {
		while (($buffer = fgets($attachFile)) !== false) {
			echo $buffer;
		}
		fclose($attachFile);
	}
}

?>
