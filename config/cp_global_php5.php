<?php

//-------------------------
require_once "WSDL.php";

$base = "beget.ru";
$hbase = "http://".$base;
$begining = "/index.php";

//--------------------------

class Customer
{
	var $login;
	var $doc_root;
	var $rate;
	var $quota;
	var $plan_id;
	var $plan;
	var $server;

	var $vhosts_num;
	var $virtdoms_num;
	var $max_vhosts_num;
	var $max_virtdoms_num;
	var $mbox_num;
	var $max_mbox_num;
	var $ftp_num;
	var $cur_quota;

	var $vhosts;

	var $company;
	var $cont_fio;
	var $email;
	var $phone;
	var $fax;
	var $type;
	var $blocked;
	var $lang;

	var $max_mysql_num;
	var $max_pgsql_num;
	var $mysql_num;
	var $pgsql_num;

	var $os;
	var $apache_version;
	var $perl_version;
	var $php_version;
	var $mysql_version;
	var $ssh_on;

	function Customer($name,$db_c)
	{
		$this->login = $name;
		$this->doc_root = "/home/$name[0]/$name";

		$sql = "SELECT * FROM account_status WHERE id= (SELECT id FROM customers WHERE cust_login = '$name')";

	//	echo "$name<br>";
	//	print_r($db);

		$rs = $db_c->query($sql);
		$rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);

		$this->rate    = $rw->rate;
		$this->plan_id = $rw->plan_id;
		$this->blocked = $rw->blocked;
		$this->lang    = $rw->cp_lang;
		$this->server  = $rw->server_name;

                $ws = WSDL::singleton();
		$system = $ws->Main($this->server);

		$ssh_open         = $system->getUserShell($this->login);
		$this->cur_quota  = $system->getQuota($this->login);
		$this->ftp_num    = $system->getCountFtp($this->login);
                
		if ($ssh_open == "/bin/false")
		   $this->ssh_on = "N";
                else
		   $this->ssh_on = "Y";

// wsdl
//		$this->mysql_num = $rw->mysql_num;
//		$this->pgsql_num = $rw->pg_num;
//		$this->mbox_num = $rw->mbox_num;

// ÅÚÅ ×Ù×ÏÄ ÄÉÌÅÒÁ
//
//
		$sql = "select name,quota,multi_dom,subdom,mysql_num,pgsql,mbox from plans where id='".$this->plan_id."'";
		$rs = $db_c->query($sql);
		$rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);

		$this->plan = $rw->name;
		$this->quota = $rw->quota;
		$this->max_vhosts_num = $rw->multi_dom;
		$this->max_virtdoms_num = $rw->subdom;
		$this->max_mysql_num = $rw->mysql_num;
		$this->max_mbox_num = $rw->mbox;
		$t = $rw->pgsql;
		$this->max_pgsql_num = ($t == "N"? 0: 1);

//		$sql = "select fqdn from vhosts where customer_id='".$this->login."'";
//		$this->vhosts = $dbh->getCol($sql);
//		$this->vhosts_num = count($this->vhosts);
//
//		$sql = "select count(*) from virtdom where customer_id='".$this->login."'";
//		$this->virtdoms_num = $dbh->getOne($sql);

		$sql = "SELECT * FROM customers WHERE cust_login = '".$this->login."'";
		$rs = $db_c->query($sql);
		$rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);
		
		$this->company = $rw->company;
		$this->phone = $rw->tel;
		$this->fax = $rw->fax;
		$this->type = $rw->type;
		$this->cont_fio = $rw->cont_fio;
		$this->email = $rw->cont_mail;	

		if($this->type == "person")
	            $this->company = $this->cont_fio;
                                                                                                                 
		if($rw->person_mail != $rw->cont_mail && !empty($rw->person_mail))
		    $this->email = $this->email.", ".$rw->person_mail;

		$sql = "select os, apache_version, mysql_version, perl_version, php_version from servers where name ='".$this->server."'";
		list(
                    $this->os,
                    $this->apache_version,
                    $this->mysql_version,
                    $this->perl_version,
                    $this->php_version
                ) = $db_c->getRow($sql);

		if(!$this->max_pgsql_num) 
		{
			$this->pgsql_num = 0;
		}
	}
}



//-------------------------------------------------

function tohtml($text) 
{
  $arr = explode(" ",$text);
  for($i=0;$i<sizeof($arr); $i++)
  {
	if(eregi("^http://*",$arr[$i])) 
	{
	    $output .= " <a href='".$arr[$i]."'>".$arr[$i]."</a>";
	} else 
	    $output .= " ".$arr[$i];
  }

  $search = array("'\n'is");
  $replace = array("<br>");
  $output = preg_replace($search,$replace,$output);

  return $output;
}

function gen_salt() 
{
    $salt = "$1$";
    $alfa = "./0AaBb1CcDd2EeFf3GgHh4IiJj5KkLl6MmNn7OoPp8QqRr9SsTtUuVvWwXxYyZz";
    $len = strlen($alfa)-1;

    for($i=3;$i<11;$i++)
        $salt .= $alfa[mt_rand(0,$len)];
    return $salt;
}

function gen_password() 
{
    $alfa = "./0AaBb1CcDd2EeFf3GgHh4IiJj5KkLl6MmNn7OoPp8QqRr9SsTtUuVvWwXxYyZz";
    $len = strlen($alfa)-1;

    for($i=0;$i<9;$i++)
        $str .= $alfa[mt_rand(0,$len)];
 
    return $str;
}

//-------------------------------------------------

require_once "HTML/Template/IT.php"; 
require_once 'idn.inc';
?>
