<?php
require_once "../PHP/svn.php";


function svn_dir($svn, $url)
{
// URLの末が"/"で終わってない場合は"/"付きにリダイレクト
if(strlen($url) && !preg_match("/\/$/", $url)) {
	header("Location: /".PARENT_DIR."/$url/"); 
}
$url = preg_replace("/\/$/", "", $url);


header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Content-Type: text/html; charset=ECU-JP");
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="/<?= PARENT_DIR ?>/browser.css" type="text/css"/>
  <title><?= "/".PARENT_DIR."/".$url ?></title>
</head>
<body>
<?php
// タイトルの作成
$dirs = array();
if(strlen($url)) {
	$dirs = explode("/", $url);
}
array_unshift($dirs, PARENT_DIR);
echo "<H1>".$_SERVER['SERVER_NAME']." - /";
for($i = 0 ; $i < count($dirs)-1 ; $i++) {
	echo "<A href=\"";
	for($j = $i+1 ; $j < count($dirs) ; $j++) {
		echo "../";
	}
	echo "\">$dirs[$i]</A>/";
}
echo "<A href=\"./\">".$dirs[count($dirs)-1]."</A>/";
echo "</H1>\n";

// 登録ページへ
echo "<P>\n";
echo "このフォルダへ新たにファイルを登録したい場合、以下より登録してください。<BR>\n";
echo "<A href=\"".BASE_PHP."?commit=$url&cache=1\">[新規登録]</A>";
echo "</P>\n";

echo "<hr>\n";
// ファイルリストの作成
if((count($dirs) > 1) && strlen($dirs[count($dirs)-1])) {
	echo "<P><A href=\"../\">[To Parent Directory]</A></P>\n";
}
?>
<table class="list">
<tr><th width="40">履歴</th><th>名前</th><th width="70">サイズ</th><th width="150">最終更新日</th><th width="100">最終更新者</th></tr>

<?php
$fileArray = $svn->getDir($url);
$cnt = 1;
foreach($fileArray as $file) {
	if($cnt++ % 2) {
		echo "<tr class = \"odd\">";
	}
	else {
		echo "<tr class = \"even\">";
	}
	if($file['kind'] == "dir") {
		echo "<td>&nbsp;</td>";
	}
	else {
		echo "<td align=\"center\"><A href=\"$file[file]@REV\"><img src=\"/".PARENT_DIR."/img/paper01.gif\" width=12 border=0></A></td>";
	}
	echo "<td>";
	if($file['kind'] == "dir") {
		echo "<img src=\"/".PARENT_DIR."/img/folder_s10.gif\" width=12>  ";
	}
	else {
		echo "<img src=\"/".PARENT_DIR."/img/paper03.gif\" width=10>  ";
	}
	echo "<A href=\"$file[file]\">$file[file]</A></td>";
	if($file['size'] > (1024*1024)) {
		$size = sprintf("%.1f", $file['size']/(1024*1024));
		echo "<td>$size MB</td>";
	}
	else if($file['size'] > 1024) {
		$size = sprintf("%.1f", $file['size']/1024);
		echo "<td>$size kB</td>";
	}
	else {
		if(strlen($file['size'])) {
			echo "<td>$file[size] Byte</td>";
		}
		else {
			echo "<td>&nbsp;</td>";
		}
	}
	echo "<td>" . sprintf("%04d/%02d/%02d", $file['year'], $file['month'], $file['day']);
	echo " $file[time]</td>";
	echo "<td>$file[author]</td>";
	echo "</tr>\n";
}

echo "</table>\n";
}
?>
