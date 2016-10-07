<?php

require_once "DB.php";

$db_login  = "";
$db_passwd = "";
$db_name   = "";

define("DB_CENTRAL", $db_passwd);
define("DB_LOCAL_PASS", $db_passwd);
define("DSN", "mysql://".$db_login.":".$db_passwd."@localhost/".$db_name);

$dsn = "mysql://".$db_login.":".$db_passwd."@localhost/".$db_name;
$db = DB::Connect($dsn, true);

if (DB::isError($db))
{
	die ($db->getMessage());
}

$db->query("set session wait_timeout=3600");
$db->query("set names cp1251");

?>
