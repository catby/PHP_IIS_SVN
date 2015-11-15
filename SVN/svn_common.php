<?php
define("PARENT_DIR", "SVN");
define("BASE_PHP", "/SVN/svn_mgr.php");

require_once "../PHP/svn.php";
require_once "../PHP/userAuth.php";


$svn_name = "";
$auth = new UserAuth("C:\\inetpub\\wwwroot\\userInfo\\user.db");
if(isset($_COOKIE['auth_magicID'])) {
	if($auth->set_magicID($_COOKIE['auth_magicID'])) {
		$svn_name = $auth->get_address();
		$svn_name = substr($svn_name, 0, strpos($svn_name, '@'));
	}
}

$svn = new SVN("file:///C:/inetpub/wwwroot/SVN/DB", "C:/tmp", $svn_name);

?>
