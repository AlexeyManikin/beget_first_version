<?php
class setSession
{
    
    static function setSess($var,$data,$ser = false)
    {
        $_SESSION[$var] = ($ser ? serialize($data) : $data);
    }
    static function chkSess($var)
    {
        return (isset($_SESSION[$var]) ? true : false);
    }
    static function getVar($var,$ser){
        return ($ser ? unserialize($_SESSION[$var]) : $_SESSION[$var]);
    }
    static function setArraySess(&$array, $clear,$ser)
    {
        foreach ($array as $key=>$val)
        {
            $clear ? (isset($_SESSION[$key]) ? '' : $_SESSION[$key] = ($ser ? serialize($val) : $val)) : $_SESSION[$key] = ($ser ? serialize($val) : $val);
        }
    }
}
function setSess(&$item, &$key, $clear)
{
    $clear ? (isset($_SESSION[$key]) ? '' : $_SESSION[$key] = $item) : $_SESSION[$key] = $item;
}
?>