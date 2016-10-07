<?php

////////////////////////////////////////////////////////////////////////////////
class MySQL_ extends BaseClass_ {

    ////////////////////////////////////////////////////////////////////////////
    public function  __construct($post,$get,$template_file)
    {
        parent::__construct(__CLASS__,$post,$get);
        $this->templateFileName = $template_file;
        
        $this->ajax_fpar['ajax_load_list']         = '0';
        $this->ajax_fpar['ajax_create_db']         = '2';
        $this->ajax_fpar['ajax_get_count_db']      = '0';
        $this->ajax_fpar['ajax_drop_db']           = '1';
        $this->ajax_fpar['ajax_create_access']     = '3';
        $this->ajax_fpar['ajax_drop_access']       = '2';
        $this->ajax_fpar['ajax_change_passwd']     = '3';
    }

    ////////////////////////////////////////////////////////////////////////////
    private function format_return_string($type, $value1, $value2)
    {
        return $type."|".$value1."|".$value2."\n";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_load_list()
    {   
        $ws = $this->getWSDL();
        
        $mysql_account = unserialize($ws->Mysql()->getAllDB($_SESSION['customerName'],
                                                            $_SESSION['password']));
        $count_mysql = count($mysql_account);
        $return_value = "";
        
        if ($count_mysql > 0)
        {
            $i = $count_mysql-1; 
            while($i >= 0)
            {
                $mysql_name = $mysql_account[$i--];
                $mysql_size = ceil($ws->Mysql()->sizeDB($_SESSION['customerName'],
                                                        $_SESSION['password'],
                                                        $mysql_name)/1024);
                
                $return_value .= $this->format_return_string('b',$mysql_name,$mysql_size);
                
                $mysql_access = unserialize($ws->Mysql()->getAllAccess($_SESSION['customerName'],
                                                                       $_SESSION['password'],
                                                                       $mysql_name));
                $count_mysql_access = count($mysql_access);
                if ($count_mysql_access > 0)
                {
                    $j = $count_mysql_access-1; 
                    while($j >= 0)
                    {
                        $mysql_access_date = $mysql_access[$j--];
                        $return_value .= $this->format_return_string('a',$mysql_access_date,$mysql_name);
                    }
                }
            }
        }
        echo $return_value;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function findBase($dbname)
    {
        $ws = $this->getWSDL();
        return  $ws->Mysql()->findBase($_SESSION['customerName'],
                                       $_SESSION['password'],
                                       $dbname);
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function findAccess($dbname,$dest)
     {
        $ws = $this->getWSDL();
        $list_access  = unserialize($ws->Mysql()->getAllAccess($_SESSION['customerName'],
                                                               $_SESSION['password'],
                                                               $dbname));
        return in_array($dest,$list_access);
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_create_db()
    {
        $mysqllogin   = $this->in_mass['p0'];
        $mysqlpasswd  = $this->in_mass['p1'];
        
        $login = strtolower(trim($_SESSION['customerName']."_".$mysqllogin));
        $alert = $this->checkLogin($mysqllogin);
        if ($alert != 0)
        {
            echo $alert;
            exit;
        }
        
        if ($this->findBase($login))
        {
            echo '2';
            exit;
        }
        
        $ws = $this->getWSDL();
        
        if ($ws->Mysql()->createDB($_SESSION['customerName'],
                                   $_SESSION['password'],
                                   strtolower(trim($mysqllogin)),
                                   $mysqlpasswd))
             echo '0';
        else
             echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_get_count_db()
    {
        $ws = $this->getWSDL();
        $count_mysql = $ws->Mysql()->countDB($_SESSION['customerName'],
                                             $_SESSION['password']);
        echo $count_mysql;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_drop_db()
    {
        $mysqllogin   = $this->in_mass['p0'];
 
        if (!$this->ismyLogin($mysqllogin))
        {
            echo '3';
            exit;
        }
        
        if (!$this->findBase($mysqllogin))
        {
            echo '4';
            exit;
        }
        
        $ws = $this->getWSDL();
        if ($ws->Mysql()->dropDB($_SESSION['customerName'],
                                 $_SESSION['password'],
                                 $mysqllogin))
            echo '0';
        else
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_create_access()
    {
        $dbname   = $this->in_mass['p0'];
        $access   = $this->in_mass['p1'];
        $passwd   = $this->in_mass['p2'];
        
        if (!$this->findBase($dbname))
        {
            echo '4';
            exit;
        }
        
        if (strlen($access) <1)
        {
            echo '5';
            exit;
        }
        
        if (!$this->ismyLogin($dbname))
        {
            echo '3';
            exit;
        }
        
        $ws = $this->getWSDL();
        if ($ws->Mysql()->addAccess($_SESSION['customerName'],
                                    $_SESSION['password'],
                                    $dbname,
                                    $access))
        {
            $ws->Mysql()->changePasswd($_SESSION['customerName'],
                                       $_SESSION['password'],
                                       $dbname,
                                       $access,
                                       $passwd);
            echo '0';
        }
        else
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_change_passwd()
    {
        $dbname = $this->in_mass['p0'];
        $access   = $this->in_mass['p1'];
        $passwd = $this->in_mass['p2'];
        
        if (!$this->findBase($dbname))
        {
            echo '4';
            exit;
        }
        
        if (strlen($access) <1)
        {
            echo '5';
            exit;
        }
        
        if (!$this->ismyLogin($dbname))
        {
            echo '3';
            exit;
        }
        
        if (!$this->findAccess($dbname,$access))
        {
            echo '5';
            exit;
        }
        
        $ws = $this->getWSDL();
        
        if ($ws->Mysql()->changePasswd($_SESSION['customerName'],
                                       $_SESSION['password'],
                                       $dbname,
                                       $access,
                                       $passwd))
            echo '0';
        else
            echo '1';  
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_drop_access()
    {
        $dbname   = $this->in_mass['p0'];
        $access   = $this->in_mass['p1'];
        
        if (!$this->findBase($dbname))
        {
            echo '4';
            exit;
        }
        
        if (strlen($access) <1)
        {
            echo '5';
            exit;
        }
        
        if (!$this->ismyLogin($dbname))
        {
            echo '3';
            exit;
        }
        
        if (!$this->findAccess($dbname,$access))
        {
            echo '5';
            exit;
        }
        
        $ws = $this->getWSDL();
        
        if ($ws->Mysql()->dropAccess($_SESSION['customerName'],
                                     $_SESSION['password'],
                                     $dbname,
                                     $access))
            echo '0';
        else 
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    public function getMainPage($alert)
    {
        $tpl = $this->getTemplate(get_class($this));
        
        $tpl->setVariable("LOGIN",$_SESSION['customerName']);
        $tpl->setVariable("SERVER",$_SESSION['server_name']);
        
        $tpl->setCurrentBlock("MAIN_INFORMATION");
        
        $ws = $this->getWSDL();
        $count_mysql = $ws->Mysql()->countDB($_SESSION['customerName'],
                                             $_SESSION['password']);
        
        $tpl->setVariable("MYSQLCOUNT",$count_mysql);
        $tpl->parse("MAIN_INFORMATION");
        $tpl->parse("DOCUMENT");
        $tpl->show();
    }

    ////////////////////////////////////////////////////////////////////////////
    public function callMetod($name)
    {
        if (method_exists($this, $name))
            call_user_func(array($this, $name));
    }
}
?>