<?php
require_once "./svn_common.php";
require_once "./svn_dir.php";

svn_dir($svn, "");

unset($svn);
unset($auth);
?>
