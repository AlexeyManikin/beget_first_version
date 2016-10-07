<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL.php';
require_once 'setSession.php';

class Main_ {
    protected $in_mass;

    public function  __construct($post){
        $this->in_mass = $post;
    }

    protected function menuItem($rs,$tpl){
        if ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT)){
               $tpl->setCurrentBlock("TD");
               $tpl->setVariable("PATH",$rw->path);
               $tpl->setVariable("IMG",$rw->img_1);
               $tpl->setVariable("TEXT",$rw->text);
                $tpl->parse("TD");
        } else {
               $tpl->setCurrentBlock("TD_NULL");
            $tpl->setVariable("START","");
            $tpl->parse("TD_NULL");
        }
    }

    protected  function getMenu(){
        global $db;

        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("index.tpl.html", true, true);

        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");

        $tpl->setCurrentBlock("MAIN_INFORMATION");
        $tpl->setVariable("START","");
        $tpl->setVariable("LOGIN",$_SESSION['customerName']);
        $tpl->setVariable("PLANNAME",$_SESSION['plan_name']);
        $tpl->setVariable("RATE",$_SESSION['rate']);
        $tpl->setVariable("RATEDAY","");
        $tpl->setVariable("SERVERNAME",$_SESSION['server']);
        $tpl->setVariable("QUOTA","");

        $ws = WSDL_local::singleton();

        $login     = $_SESSION['customerName'];
        $ssh_open  = $ws->Auth()->getUserShell($_SESSION['customerName'],$_SESSION['password']);
        $quota     = ceil($ws->Auth()->getQuota($_SESSION['customerName'],$_SESSION['password'])/1024);
        $ftp_count = $ws->Ftp()->getCountFtp($_SESSION['customerName'],$_SESSION['password']);

        $mysql_count = $ws->Mysql()->countDB($_SESSION['customerName'],$_SESSION['password']);
        $mysql_size  = $ws->Mysql()->getAllSizeDB($_SESSION['customerName'],$_SESSION['password']);
        $mysql_size  = ceil($mysql_size/1024);
        $max_mysql   = $_SESSION['plan_mysql_num']>50?'Неограничено':$_SESSION['plan_mysql_num'];
        
        $mailCount   = $ws->Mail()->getMailsCount($_SESSION['customerName'],$_SESSION['password']);

        if($_SESSION['new_account'] == 'Y')
            $quotaall = $_SESSION['plan_test_period_quota'];
        else
            $quotaall = $_SESSION['plan_quota'];

        if ($quotaall != 0)
            $ratioquota = ceil($quota/$quotaall*100);
        else
            $ratioquota = 0;

        $clt_id = $_SESSION['customerId'];
        $count_sites   = $db->getOne("select count(*) from sites where cust_id = '$clt_id'");
        $count_domains = $db->getOne("select count(*) from domains where cust_id = '$clt_id' and subdomain is null;");
        $max_sites     = $_SESSION['plan_multi_dom'];
        $max_domain    = $_SESSION['plan_subdom']>50?'Неограничено':$_SESSION['plan_subdom'];

        $tpl->setVariable("SITES",$count_sites);
        $tpl->setVariable("SITESALL",$max_sites);
        $tpl->setVariable("DOMAIN",$count_domains);
        $tpl->setVariable("DOMAINALL",$max_domain);

        $tpl->setVariable("MYSQL",$mysql_count);
        $tpl->setVariable("MYSQLSIZE",$mysql_size);
        $tpl->setVariable("MYSQLALL",$max_mysql);
        $tpl->setVariable("QUOTA",$quota);
        $tpl->setVariable("QUOTAALL",$quotaall);
        $tpl->setVariable("RATIOQUOTA",$ratioquota);
        $tpl->setVariable("FTP",$ftp_count);
        $tpl->setVariable("FTPALL",$_SESSION['plan_ftp_login']);
        $tpl->setVariable("MAILBOXCOUNT",$mailCount);
        $tpl->parse("MAIN_INFORMATION");


        $tpl->setCurrentBlock("SERVER_INFORMATION");
        $res  =  $db->query("SELECT * FROM servers WHERE id =".$_SESSION['server_id']);
        $rw   =  $res->fetchRow(DB_FETCHMODE_OBJECT);
        $tpl->setVariable("START","");
        $tpl->setVariable("OS",$rw->os);
        $tpl->setVariable("APACHE",$rw->apache_version);
        $tpl->setVariable("MYSQL",$rw->mysql_version);
        $tpl->setVariable("PHP",$rw->php_version);
        $tpl->setVariable("PHPPATH",$rw->php_path);
        $tpl->setVariable("SERVERNAME",$_SESSION['server']);
        $tpl->setVariable("PERL",$rw->perl_version);
        $tpl->setVariable("PERLPATH",$rw->perl_path);
        $tpl->setVariable("SSH",$ssh_open);
        $tpl->parse("SERVER_INFORMATION");

        $tpl->setCurrentBlock("FILEMANAGER");
        $tpl->setVariable("SERVERIP",$_SESSION['server_name'].".beget.ru");
        $tpl->setVariable("USERNAME",$_SESSION['customerName']);
        $tpl->setVariable("USERPASSWD",$_SESSION['password']);
        $tpl->parse("FILEMANAGER");

        $tpl->setCurrentBlock("WELCOM_MESSAGE");
        $tpl->setVariable("START","");
        $tpl->setVariable("NAME",$_SESSION['fio']);

        if (isset($_SESSION['cp_last_time'])){
            $tpl->setCurrentBlock("LAST_LOGIN");
            $tpl->setVariable("LASTDATE",$_SESSION['cp_last_time']);
            $tpl->setVariable("IP",$_SESSION['cp_last_ip']);
            $tpl->parse("LAST_LOGIN");
        }
        $tpl->parse("WELCOM_MESSAGE");

        $plan_id = $_SESSION['plan_id'];
        $cust_id = $_SESSION['cust_id'];

        $sql_block = "SELECT blocked, blockdate FROM account_status WHERE cust_id = $cust_id";
        $rs  = $db->query($sql_block);
        $rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);

        if ($rw->blocked == "N"){
                $sql = "SELECT text,img_1,img_2,img_3,path FROM cpmenu WHERE find_in_set('$plan_id',plans) != 0 and status = 1";
                $rs  = $db->query($sql);

                $tpl->setCurrentBlock("MENU");
                while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT)){
                        $tpl->setCurrentBlock("TR");
                        $tpl->setCurrentBlock("TD");
                        $tpl->setVariable("PATH",$rw->path);
                        $tpl->setVariable("IMG",$rw->img_1);
                        $tpl->setVariable("TEXT",$rw->text);
                        $tpl->parse("TD");
                        $this->menuItem($rs,$tpl);
                        $this->menuItem($rs,$tpl);
                        $this->menuItem($rs,$tpl);
                        $tpl->parse("TR");
                }
                $tpl->parse("MENU");
        } else {
                $tpl->setCurrentBlock("BLOCKED");
                $tpl->setVariable("BLOCKDATE",$rw->blockdate);
                $tpl->parse("BLOCKED");
        }
        $tpl->show();
        //print_r($_SESSION);
        //print_r($_SERVER);
    }

    public function  getDate(){
        return $this->getMenu();
    }
}
?>
