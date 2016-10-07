<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL.php';
require_once 'setSession.php';

class Passwd_ {
    protected $in_mass;
    protected $ajax_mass;
    protected $ajax_par;
    protected $ajax_fpar;
        
    public function  __construct($post,$get){
        $this->in_mass   = $post;
        $this->ajax_mass = $get;
        
        $this->ajax_fpar['ajax_save_passwd'] = '2';
    }

    private function gen_salt(){
        $salt = "$1$";
        $alfa = "./0AaBb1CcDd2EeFf3GgHh4IiJj5KkLl6MmNn7OoPp8QqRr9SsTtUuVvWwXxYyZz";
        $len = strlen($alfa)-1;
        for($i=3;$i<11;$i++)
            $salt .= $alfa[mt_rand(0,$len)];
        return $salt;
    }


    private function ajax_save_passwd(){
        $old_passwd    = $this->in_mass['p0'];
        $new_passwd    = $this->in_mass['p1'];
        
        if ($old_passwd != $_SESSION['password']){
            echo '1';
            exit;
        }
           
        $hash = crypt($new_passwd, $this->gen_salt());
        $ws = WSDL_local::singleton();
        $ret_val = $ws->Auth()->setUserPasswd($_SESSION['customerName'],$_SESSION['password'],$hash,$new_passwd);
        if ($ret_val == '0'){
            $_SESSION['password'] = $new_passwd;
            echo '0';
        } else
            echo '1';
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
        $tpl->loadTemplatefile("passwd.tpl.html", true, true);
        
        $this->_ajaxFunction($tpl);

        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");

        $tpl->setCurrentBlock("DOCUMENT");
        $tpl->setVariable("START","");                
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
