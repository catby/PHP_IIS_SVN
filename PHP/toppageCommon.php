<?php

require_once "SVN.php";


// �ڡ����إå��ν���
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


// �ڡ����եå��ν���
function printFooter($db) {
	$info = $db->get_pageInfo();
	
	echo "<HR>\n";
	echo "<DIV class=\"copyright\">�ܥڡ����˴ؤ�뤴�ո�����˾�� \n";
	echo "<A href=\"mailto:$info[mail]\">$info[mail]</A>\n";
	echo "�ޤǤ��ꤤ���ޤ�.</div>\n";
	echo "\n";
	echo "</BODY>\n";
	echo "</HTML>\n";
}


// �����ƥ�ꥹ�Ȥν���
function printItemList($items, $catName) {
	echo "<table width=\"100%\">\n";
	echo "<tbody>\n";
	$cnt = 1;
	foreach($items as $item) {
		$year  = substr($item['date'], 0, 4);
		$month = substr($item['date'], 4, 2);
		$day   = substr($item['date'], 6, 2);
		$dateStr = $year."ǯ".$month."��".$day."��";
		
		echo "<TR class=\"".(($cnt++ % 2) ? "odd" : "even")."\">\n";
		echo "  <TD nowrap=\"nowrap\" valign=\"top\" width=\"10%\">$dateStr</TD>\n";
		echo "  <TD>";
		if(isSVNFile($item['path'])){
			echo "<A href=\"$item[path]@REV\">".
				 "<IMG SRC=\"$GLOBALS[detailIcon]\" border=0 ALT=\"SVN����\"></A>&nbsp;";
		}
		else if($item['haveChild']) {
			echo "<A href=\"?cat=$catName&no=$item[num]\">".
				 "<IMG SRC=\"$GLOBALS[detailIcon]\" border=0 ALT=\"��������\"></A>&nbsp;";
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


// �����ƥ����ϥơ��֥�ν���
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
	<TR><TH nowrap>������</TH><TD><INPUT TYPE="TEXT" NAME="year" SIZE="4" VALUE="<?= $year ?>"<?= $svnPlus ?>>ǯ
	                       <INPUT TYPE="TEXT" NAME="month" SIZE="2" VALUE="<?= $month ?>"<?= $svnPlus ?>>��
	                       <INPUT TYPE="TEXT" NAME="day" SIZE="2" VALUE="<?= $day ?>"<?= $svnPlus ?>>��</TD></TR>
	<TR><TH nowrap>�����ȥ�</TH><TD><INPUT TYPE="TEXT" NAME="title" SIZE="100" VALUE="<?= (isset($target['name']) ? $target['name'] : "") ?>"<?= $textPlus ?>></TD></TR>
	<TR><TH nowrap>���</TH><TD><INPUT TYPE="TEXT" NAME="author" SIZE="20" VALUE="<?= (isset($target['author']) ? $target['author'] : "") ?>"<?= $textPlus ?>></TD></TR>
	<TR><TH nowrap>���ɥ쥹</TH><TD><INPUT TYPE="TEXT" NAME="path" SIZE="100" VALUE="<?= (isset($target['path']) ? $target['path'] : "") ?>"></TD></TR>
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
<a href="" id="open_contents"  onclick="return showpara()"><B>������ˡ</B>������</a>
<a href="" id="close_contents" onclick="return hidepara()"><B>������ˡ</B>���Ĥ���</a>
<BR>
<font size="2">
<ul style="margin-top:0;margin-bottom:0;" id="chap_contents">
<li>�嵭������Ǥ�HTML�����ϻ��ѤǤ��ޤ���<BR>
<li>�����ȥ�˼��ä���������������ϡ������ȥ������Ƭ�ˡ�<B>\s</B>�פ򵭽Ҥ��Ƥ���������<BR>
<li>��<B>|</B>�פ��ü�ʸ���Ȥ��ư����ޤ���
	<ul><li>��<B>|</B>�פ���Ѥ���ȡ����ĤΥե�����ɤ�ʣ���ԥɥ�����Ȥ�Ǻܤ��������ǽ�Ǥ���<BR>
		<li>��<B>|</B>�פ���Ѥ����硢�����ȥ���ȡ����ɥ쥹��ǻ��Ѥ����<B>|</B>�פο��Ϲ�碌�Ƥ���������<BR>
		<li>��<B>\s</B>�פϡ�<B>|</B>����˵��Ҥ���ɬ�פ�����ޤ���<BR>
	</ul>
</ul>
</font>
<script language="JavaScript"><!--
hidepara();
// --></script>
<?php
	}
}

// ɽ��ʸ�����HTML���ϲ�ǽ��ʸ������Ѵ�����
function encoding_outputStr($str)
{
	$str = preg_replace("/</", "&lt;", $str);
	$str = preg_replace("/>/", "&gt;", $str);
	$str = preg_replace("/\"/", "&quot;", $str);
	return $str;
}

// �����ȥ�ȡ����ɥ쥹�򥻥ѥ졼��ʸ����ʬ��[Array[title, url]]
function split_outputLinkData($title, $url) {
	$ret = array();
	$tArray = explode("|", encoding_outputStr($title));
	$uArray = explode("|", encoding_outputStr($url));
	if(count($tArray) != count($uArray)) {
		$tmp['title'] = "<font color=#FF0000>�����ȥ�Υ��ɥ쥹�Υ��ѥ졼��ʸ���ο������פ��Ƥޤ���</font>";
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


// �оݤ�SVN�ե����뤫Ƚ��
function isSVNFile($file) {
	if(!preg_match("/^\/SVN\//", $file)) {
		return false;
	}
	return preg_replace("/^\/SVN\//", "", $file);
}


// �����ƥ�Υ�����
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

// �����ƥ�Υ������ѥ�����Хå��ؿ�(Date,writeTime �߽�)
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


// SVN�Υϥ�ɥ���������
function openSVN() {
	return new SVN("file:///C:/inetpub/wwwroot/SVN/DB", "C:\\tmp", "guest");
}

?>
