<?
ini_set('error_reporting', E_ALL);
ini_set("soap.wsdl_cache_enabled", "0");

function __autoload($class_name)
{
    require_once 'classes/'.$class_name . '.php';
}

function getDefaultLanguage()
{
    // TODO: See ip adress and get langiage.
    return 'rus';
}
require_once 'idn.inc';
?>