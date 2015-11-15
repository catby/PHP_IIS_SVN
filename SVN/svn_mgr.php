<?php
require_once "./svn_disp.php";
require_once "./svn_dir.php";
require_once "./svn_commit.php";
require_once "./svn_common.php";



if(preg_match("/^404/", $_SERVER['QUERY_STRING'])) {
	$url = substr($_SERVER['QUERY_STRING'], strpos($_SERVER['QUERY_STRING'], "/", 12));
	$url = substr($url, strpos($url, "/", 1)+1);

	if(preg_match("/#/", $url)) {
		$url = substr($url, 0, strrpos($url, "#"));
	}
	$file = $url;
	if(preg_match("/@[0-9]+$/", $file) || preg_match("/@REV$/", $file)) {
		$file = substr($url, 0, strrpos($file, "@"));
	}

	$kind = $svn->isExist($file);
	switch($kind) {
	case 0:
	case 2:
		svn_dir($svn, $url);
		break;
	case 1:
		svn_display($svn, $url);
		break;
	}
}
else {
	if(isset($_REQUEST['commit']) || isset($_REQUEST['delete'])) {
		svn_commit($svn, $auth);
	}
}

unset($svn);
unset($auth);
?>
