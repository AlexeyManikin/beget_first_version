<?php

////////////////////////////////////////////////////////////////////////////////
class SubDomains_ extends BaseClass_ {
    
    ////////////////////////////////////////////////////////////////////////////
    public function  __construct($post,$get,$template_file)
    {
        parent::__construct(__CLASS__,$post,$get);
        $this->templateFileName = $template_file;
        
        $this->ajax_fpar['ajax_load_list']         = '0';
        $this->ajax_fpar['ajax_drop_subdomain']    = '2';
        $this->ajax_fpar['ajax_add_subdomain']     = '2';
    }

    ////////////////////////////////////////////////////////////////////////////
    private function format_return_string($type, $domain, $subdomain)
    {
        return $type."|".$domain."|".$subdomain."\n";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_load_list()
    {
        $ws = $this->getWSDL();
        $domains = unserialize($ws->Domains()->getAllDomain($_SESSION['customerName'],
                                                            $_SESSION['password']));
        $count_domians = count($domains) - 1;
        
        $return_value ="";
        
        while($count_domians >= 0)
        {
            $temp = $domains[$count_domians--];
            $subdomains = unserialize($ws->Domains()->getAllSubDomain($_SESSION['customerName'],
                                                                      $_SESSION['password'],
                                                                      $temp));
            $count_subdomians = count($subdomains) - 1;
            $temp_str = "";
            while($count_subdomians >= 0)
            {
                $temp_str .= $this->format_return_string('s',$temp,$subdomains[$count_subdomians--]);
            }
            $return_value .= $this->format_return_string('d',$temp,count($subdomains)).$temp_str;
        }
        echo $return_value;
    }
        
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_drop_subdomain()
    {
        $domain    = $this->in_mass['p0'];
        $subdomain = $this->in_mass['p1']; 
        
        $ws = $this->getWSDL();
        $subdomains = unserialize($ws->Domains()->getAllSubDomain($_SESSION['customerName'],
                                                                  $_SESSION['password'],
                                                                  $domain));
        
        if (!in_array($subdomain,$subdomains))
        {
            echo '2';
            exit;
        }
        
        if ($ws->Domains()->dropSubDomain($_SESSION['customerName'],
                                          $_SESSION['password'],
                                          $domain,
                                          $subdomain))
            echo '0';
        else
            echo '1';
    }
        
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_add_subdomain()
    {
        $domain       = $this->in_mass['p0'];
        $subdomain    = $this->in_mass['p1'];

        if(ereg("www\.", $subdomain))
                    $subdomain = substr($subdomain, 4);
                    
        if (!ereg("[[:alnum:]]",$subdomain) || ereg("\.",$subdomain))
        {
           echo '3'; 
           exit;
        }     
        
        $ws = $this->getWSDL();
        $subdomains = unserialize($ws->Domains()->getAllSubDomain($_SESSION['customerName'],
                                                                  $_SESSION['password'],
                                                                  $domain));
        if (in_array($subdomain,$subdomains))
        {
            echo '4';
            exit;
        }
        
        $domains = unserialize($ws->Domains()->getAllDomain($_SESSION['customerName'],
                                                            $_SESSION['password']));
        if (!in_array($domain,$domains))
        {
            echo '5';
            exit;
        }
        
        if ($ws->Domains()->addNewSubDomain($_SESSION['customerName'],
                                            $_SESSION['password'],
                                            $domain,
                                            $subdomain))
            echo '0';
        else
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    public function getMainPage($alert)
    {
        $tpl = $this->getTemplate(get_class($this));
        $tpl->setVariable("START","");
        
        $ws = $this->getWSDL();
        $domains = unserialize($ws->Domains()->getAllDomain($_SESSION['customerName'],
                                                            $_SESSION['password']));
        $count_domians = count($domains) - 1;
        
        while($count_domians >= 0)
        {
            $temp = $domains[$count_domians--];
            $tpl->setCurrentBlock("SELECT_DOMAINS");
            $tpl->setVariable("DOMAIN",$temp);
            $tpl->parse("SELECT_DOMAINS");
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