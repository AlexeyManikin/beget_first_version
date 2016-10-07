<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL.php';
require_once 'setSession.php';

class Backup_ {
    protected $in_mass;
    protected $ajax_mass;
    protected $ajax_par;
    protected $ajax_fpar;
          
    public function  __construct($post,$get){
        $this->in_mass   = $post;
        $this->ajax_mass = $get; 
        
        $this->ajax_fpar['ajax_load_file_list'] = '1';
        $this->ajax_fpar['ajax_set_date'] = '1';
        $this->ajax_fpar['ajax_restore'] = '1';    
        $this->ajax_fpar['ajax_set_type'] = '1';
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
    
    private function load_file_list($path){
        $ws = WSDL_local::singleton();
        $rdate = $ws->Backup()->getFileList($_SESSION['customerName'],$_SESSION['password'],$_SESSION['restoreDate'],$path);
        if ($rdate == '0'){
            echo "c|..";
            exit;
        }
        $file_list = unserialize($rdate);
        $i = count($file_list) - 1; 
        $out_put1 = "";
        $out_put2 = "";        
        while($i >= 0){
            $file = unserialize($file_list[$i--]);
               $type = $file[0];
               $name = $file[1];

               if ($name != "." && $name != ".."){
                   if ($type == "d")
                       $out_put1 .= "$type|$name\n";
                   else  
                       $out_put2 .= "$type|$name\n";
               }
        }
        $out_put = $out_put1.$out_put2;
        if (strlen($out_put) != 0)
            $out_put = "\n".substr($out_put,0,strlen($out_put) - 1);
        
        echo "c|..$out_put";
    }
    
    private function load_db($path){
        if (strlen($path) == 0 || $path == "/"){
            $ws = WSDL_local::singleton();
            $rdate = $ws->Backup()->getMysqlBase($_SESSION['customerName'],$_SESSION['password'],$_SESSION['restoreDate']);
            if ($rdate == '0'){
                echo "c|..";
                exit;
            }
            $file_list = unserialize($rdate);
            $i = count($file_list) - 1; 
            $out_put = "";
            while($i >= 0){
                $file = $file_list[$i--];
                   $type = 'b';
                   $name = $file;

                   if ($name != "." && $name != "..")
                       $out_put .= "$type|$name\n";
            }
            if (strlen($out_put) != 0)
                $out_put = "\n".substr($out_put,0,strlen($out_put) - 1);
            echo "c|..$out_put";
        } else {
            if (substr($path,0,1) == "/")
                $path = substr($path,1,strlen($path)-1);
     
            $ws = WSDL_local::singleton();
            $rdate = $ws->Backup()->getMysqlTable($_SESSION['customerName'],$_SESSION['password'],$_SESSION['restoreDate'],$path);
            if ($rdate == '0'){
                echo "c|..";
                exit;
            }
            $file_list = unserialize($rdate);
            $i = count($file_list) - 1; 
            $out_put = "";
            while($i >= 0){
                $file = $file_list[$i--];
                   $type = 't';
                   $name = $file;

                   if ($name != "." && $name != "..")
                       $out_put .= "$type|$name\n";
            }
            if (strlen($out_put) != 0)
                $out_put = "\n".substr($out_put,0,strlen($out_put) - 1);
            echo "b|..$out_put";
        }
    }
    
    private function ajax_load_file_list(){
        $path  = $this->in_mass['p0'];
        $types = $_SESSION['restoreType'];
            
        if ( $types == 'files')
            $this->load_file_list($path);
        elseif ( $types == 'mysql' )
            $this->load_db($path);
    }
    
    private function ajax_set_date(){
        $date = $this->in_mass['p0'];
        $_SESSION['restoreDate'] = $date; 
    }
    
    private function ajax_set_type(){ 
        $types = $this->in_mass['p0'];
        $_SESSION['restoreType'] = $types;
    }
    
    private function sendMail($from, $to, $subject, $message)
    {
        $subject = "=?windows-1251?b?" . base64_encode($subject) . "?=";
        $headers = 
        'From: '. $from . "\r\n" .
        'Content-type: text/plain; charset="windows-1251"\r\n'.
        "Subject:  $subject \r\n" .
        'Reply-To:' . $from . "\r\n" .
        'X-Mailer: PHP/' . phpversion() . '\r\n';
        
        mail($to,$subject,$message,$headers);
    }
    
    private function createMessage($path)
    {
        $login    = $_SESSION['customerName'];
        $types    = $_SESSION['restoreType'];
        $date     = $_SESSION['restoreDate'];
        $message =
        "Здравствуйте.\n\n".
        "Принята заявка на восстановление данных из бэкапа.\n\n".
        "Имя Аккаунта:         $login\n".
        "Тип данных:           $types\n".
        "Дата восстановления:  $date\n".
        "Путь до данных:       $path\n\n".
        "Приблизительное время восстановления от 10 до 30 минут в зависимости\n".
        "от размера восстанавлеваямых данных.\n\n".
        "--\n".
        "Служба технической поддержки BeGet\n".
        "Phone: 8 (812) 953-68-28\n".
        "ICQ:   963141";
        
        return $message;
    }
    
    private function ajax_restore(){
        $path    = $this->in_mass['p0'];
        $message = $this->createMessage($path);
        $cont_mail= $_SESSION['cont_mail'];
        
        $this->sendMail('support@beget.ru',$cont_mail,"Заявка на восстановление данных",$message);
        $this->sendMail($cont_mail,'support@beget.ru',"Заявка на восстановление данных",$message);
    }
    
    private function getMainPage($alert){
        global $db;
        
        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("backup.tpl.html", true, true);

        $this->_ajaxFunction($tpl);
        
        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");

        $tpl->setCurrentBlock("DOCUMENT");
        $tpl->setVariable("START","");
        
        $ws = WSDL_local::singleton();
        $have_backup = unserialize( $ws->Backup()->getDate($_SESSION['customerName'],$_SESSION['password']) );
        $i = count($have_backup) - 1; 
        while($i >= 0){
                $tpl->setCurrentBlock("SELECT_DATE");
                $tpl->setVariable("DATE",$have_backup[$i]);
                $year   = substr($have_backup[$i],0,4);
                $mounth = substr($have_backup[$i],4,2);
                $day    = substr($have_backup[$i],6,2);
                $tpl->setVariable("DATESEE","$year-$mounth-$day");
                if ($i == (count($have_backup) - 1)){
                    $tpl->setVariable("DATESTATUS","SELECTED");
                    $_SESSION['restoreDate'] = $have_backup[$i];
                }
                $i--;
                $tpl->parse("SELECT_DATE");
        }
        $_SESSION['restoreType'] = 'files';
        $tpl->setVariable("ALERT",$alert);
        $tpl->parse("DOCUMENT");
        $tpl->show();
    }
    
    public function getDate(){
        //print_r($this->in_mass);
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
