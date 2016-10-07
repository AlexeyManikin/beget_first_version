<?php

////////////////////////////////////////////////////////////////////////////////
class Ftp_ extends BaseClass_ {
    
    ////////////////////////////////////////////////////////////////////////////
    public function  __construct($post,$get,$template_file)
    {
        parent::__construct(__CLASS__,$post,$get);
        $this->templateFileName = $template_file;
        
        $this->ajax_fpar['ajax_load_list']         = '0';
        $this->ajax_fpar['ajax_change_ftp_passwd'] = '2';
        $this->ajax_fpar['ajax_delete_ftp']        = '1';
        $this->ajax_fpar['ajax_craete_ftp_user']   = '3';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function format_return_string($param, $array)
    {
        $size = 9 + strlen($_SESSION['customerName']);
        return $param."|".substr($array[1],$size,strlen($array[1])-$size)."|".$array[0]."\n";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_load_list()
    {   
        $ws = $this->getWSDL();
        
        $ftp_count = $ws->Ftp()->getCountFtp($_SESSION['customerName'],
                                             $_SESSION['password']);
        $ftp_account = unserialize($ws->Ftp()->getFtpAccounts($_SESSION['customerName'],
                                                              $_SESSION['password']));
        
        $sites = unserialize($ws->Domains()->getAllSites($_SESSION['customerName'],
                                                         $_SESSION['password']));
        $sites_count = count($sites) - 1;
        
        $return_value = "";
        $ftp_in_site =array();
        $all_ftp =array();
        
        for ($sites_number = 0; $sites_number <= $sites_count; $sites_number++)
        {
            $tmpFtpString = "";
            $i = $ftp_count - 1;
            $j = 0;
            while($i >= 0){
                $ftp_date = unserialize($ftp_account[$i--]);
                if (strcmp($sites[$sites_number],substr($ftp_date[1], 0, strlen($sites[$sites_number]))) == 0)
                {
                    $tmpFtpString .=  $this->format_return_string('l',$ftp_date);
                    $ftp_in_site[] = $this->format_return_string('u',$ftp_date);
                    $j++;
                }
                $all_ftp[] = $this->format_return_string('u',$ftp_date);
            }
            $return_value .= $this->format_return_string('s',array($j,$sites[$sites_number])).$tmpFtpString;
        }
        
        $notSitesFtp = array_diff(array_unique($all_ftp),$ftp_in_site);
        
        if (count($notSitesFtp) > 0)
        {
            $return_value .= $this->format_return_string('s',array(count($notSitesFtp),"                 "));
            for ($ftp_number = 0; $ftp_number <= (count($notSitesFtp) - 1); $ftp_number++)
            {
                $return_value .= array_pop($notSitesFtp);
            }
        }
        
        echo $return_value;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_change_ftp_passwd()
    {
        $ftplogin  = $this->in_mass['p0'];
        $passwd    = $this->in_mass['p1'];
        $ftppasswd = crypt($passwd, gen_salt());
        
        $alert     = $this->checkLogin($passwd);
        if ($alert!=0)
        {
            echo $alert+10;       
            exit;
        }
        
        $ws = $this->getWSDL();
        if ($ws->Ftp()->changePasswordFtp($_SESSION['customerName'],
                                          $_SESSION['password'],
                                          $ftplogin,
                                          $ftppasswd))
            echo "0";
        else
            echo "2";
        
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_delete_ftp()
    {
        $ftplogin    = $this->in_mass['p0'];
        $ws = $this->getWSDL();
        if ($ws->Ftp()->removeFtpUser($_SESSION['customerName'],
                                      $_SESSION['password'],
                                      $ftplogin))
            echo "0";
        else
            echo "1";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_craete_ftp_user()
    {
        $ftplogin    = $this->in_mass['p0'];
        $path        = $this->in_mass['p1'];
        $passwd      = $this->in_mass['p2'];
        $ftppasswd   = crypt($passwd, gen_salt());
        
        $ftplogin = strtolower(trim($ftplogin));
        $alert    = $this->checkLogin($ftplogin);
        $ws = $this->getWSDL();
        $ftp_count = $ws->Ftp()->getCountFtp($_SESSION['customerName'],
                                             $_SESSION['password']);
        
        if ($ftp_count >= $_SESSION['plan_ftp_login'])
        {
            echo "2";          
            exit;
        }
        
        if ($alert != 0)
        {
            echo $alert;
            exit;
        }

        if ($ws->Ftp()->createFtpUser($_SESSION['customerName'],
                                      $_SESSION['password'],
                                      $ftplogin,
                                      $ftppasswd,
                                      $path))
             echo '0';
        else
             echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    public function getMainPage($alert)
    {
        $tpl = $this->getTemplate(get_class($this));
        $tpl->setVariable("LOGIN",$_SESSION['customerName']);
        $tpl->setCurrentBlock("MAIN_INFORMATION");
        $tpl->setVariable("START","");
        $tpl->setVariable("FTPALL",$_SESSION['plan_ftp_login']);
        $tpl->setVariable("ALERT",$alert);
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