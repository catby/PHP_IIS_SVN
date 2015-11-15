<?php

require_once "../PHP/svn.php";


function svn_display($svn, $url)
{
	$file = $url;
	if(preg_match("/@[0-9]+$/", $url) || preg_match("/@REV$/", $url)) {
		$rev  = substr($url, strrpos($url, "@")+1);
		$file = substr($url, 0, strrpos($url, "@"));
	}
	$endPos = strrpos($file, "/");
	$filename = substr($file, ($endPos == 0 ? $endPos : $endPos+1));

	// 対象ファイルの内容を出力
	if(!isset($rev) || ($rev != "REV")) {
		$ext = strtolower(substr($file, strrpos($file, ".")+1));
		$contentType = get_ContentType($ext);
		$dispositionType = get_DispositionType($ext);
		$data = $svn->getData($url, $fileSize);
		header("Content-Disposition: $dispositionType; filename=\"$filename\"");
		header("Content-Type: $contentType");
		header("Content-Length: $fileSize");
		
		echo $data;
	}
	// 対象ファイルの変更履歴を出力
	else {
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Content-Type: text/html; charset=ECU-JP");
		?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
  <link rel="stylesheet" href="/<?= PARENT_DIR ?>/browser.css" type="text/css"/>
  <title><?= "/".PARENT_DIR."/".$file ?></title>
</head>
<body>
<?php
		// タイトルの作成
		$dirs = explode("/", $file);
		array_unshift($dirs, PARENT_DIR);
		echo "<H1>".$_SERVER['SERVER_NAME']." - ";
		for($i = 0 ; $i < count($dirs)-1 ; $i++) {
/*
			echo "<A href=\"";
			for($j = $i+2 ; $j < count($dirs) ; $j++) {
				echo "../";
			}
			echo "\">$dirs[$i]</A>/";
*/
			echo "$dirs[$i]/";
		}
		echo $dirs[count($dirs)-1];
		echo "</H1>\n";

		// 登録ページへ
		echo "<P>\n";
		echo "このファイルを更新、削除したい場合、以下より更新、削除してください。<BR>\n";
		echo "<A href=\"".BASE_PHP."?commit=$file&cache=1\">[ファイル更新]</A>  ";
		echo "<A href=\"".BASE_PHP."?delete=$file&cache=1\">[ファイル削除]</A>";
		echo "</P>\n";

		echo "<hr>\n";
		// ログリストの作成
		?>
<table class="list">
<tr><th width="80">Rev</th><th width="150">日時</th><th width="150">更新者</th><th>ログメッセージ</th></tr>
<?php
		$logArray = $svn->getFileRev($file);
		$cnt = 1;
		foreach($logArray as $log) {
			if($cnt++ % 2) {
				echo "<tr class = \"odd\">";
			}
			else {
				echo "<tr class = \"even\">";
			}
			echo "<td><A href=\"$filename@$log[rev]\"><img src=\"/".PARENT_DIR."/img/tama_01.gif\" width=10 border=0> $log[rev]</td>";
			echo "<td>$log[date]</td>";
			echo "<td>$log[author]</td>";
			echo "<td>$log[comment]</td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
}


// DispositionTypeの取得
function get_DispositionType($ext)
{
	switch($ext) {
		case "txt":
		case "htm":
		case "html":
		case "css":
		case "xml":
		case "jpg":
		case "jpeg":
		case "gif":
		case "png":
			$type = "inline";
			break;
		default:
			$type = "attachment";
			break;
	}
	return $type;
}


// Content-Typeの取得
function get_ContentType($ext)
{
	switch($ext) {
		case "doc":
			$contentType = "application/msword";
			break;
		case "xls":
			$contentType = "application/vnd.ms-excel";
			break;
		case "ppt":
			$contentType = "application/vnd.ms-powerpoint";
			break;
		case "pdf":
			$contentType = "application/pdf";
			break;
		case "txt":
			$contentType = "text/plain";
			break;
		case "htm":
			$ext = "html";
		case "html":
		case "css":
		case "xml":
			$contentType = "text/$ext";
			break;
		case "jpg":
			$ext = "jpeg";
		case "jpeg":
		case "gif":
		case "png":
			$contentType = "image/$ext";
			break;
		default:
			$contentType = "application/octet-stream";
			break;
	}
	return $contentType;
}


?>
