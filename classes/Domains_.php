<?php

////////////////////////////////////////////////////////////////////////////////
class Domains_ extends BaseClass_ {

    ////////////////////////////////////////////////////////////////////////////
    public function  __construct($post,$get,$template_file)
    {
        parent::__construct(__CLASS__,$post,$get);
        $this->templateFileName = $template_file;
        
        $this->ajax_fpar['ajax_load_list']         = '0';
        $this->ajax_fpar['ajax_move_domain']       = '2';
        $this->ajax_fpar['ajax_drop_domain']       = '1';
    }

    ////////////////////////////////////////////////////////////////////////////
    private function ajax_move_domain()
    {
        $domain = $this->in_mass['p0'];
        $tld    = $this->in_mass['p1'];

        if(ereg("www\.", $domain))
                    $domain = substr($domain, 4);
                    
        if (!ereg("[[:alnum:]]",$domain) || ereg("\.",$domain))
        {
           echo '2';
           exit;
        }
        
        $ws = WSDL_local::singleton();

        $tldArray = unserialize($ws->Domains()->getAllTld($_SESSION['customerName'],
                                                          $_SESSION['password']));
        
        if (!in_array($tld,$tldArray))
        {
           echo '3';
           exit;
        }
        
        if ($ws->Domains()->findDomain($_SESSION['customerName'],
                                       $_SESSION['password'],
                                       $domain.$tld) != 0)
        {
           echo '4';
           exit;
        }

        if ($ws->Domains()->addNewDomain($_SESSION['customerName'],
                                          $_SESSION['password'],
                                          $domain,
                                          $tld,
                                          'move',
                                          '0'))
           echo '0';
        else
           echo '1';
    }

    ////////////////////////////////////////////////////////////////////////////
    private function ajax_drop_domain()
    {
        $domain   = $this->in_mass['p0'];
        
        $ws = $this->getWSDL();
        if ($ws->Domains()->dropDomain($_SESSION['customerName'],
                                       $_SESSION['password'],
                                       $domain) )
             echo '0';
         else
             echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_load_list()
    {   
        $ws = $this->getWSDL();
        $domains = unserialize($ws->Domains()->getAllDomainInfo($_SESSION['customerName'],
                                                                $_SESSION['password']));
        $count_domians = count($domains) - 1;
        $return_value = '';
        
        while($count_domians >= 0)
        {
            $temp = $domains[$count_domians--];
            $return_value .= $temp."\n";
        }
        echo $return_value;
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getMainPage($alert)
    {

        $tpl = $this->getTemplate(get_class($this));
        $ws = $this->getWSDL();
        $tld = unserialize($ws->Domains()->getAllTld($_SESSION['customerName'],
                                                     $_SESSION['password']));
        
        $count_tld = count($tld) - 1;
        while ($count_tld >= 0)
        {
            $tpl->setCurrentBlock("SELECT_ZONE_NAME");
            $tpl->setVariable("ZONE",$tld[$count_tld--]);
            $tpl->parse("SELECT_ZONE_NAME");
        }
        
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
