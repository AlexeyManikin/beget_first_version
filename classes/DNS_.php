<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL.php';
require_once 'setSession.php';

class DNS_ {
    protected $in_mass;
    protected $ajax_mass;
    protected $ajax_par;
    protected $ajax_fpar;

    public function  __construct($post,$get){
        $this->in_mass   = $post;
        $this->ajax_mass = $get;

        $this->ajax_fpar['ajax_set_domain'] = '1';
        $this->ajax_fpar['ajax_seve'] = '8';
    }

    private function ajax_set_domain(){
        global $db;
        $domain  = $this->in_mass['p0'];
        $clt_id = $_SESSION['customerId'];

        if (ereg ("([A-Za-z0-9_]*).([A-Za-z0-9_.-]*)", $domain, $regs)){
            $rs = $db->query("SELECT count(*) as count FROM domains WHERE subdomain IS NULL AND fqdn='$domain' AND cust_id = '$clt_id'");
            $rs2 = $db->query("SELECT count(*) as count FROM domains WHERE subdomain='$regs[1]' AND fqdn='$regs[2]' AND cust_id = '$clt_id'");
            $rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);
            $rw2 = $rs2->fetchRow(DB_FETCHMODE_OBJECT);
            if ($rw->count == '1' && $rw2->count == '0') {
                $_SESSION['dnsDomain']    = $domain;
                $_SESSION['dnsSubDomain'] = "";
                $_SESSION['dnsTypes']     = "domain";
                echo "Changet";
            } else if ($rw->count == '0' && $rw2->count == '1') {
                $_SESSION['dnsDomain']    = $regs[2];
                $_SESSION['dnsSubDomain'] = $regs[1];
                $_SESSION['dnsTypes']     = "subdomain";
                echo "Changet";
            } else {
                $_SESSION['dnsDomain']    = "notdefiden";
                $_SESSION['dnsSubDomain'] = "notdefiden";
                $_SESSION['dnsTypes']     = "notdefiden";
                echo "NotChanget";
            }
        } else  {
            echo "NotChanget";
        }

    }

    private function get_dns_record(){
        global $db;
        $typeDomain = $_SESSION['dnsTypes'] ;
        $domain     = $_SESSION['dnsDomain'];
        $subdomain  = $_SESSION['dnsSubDomain'];
        $clt_id     = $_SESSION['customerId'];

        if ($typeDomain == 'subdomain'){
            $rs = $db->query("SELECT id FROM domains WHERE subdomain='$subdomain' AND fqdn='$domain' AND cust_id = '$clt_id'");
        } else if ($typeDomain == 'domain') {
            $rs = $db->query("SELECT id FROM domains WHERE subdomain IS NULL AND fqdn='$domain' AND cust_id = '$clt_id'");
        } else
            exit;
        $rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);
        $id = $rw->id;
        $rs = $db->query("SELECT * FROM dns WHERE domains_id = '$id'");
        $rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);
        return $rw;
    }

    private function set_a_records($domain,$rr_a,$rr_ns1,$rr_ns2){
        $ws = WSDL_local::singleton();
        $rdate = $ws->Domains()->changeARecords($_SESSION['customerName'],$_SESSION['password'],$domain,$rr_a,$rr_ns1,$rr_ns2);
        return $rdate;
    }

    private function set_ns_records($domain,$rr_ns1,$rr_ns2){
        $ws = WSDL_local::singleton();
        $rdate = $ws->Domains()->changeNSRecords($_SESSION['customerName'],$_SESSION['password'],$domain,$rr_ns1,$rr_ns2);
        return $rdate;
    }

    private function set_cname_records($domain,$rr_cname){
        $ws = WSDL_local::singleton();
        $rdate = $ws->Domains()->changeCnameRecords($_SESSION['customerName'],$_SESSION['password'],$domain,$rr_cname);
        return $rdate;
    }

    private function ajax_seve(){
        $domain  = $this->in_mass['p0'];
        $type    = $this->in_mass['p1'];
        $rr_a    = $this->in_mass['p2'];
        $rr_mx1  = $this->in_mass['p3'];
        $rr_mx2  = $this->in_mass['p4'];
        $rr_ns1  = $this->in_mass['p5'];
        $rr_ns2  = $this->in_mass['p6'];
        $rr_cname = $this->in_mass['p7'];

        $ret_value = "";
        switch($type){
                case '0':
                        $ret_value = $this->set_a_records($domain,$rr_a,$rr_mx1,$rr_mx2);
                        break;
                case '1':
                        $ret_value = $this->set_ns_records($domain,$rr_ns1,$rr_ns2);
                        break;
                case '2':
                        $ret_value = $this->set_cname_records($domain,$rr_cname);
                        break;
                default:
                        $ret_val = "10";
        }
        echo $ret_value;
    }

    private function ajax_get_rra(){
        $rw = $this->get_dns_record();
        $rr_a = $rw->a;
        echo "$rr_a";
    }

    private function ajax_get_mx1(){
        $rw = $this->get_dns_record();
        $rr_mx1 = $rw->mx1;
        echo "$rr_mx1";
    }

    private function ajax_get_mx2(){
        $rw = $this->get_dns_record();
        $rr_mx2 = $rw->mx2;
        echo "$rr_mx2";
    }

    private function ajax_get_cmane(){
        $typeDomain = $_SESSION['dnsTypes'] ;
        if ($typeDomain == 'domain'){
            exit;
        }
        $rw = $this->get_dns_record();
        $rr_cname = $rw->cname;
        echo "$rr_cname";
    }

    private function ajax_get_ns1(){
        $typeDomain = $_SESSION['dnsTypes'] ;
        if ($typeDomain == 'domain'){
            exit;
        }
        $rw = $this->get_dns_record();
        $rr_ns1 = $rw->ns1;
        echo "$rr_ns1";
    }

    private function ajax_get_ns2(){
        $typeDomain = $_SESSION['dnsTypes'] ;
        if ($typeDomain == 'domain'){
            exit;
        }
        $rw = $this->get_dns_record();
        $rr_ns2 = $rw->ns2;
        echo "$rr_ns2";
    }

    private function ajax_get_domain_type(){
        $typeDomain = $_SESSION['dnsTypes'] ;
        echo "$typeDomain";
    }

    private function ajax_get_type(){
        $rw = $this->get_dns_record();
        $rr_type = $rw->type_r;
        echo "$rr_type";
    }

    private function _ajaxFunction($tpl){
        $class_methods = get_class_methods(get_class($this));
        foreach ($class_methods as $method_name) {
            if (substr($method_name,0,4) == 'ajax'){
                $tpl->setCurrentBlock("AJAX_FUNCTION");
                   $tpl->setVariable("FINCTION_NAME",$method_name);
                if (isset($this->ajax_fpar["$method_name"])){
                       $param = "";
                       $param_desc = "";
                       for ($i=0; $i<$this->ajax_fpar[$method_name]; $i++){
                           $param .= "p$i, ";
                           $param_desc .= "\"p$i=\" + p$i,";
                       }
                       $param_desc = substr($param_desc,0,(strlen($param_desc)-1));
                       $tpl->setVariable("PARAM_LIST",$param);
                       $tpl->setVariable("PARAM_NAME",$param_desc);
                   }
                $tpl->parse("AJAX_FUNCTION");
            }
        }
    }

    private function getMainPage($alert){
        global $db;

        $_SESSION['dnsTypes'] = "domain";

        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("dns.tpl.html", true, true);

        $this->_ajaxFunction($tpl);

        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");

        $tpl->setCurrentBlock("DOCUMENT");
        $tpl->setVariable("START","");

        $clt_id = $_SESSION['customerId'];
        $rs  = $db->query("SELECT fqdn FROM domains WHERE subdomain IS NULL AND cust_id = '$clt_id' ORDER BY date_registry");
        while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT)){
            $tpl->setCurrentBlock("SELECT_DOMAIN");
            $domain = $rw->fqdn;
            $tpl->setVariable("DOMAIN",$domain);
            $rs_sub  = $db->query("SELECT subdomain FROM domains WHERE fqdn='$domain' AND cust_id = '$clt_id' AND subdomain IS NOT NULL ORDER BY date_registry");
            while ($rw_sub = $rs_sub->fetchRow(DB_FETCHMODE_OBJECT)){
                $tpl->setCurrentBlock("SELECT_SUBDOMAIN");
                $subdomain = $rw_sub->subdomain.".".$domain;
                $tpl->setVariable("SUBDOMAIN",$subdomain);
                $tpl->parse("SELECT_SUBDOMAIN");
            }
            $tpl->parse("SELECT_DOMAIN");
        }

        $tpl->setVariable("ALERT",$alert);
        $tpl->parse("DOCUMENT");
        $tpl->show();
    }

    public function getDate(){
        if (isset($this->ajax_mass['ajax'])){
            if (substr($this->ajax_mass['method'],0,4) == "ajax")
                if (method_exists($this, $this->ajax_mass['method']))
                    call_user_func(array($this, $this->ajax_mass['method']));
        } else
            $this->getMainPage("");
     }
}
?>
