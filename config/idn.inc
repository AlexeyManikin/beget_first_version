<?

function idn_decode($fqdn) 
{
    if(ereg("xn--",$fqdn)) 
        $fqdn = iconv("utf-8", "cp1251", idn_to_utf8($fqdn));
    return $fqdn;
}

function idn_encode($fqdn) 
{
    for($i=0;$i<strlen($fqdn);$i++) 
    {
        if(ord($fqdn[$i]) > 127) 
        {
            $fqdn = idn_to_ascii(iconv("cp1251", "utf-8", $fqdn));
	    break;
 	}
    }
    return $fqdn;
}


?>