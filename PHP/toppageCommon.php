<?php

require_once "SVN.php";


// ページヘッダの出力
function printHeader($db, $str="") {
	$info = $db->get_pageInfo();
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Content-Type: text/html; charset=ECU-JP");
	echo "<HTML>\n";
	echo "<HEAD>\n";
	echo "  <META http-equiv=\"Content-Type\" content=\"text/html; charset=EUC-JP\">\n";
	echo "  <LINK rel=\"stylesheet\" href=\"$GLOBALS[styleSheet]\" type=\"text/css\"/>\n";
	echo "  <TITLE>$str$info[title]</TITLE>\n";
	echo "</HEAD>\n";
	echo "<body>\n";
}


// ページフッタの出力
function printFooter($db) {
	$info = $db->get_pageInfo();
	
	echo "<HR>\n";
	echo "<DIV class=\"copyright\">本ページに関わるご意見ご要望は \n";
	echo "<A href=\"mailto:$info[mail]\">$info[mail]</A>\n";
	echo "までお願いします.</div>\n";
	echo "\n";
	echo "</BODY>\n";
	echo "</HTML>\n";
}


// アイテムリストの出力
function printItemList($items, $catName) {
	echo "<table width=\"100%\">\n";
	echo "<tbody>\n";
	$cnt = 1;
	foreach($items as $item) {
		$year  = substr($item['date'], 0, 4);
		$month = substr($item['date'], 4, 2);
		$day   = substr($item['date'], 6, 2);
		$dateStr = $year."年".$month."月".$day."日";
		
		echo "<TR class=\"".(($cnt++ % 2) ? "odd" : "even")."\">\n";
		echo "  <TD nowrap=\"nowrap\" valign=\"top\" width=\"10%\">$dateStr</TD>\n";
		echo "  <TD>";
		if(isSVNFile($item['path'])){
			echo "<A href=\"$item[path]@REV\">".
				 "<IMG SRC=\"$GLOBALS[detailIcon]\" border=0 ALT=\"SVN履歴\"></A>&nbsp;";
		}
		else if($item['haveChild']) {
			echo "<A href=\"?cat=$catName&no=$item[num]\">".
				 "<IMG SRC=\"$GLOBALS[detailIcon]\" border=0 ALT=\"更新履歴\"></A>&nbsp;";
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
		if(mktime(0, 0, 0, $month, $day, $year)+($GLOBALS['newDate']+1)*60*60*24 >= time()) {
			echo " <IMG SRC=\"$GLOBALS[newIcon]\">";
		}
		echo "</TD></TR>\n";
	}
	echo "</tbody>\n";
	echo "</table>\n";
}


// アイテム入力テーブルの出力
function printItemEntry($target, $readOnly=false) {
	$svnPlus = "";
	$textPlus = "";
	$isSVN = isSVNFile($target['path']);
	if($isSVN) {
		$svn = openSVN();
		$lastUpdate = $svn->getLastUpdate($isSVN);
		$year  = substr($lastUpdate, 0, 4);
		$month = substr($lastUpdate, 4, 2);
		$day   = substr($lastUpdate, 6, 2);
		unset($svn);
	}
	else {
		$year  = substr($target['date'], 0, 4);
		$month = substr($target['date'], 4, 2);
		$day   = substr($target['date'], 6, 2);
	}
	if($isSVN) {
		$svnPlus = "readonly STYLE=\"background-color:#DDDDDD\"";
	}
	if($readOnly) {
		$svnPlus = "readonly STYLE=\"background-color:#DDDDDD\"";
		$textPlus = $svnPlus;
	}
	?>
<TABLE class="Entry">
	<TR><TH nowrap>作成日</TH><TD><INPUT TYPE="TEXT" NAME="year" SIZE="4" VALUE="<?= $year ?>"<?= $svnPlus ?>>年
	                       <INPUT TYPE="TEXT" NAME="month" SIZE="2" VALUE="<?= $month ?>"<?= $svnPlus ?>>月
	                       <INPUT TYPE="TEXT" NAME="day" SIZE="2" VALUE="<?= $day ?>"<?= $svnPlus ?>>日</TD></TR>
	<TR><TH nowrap>タイトル</TH><TD><INPUT TYPE="TEXT" NAME="title" SIZE="100" VALUE="<?= (isset($target['name']) ? $target['name'] : "") ?>"<?= $textPlus ?>></TD></TR>
	<TR><TH nowrap>作者</TH><TD><INPUT TYPE="TEXT" NAME="author" SIZE="20" VALUE="<?= (isset($target['author']) ? $target['author'] : "") ?>"<?= $textPlus ?>></TD></TR>
	<TR><TH nowrap>アドレス</TH><TD><INPUT TYPE="TEXT" NAME="path" SIZE="100" VALUE="<?= (isset($target['path']) ? $target['path'] : "") ?>"></TD></TR>
</TABLE>
<?php
	if(!$readOnly) {
	?>

<script language="JavaScript" type="text/JavaScript"> 
<!-- 

function showpara() {
	document.getElementById("close_contents").style.display="block";
	document.getElementById("chap_contents").style.display="block";
	document.getElementById("open_contents").style.display="none";
	return false;
}

function hidepara() {
	document.getElementById("open_contents").style.display="block";
	document.getElementById("close_contents").style.display="none";
	document.getElementById("chap_contents").style.display="none";
	return false;
}

//--> 
</script> 
<a href="" id="open_contents"  onclick="return showpara()"><B>入力方法</B>▼開く</a>
<a href="" id="close_contents" onclick="return hidepara()"><B>入力方法</B>▲閉じる</a>
<BR>
<font size="2">
<ul style="margin-top:0;margin-bottom:0;" id="chap_contents">
<li>上記入力欄ではHTMLタグは使用できません。<BR>
<li>タイトルに取り消し線を引きたい場合は、タイトル欄の先頭に「<B>\s</B>」を記述してください。<BR>
<li>「<B>|</B>」は特殊文字として扱われます。
	<ul><li>「<B>|</B>」を使用すると、１つのフィールドに複数行ドキュメントを掲載する事が可能です。<BR>
		<li>「<B>|</B>」を使用する場合、タイトル欄と、アドレス欄で使用する「<B>|</B>」の数は合わせてください。<BR>
		<li>「<B>\s</B>」は「<B>|</B>」毎に記述する必要があります。<BR>
	</ul>
</ul>
</font>
<script language="JavaScript"><!--
hidepara();
// --></script>
<?php
	}
}

// 表示文字列をHTML出力可能な文字列に変換する
function encoding_outputStr($str)
{
	$str = preg_replace("/</", "&lt;", $str);
	$str = preg_replace("/>/", "&gt;", $str);
	$str = preg_replace("/\"/", "&quot;", $str);
	return $str;
}

// タイトルと、アドレスをセパレート文字で分割[Array[title, url]]
function split_outputLinkData($title, $url) {
	$ret = array();
	$tArray = explode("|", encoding_outputStr($title));
	$uArray = explode("|", encoding_outputStr($url));
	if(count($tArray) != count($uArray)) {
		$tmp['title'] = "<font color=#FF0000>タイトルのアドレスのセパレート文字の数が一致してません。</font>";
		$tmp['url']   = "";
		array_push($ret, $tmp);
		return $ret;
	}
	for($i = 0 ; $i < count($tArray) ; $i++) {
		$tmp['title'] = $tArray[$i];
		$tmp['url']   = $uArray[$i];
		array_push($ret, $tmp);
	}
	return $ret;
}


// 対象がSVNファイルか判定
function isSVNFile($file) {
	if(!preg_match("/^\/SVN\//", $file)) {
		return false;
	}
	return preg_replace("/^\/SVN\//", "", $file);
}


// アイテムのソート
function sort_margeSVN(&$items) {
	if(!count($items)) {
		return;
	}
	$svn = openSVN();
	for($i = 0 ; $i < count($items) ; $i++) {
		$svnPath = isSVNFile($items[$i]['path']);
		if($svnPath) {
			$items[$i]['date'] = $svn->getLastUpdate($svnPath);
		}
	}
	unset($svn);

	usort($items, "sort_margeSVN_callback");
}

// アイテムのソート用コールバック関数(Date,writeTime 降順)
function sort_margeSVN_callback($a, $b) {
	if($a['date'] < $b['date']) {
		return 1;
	}
	else if($a['date'] > $b['date']) {
		return -1;
	}
	else if($a['writeTime'] < $b['writeTime']) {
		return 1;
	}
	else {
		return -1;
	}
}


// SVNのハンドルを取得する
function openSVN() {
	return new SVN("file:///C:/inetpub/wwwroot/SVN/DB", "C:\\tmp", "guest");
}

?>
