<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL.php';
require_once 'setSession.php';

class Support_ {
    protected $in_mass;

    public function  __construct($post){
        $this->in_mass = $post;
    }

    private function getMainPage($alert){
        global $db;

        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("support.tpl.html", true, true);

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
    
    private function sendMailfromSupport($to, $subject, $message)
    {
        $subject = "=?windows-1251?b?" . base64_encode($subject) . "?=";
        $headers = 
        'From: Support Beget.ru <support@beget.ru>' . "\r\n" .
        'Content-type: text/plain; charset="windows-1251"\r\n'.
        "Subject: $subject \r\n" .
        'Reply-To: Support Beget.ru <support@beget.ru>' . "\r\n" .
        'X-Mailer: PHP/' . phpversion() . '\r\n';
        
        mail($to,$subject,$message,$headers);
        mail('support@beget.ru',$subject,$message,$headers);
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

    private function sendRequest(){
        /*$db = getDBsite();*/

        $to       = $this->in_mass['select_service_name'];
        $subject  = $this->in_mass['subject'];
        $body     = $this->in_mass['body'];
        $cont_mail= $_SESSION['cont_mail'];
        $login    = $_SESSION['customerName'];
        $id       = $_SESSION['customerId'];
        $ip       = $_SERVER['REMOTE_ADDR'];
        $brouser  = $_SERVER['HTTP_USER_AGENT'];
        
        $message =
        "REMOTE_ADDRESS:  $ip \n".
        "HTTP_USER_AGENT: $brouser \n".
        "LOGIN:           $login \n".
        "TO:              $to \n\n".$body;
        
        $this->sendMail($cont_mail,'support@beget.ru'," Запрос с ПУ: ".$subject,$message);
        $this->getMainPage("<script>alert('Запрос отправлен, ответ придет на административный email ($cont_mail)');</script>");
    }

    public function getDate(){
        if (isset($this->in_mass['actions'])){
             switch ($this->in_mass['actions']){
                 case 'sendRequest':
                                 $this->sendRequest();
                                 break;
                 default:        $this->getMainPage("");
             }
         } else
             $this->getMainPage("");
     }
}
?>
