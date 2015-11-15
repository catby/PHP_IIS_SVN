<?php
require_once "toppageDB.php";
require_once "toppageView.php";
require_once "toppageAdd.php";
require_once "toppageEdit.php";
require_once "toppageDelete.php";
require_once "toppageCommit.php";

$db = new toppageDB("toppage.db");

if(!isset($_REQUEST['act'])) {
	view($db);
}
else if($_REQUEST['act'] == "add") {
	add($db);
}
else if($_REQUEST['act'] == "edit") {
	edit($db);
}
else if($_REQUEST['act'] == "del") {
	delete($db);
}
else if($_REQUEST['act'] == "commit") {
	commit($db);
}


unset($db);

?>
