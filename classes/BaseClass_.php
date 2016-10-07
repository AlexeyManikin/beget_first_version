<?php
////////////////////////////////////////////////////////////////////////////////
require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL_local.php';

////////////////////////////////////////////////////////////////////////////////
abstract class BaseClass_ {
    protected $in_mass;
    protected $ajax_mass;
    protected $ajax_fpar;
    protected $language;
    protected $templateFileName;
    
    private $arrMethods;

    ////////////////////////////////////////////////////////////////////////////
    public function  __construct($strDerivedClassName, $post, $get)
    {
        $this->in_mass   = $post;
        $this->ajax_mass = $get;
        
        $oRefl = new ReflectionClass ($strDerivedClassName);
        if (is_object($oRefl))
            $this->arrMethods = $oRefl->getMethods();
    }

    ////////////////////////////////////////////////////////////////////////////
    protected function getTemplate($class)
    {
        $tpldir = 'templates/';

        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile($this->templateFileName, true, true);

        foreach ($this->arrMethods as $method)
        {
            $method_name = $method->getName();
            if (substr($method_name,0,4) == 'ajax')
            {
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
        
        $tpl->setCurrentBlock("DOCUMENT");
        
        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");
        
        return $tpl;
    }

    ////////////////////////////////////////////////////////////////////////////
    abstract function getMainPage($alert);
    
    ////////////////////////////////////////////////////////////////////////////
    abstract function callMetod($name);

    ////////////////////////////////////////////////////////////////////////////
    final public function getDate()
    {
        if(notAuthorized())
        {
            header("Location: $base_url");
            exit();
        }
        
        if (isset($this->ajax_mass['ajax']))
        {
            if (substr($this->ajax_mass['method'],0,4) == "ajax")
                $this->callMetod($this->ajax_mass['method']);
        } else
        {
            $tpl = $this->getMainPage("");     
        }
     }
     
    ////////////////////////////////////////////////////////////////////////////
    // Функции проверки данных и прочей ерунды
    final protected function checkLogin($login)
    {
        if(strlen($login) < 1)
        {
           return 10;
        } else if(strlen($login) > 12)
        {
           return 11;
        } else if (!ereg("[[:alnum:]]",$login))
        {
           return 12;    
        }
        return 0;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    final protected function isMyLogin($login)
    {
        $login_b  = substr($login,0,strlen($_SESSION['customerName'])); 
        if ($_SESSION['customerName'] == $login_b)
                return true;        
        return false;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    final protected function getWSDL()
    {
        return WSDL_local::singleton();
    }
}
?>
