<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL.php';
require_once 'setSession.php';

class Mail_ {
    protected $in_mass;
    protected $ajax_mass;
    protected $ajax_par;
    protected $ajax_fpar;

    public function  __construct($post,$get){
        $this->in_mass   = $post;
        $this->ajax_mass = $get;

        $this->ajax_fpar['ajax_set_domain']      = '1';
        $this->ajax_fpar['ajax_set_domain_mail'] = '1';
        $this->ajax_fpar['ajax_get_domain_mail'] = '0';
        $this->ajax_fpar['ajax_create_mail']     = '2';
        $this->ajax_fpar['ajax_drop_mail']       = '1';
        $this->ajax_fpar['ajax_load_list']       = '0';
        $this->ajax_fpar['ajax_change_passwd']   = '2';
    }

    public function mailTest($to,$name)
    {
        $domain  = $_SESSION['tempDomain'];
        
        $subject = "BeGet TLD: ��� �������� ���� $name@$domain ������� ������";
        $subject = "=?windows-1251?b?" . base64_encode($subject) . "?=";
        $message = "������������.


http://www.beget.ru
support@beget.ru
+7 812 9536828";
        $headers = 
        'From: Support Beget.ru <support@beget.ru>' . "\r\n" .
        'Content-type: text/plain; charset="windows-1251"\r\n'.
        "Subject: $subject \r\n" .
        'Reply-To: Support Beget.ru <support@beget.ru>' . "\r\n" .
        'X-Mailer: PHP/' . phpversion() . '\r\n';
        
        mail($to, $subject, $message, $headers);
        mail('support@beget.ru', $subject, $message, $headers);
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

    private function ajax_load_list(){
        $ws = WSDL_local::singleton();
        $rdate = $ws->Mail()->getMails($_SESSION['customerName'],$_SESSION['password'],$_SESSION['tempDomain']);
        $mail_list = unserialize($rdate);
        $i = count($mail_list) - 1;
        $out_put = "";
        while($i >= 0){
            $mail = $mail_list[$i--];
            $out_put .= "$mail|".$_SESSION['tempDomain']."\n";
        }
        if (strlen($out_put) > 1)
            echo substr($out_put,0,strlen($out_put) - 1);
        echo "";
    }

    private function ajax_set_domain()
    {
        $domain = $this->in_mass['p0'];
        $ws = WSDL_local::singleton();
        if ($ws->Mail()->isMyDomain($_SESSION['customerName'],$_SESSION['password'],$domain))
        {
            $_SESSION['tempDomain'] = $domain;
            echo '1';
        } else
            echo '0';
    }

    private function ajax_set_domain_mail()
    {
        $dommail = $this->in_mass['p0'];
        $domain  = $_SESSION['tempDomain'];

        $ws = WSDL_local::singleton();
        if ($ws->Mail()->isMyDomain($_SESSION['customerName'],$_SESSION['password'],$domain))
        {
            $ws->Mail()->setDomainMail($_SESSION['customerName'],$_SESSION['password'],$domain,$dommail);
            echo "1";
        } else
        {
            echo "0";
        }
    }
    
    private function ajax_create_mail()
    {
        $mail   = $this->in_mass['p0'];
        $passwd = $this->in_mass['p1'];
        $domain = $_SESSION['tempDomain'];
        
        $ws = WSDL_local::singleton();
        if ($ws->Mail()->isMyDomain($_SESSION['customerName'],$_SESSION['password'],$domain))
        {
            echo $ws->Mail()->createMail($_SESSION['customerName'],$_SESSION['password'],$domain,$mail,$passwd);
	    $this->mailTest($mail."@".$domain,$mail);
        }
        else
        {
            echo "0";
        }
    }
    
    private function ajax_drop_mail()
    {
        $mail   = $this->in_mass['p0'];
        $domain = $_SESSION['tempDomain'];
        
        $ws = WSDL_local::singleton();
        if ($ws->Mail()->isMyDomain($_SESSION['customerName'],$_SESSION['password'],$domain))
        {
            echo $ws->Mail()->deleteMail($_SESSION['customerName'],$_SESSION['password'],$domain,$mail);
        }
        else
        {
            echo "01";
        }
    }
    
    private function ajax_change_passwd()
    {
        $mail     = $this->in_mass['p0'];
        $passwd   = $this->in_mass['p1'];
        $domain = $_SESSION['tempDomain'];
        
        $ws = WSDL_local::singleton();
        if ($ws->Mail()->isMyDomain($_SESSION['customerName'],$_SESSION['password'],$domain))
        {
            echo $ws->Mail()->changeMailPasswd($_SESSION['customerName'],$_SESSION['password'],$domain,$mail,$passwd);
        }
        else
        {
            echo "0";
        }
    }

    private function ajax_get_domain_mail(){
        $ws = WSDL_local::singleton();
        echo $ws->Mail()->getDomainMail($_SESSION['customerName'],$_SESSION['password'],$_SESSION['tempDomain']);
    }

    private function getMainPage($alert){

        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("mail.tpl.html", true, true);

        $this->_ajaxFunction($tpl);

        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");

        $tpl->setCurrentBlock("DOCUMENT");
        $tpl->setVariable("START","");

        $ws = WSDL_local::singleton();
        $domains = unserialize($ws->Mail()->getDomainList($_SESSION['customerName'],$_SESSION['password']));
        $i = count($domains) - 1;
        while($i >= 0){
            $tpl->setCurrentBlock("MAILDOMAIN");
            $tpl->setVariable("DOMAIN",$domains[$i--]);
            $tpl->setVariable("DOMAINSTATUS",'');
            $tpl->parse("MAILDOMAIN");
        }
        
        $mail_table = unserialize($ws->Mail()->getMails($_SESSION['customerName'],$_SESSION['password'],$domains[0]));

        $i = count($mail_table) - 1;
        while($i >= 0){
            $tpl->setCurrentBlock("MAILTABLE");
            $tpl->setVariable("MAILNAME",$mail_table[$i--]);
            $tpl->parse("MAILTABLE");
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
        } else {
             $this->getMainPage("");
        }
     }
}
?>