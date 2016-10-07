<?php

////////////////////////////////////////////////////////////////////////////////
class Sites_ extends BaseClass_ {
   
    ////////////////////////////////////////////////////////////////////////////
    public function  __construct($post,$get,$template_file)
    {
        parent::__construct(__CLASS__,$post,$get);
        $this->templateFileName = $template_file;
        
        $this->ajax_fpar['ajax_load_list']       = '0';
        $this->ajax_fpar['ajax_get_count_site']  = '0';
        $this->ajax_fpar['ajax_create_site']     = '1';
        $this->ajax_fpar['ajax_unlink_domain']   = '1';
        $this->ajax_fpar['ajax_link_domain']     = '2';
        $this->ajax_fpar['ajax_drop_sites']      = '1';
        $this->ajax_fpar['ajax_get_sites']       = '0';
        $this->ajax_fpar['ajax_get_free_domain'] = '0';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function format_return_string($type, $folder, $domain, $id)
    {
        return $type."|".$folder."|".$domain."|".$id."\n";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_load_list()
    {
        global $db;
        
        $cust_login = $_SESSION['customerName'];
        $homedir    = "/home/".$cust_login[0]."/".$cust_login."/";
        $clt_id     = $_SESSION['customerId'];
        
        $return_value = "";
        
        $rs  = $db->query("SELECT id, path, default_sites FROM sites WHERE cust_id = '$clt_id' ORDER BY path");        
        while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT))
        {
            $path    = substr($rw->path, strlen($homedir));
            $count_domain = 0;
            $temp_str = "";
            $site_id = $rw->id;
            $rs_dom  = $db->query("SELECT fqdn,subdomain,date_registry,id FROM domains WHERE cust_id = '$clt_id' AND sites_id = '".$rw->id."' ORDER BY date_registry");
            while ($rw_dom = $rs_dom->fetchRow(DB_FETCHMODE_OBJECT))
            {
                if (strlen($rw_dom->subdomain)==0)
                   $domain=$rw_dom->fqdn;
                else
                   $domain=$rw_dom->subdomain.".".$rw_dom->fqdn;
                $domain_id = $rw_dom->id;
                $count_domain++;
                $temp_str .= $this->format_return_string('s',$site_id,$domain,$domain_id);
            }
            $return_value .= $this->format_return_string('d',$path,$count_domain,$site_id).$temp_str;
        }
        
        echo $return_value;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_get_count_site()
    {
        global $db; 
       
        $clt_id     = $_SESSION['customerId'];
        
        $count_sites = $db->getOne("SELECT count(*) FROM sites WHERE cust_id = '$clt_id'");
       
        echo $count_sites;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_create_site()
    {
        global $db;
        
        $path       = $this->in_mass['p0'];
        
        $cust_login = $_SESSION['customerName'];
        $clt_id     = $_SESSION['customerId'];
        $server     = $_SESSION['server_name'];
        
        $count_sites = $db->getOne("SELECT count(*) FROM sites WHERE cust_id = '$clt_id'");
        if ($count_sites >= $_SESSION['plan_multi_dom'])
        {
            echo '2';
            exit;
        }
        
        $path = trim($path);
        $path = str_replace(" ", "", $path);
        $path = str_replace(";", "", $path);
        
        
        $homedir   = "/home/".$cust_login[0]."/".$cust_login."/".$path."/public_html";
        
        $count_sites  = $db->getOne("SELECT count(*) FROM sites WHERE path = '$homedir'");
        if ($count_sites != 0)
        {    
            echo '3';
            exit;    
        }
        
        $ws = $this->getWSDL();
        if ($ws->Domains()->createSite($_SESSION['customerName'],
                                       $_SESSION['password'],
                                       $path))
            echo '0';
        else
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_unlink_domain()
    {
        global $db;
        
        $id         = (int) trim($this->in_mass['p0']);
        $clt_id     = $_SESSION['customerId'];
        
        $count_domain  = $db->getOne("SELECT count(*) FROM domains WHERE cust_id = '$clt_id' and id = '$id'");
        if ($count_domain == 0)
        {    
            echo '4';
            exit;    
        }
        
        $ws = $this->getWSDL();
        if ($ws->Domains()->unlinkDomain($_SESSION['customerName'],
                                         $_SESSION['password'],
                                         $id))
            echo '0';
        else
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_link_domain()
    {
        $domain = trim($this->in_mass['p0']);
        $site   = trim($this->in_mass['p1']);
        
        $ws = $this->getWSDL();
        if ($ws->Domains()->linkDomain($_SESSION['customerName'],
                                       $_SESSION['password'],
                                       $domain,
                                       $site))
            echo '0';
        else
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_drop_sites()
    {
        $site = $this->in_mass['p0'];
        $ws = $this->getWSDL();
        if ($ws->Domains()->dropSites($_SESSION['customerName'],
                                      $_SESSION['password'],
                                      $site))
            echo '0';
        else
            echo '1';
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_get_sites()
    {
        global $db;
        $clt_id     = $_SESSION['customerId'];
        $cust_login = $_SESSION['customerName'];
        $homedir = "/home/".$cust_login[0]."/".$cust_login."/";
        $rs  = $db->query("SELECT id, path, default_sites FROM sites WHERE cust_id = '$clt_id' ORDER BY path");
        $return_value = "";
        while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT))
        {
            $path = substr($rw->path, strlen($homedir)).($rw->default_sites?" (основной) ":"");
            $id   = $rw->id;
            $return_value .= $path."|".$id."\n";
        }
        echo $return_value;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    private function ajax_get_free_domain()
    {
        global $db;
        $clt_id     = $_SESSION['customerId'];
        $return_value = "";
        $rs  = $db->query("SELECT id, fqdn, subdomain FROM domains WHERE cust_id = '$clt_id' AND sites_id IS NULL ORDER BY id");        
        while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT))
        {
            strlen($rw->subdomain)==0?$domain=$rw->fqdn:$domain=$rw->subdomain.".".$rw->fqdn;
            $id = $rw->id;
            $return_value .= $domain."|".$id."\n";
        }
        echo $return_value;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    public function getMainPage($alert)
    {
        global $db;
        
        $tpl = $this->getTemplate(get_class($this));
    
        $clt_id     = $_SESSION['customerId'];
        $cust_login = $_SESSION['customerName'];
        $homedir = "/home/".$cust_login[0]."/".$cust_login;
        
        $count_sites   = $db->getOne("select count(*) from sites where cust_id = '$clt_id'");
        $max_sites     = $_SESSION['plan_multi_dom']; 
        
        $tpl->setVariable("SITESUSE",$count_sites);
        $tpl->setVariable("SITESALL",$max_sites);
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