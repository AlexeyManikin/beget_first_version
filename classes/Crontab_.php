<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL_local.php';
require_once 'setSession.php';

class Crontab_ {
    protected $in_mass;

    private $err_format     = "<script>alert('Неверный формат параметров');</script>";
    private $add_job_ok     = "<script>alert('Задание успешно добавленно');</script>";
    private $add_mail_ok    = "<script>alert('Почтовый ящик успешно сменен');</script>";
    private $delete_job_ok  = "<script>alert('Задание успешно удалено');</script>";
    private $change_ok      = "<script>alert('Задание успешно изменено');</script>";
    private $job_err        = "<script>alert('Внутреннея ошибка сервера обратитесь в службу технической поддержки');</script>";
    private $err_format_mail = "<script>alert('Неверный формат почтового ящика');</script>";

    public function  __construct($post){
        $this->in_mass = $post;
    }

    private function testRow(){
        $patern = "/[\* | [[[0-9]|[\,]]*[0-9]] | [\*\/[0-9]+]  ]\s*/i";
        if (!preg_match($patern,$this->in_mass['minutes']) || !preg_match($patern,$this->in_mass['hours'])
            || !preg_match($patern,$this->in_mass['days']) || !preg_match($patern,$this->in_mass['monthes'])
            || !preg_match($patern,$this->in_mass['dows']))
            return false;
        $this->in_mass['commands'] = stripslashes($this->in_mass['commands']);
        return true;
    }

    private function setMailTo(){
        $is_ok = preg_match('/^[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?\.[A-Za-z0-9]{2,6}\s*$/', $this->in_mass['mail']);
        if (strlen($this->in_mass['mail']) != 0 && !$is_ok){
            $this->getMainPage($this->err_format_mail);
            exit;
        }
        $ws = WSDL_local::singleton();
        if( $ws->Crontab()->setMailTo($_SESSION['customerName'],$_SESSION['password'],$this->in_mass['mail']) )
            $this->getMainPage($this->add_mail_ok);
        else
            $this->getMainPage($this->job_err);
    }

    private function sd($str)  {
          $replace = array("\&","\'","<",">");
          $search= array("'&amp;'","'&quot;'","'&lt;'","'&gt;'");
          $str = preg_replace($search,$replace,$str);
          return $str;
    }

    private function addEntry(){
        if (!$this->testRow()){
            $this->getMainPage($this->err_format);
            exit;
        }
        $job = sprintf("%s %s %s %s %s %s",$this->in_mass['minutes'],$this->in_mass['hours'],$this->in_mass['days'],$this->in_mass['monthes'],$this->in_mass['dows'],$this->sd(urldecode($this->in_mass['commands'])));
        $ws = WSDL_local::singleton();
        if ( $ws->Crontab()->addEntry($_SESSION['customerName'],$_SESSION['password'],$job))
            $this->getMainPage($this->add_job_ok);
        else
            $this->getMainPage($this->job_err);
    }

    private function removeEntry(){
        $ws = WSDL_local::singleton();
        $cron_table = unserialize($ws->Crontab()->listCrontabArray($_SESSION['customerName'],$_SESSION['password']));
        $i = count($cron_table)-1-((int) $this->in_mass['number']);
        $cron_date = unserialize($cron_table[$i]);
        $command = unserialize($cron_date[5]);
        $ii = 0;
        $commandLine = "";
        while($ii <= (count($command)-1)){
            $commandLine .=  chr($command[$ii++]);
        }
        $job = sprintf("%s %s %s %s %s %s",$cron_date[0],$cron_date[1],$cron_date[2],$cron_date[3],$cron_date[4],$commandLine);

        if ($ws->Crontab()->removeEntry($_SESSION['customerName'],$_SESSION['password'],$job))
            $this->getMainPage($this->delete_job_ok);
        else
            $this->getMainPage($this->job_err);
    }

    private function changeEntry(){
        if (!$this->testRow()){
            $this->getMainPage($this->err_format);
            exit;
        }

        $ws = WSDL_local::singleton();
         $cron_table = unserialize($ws->Crontab()->listCrontabArray($_SESSION['customerName'],$_SESSION['password']));
        $i = count($cron_table)-1-( (int) $this->in_mass['number']);
        $cron_date = unserialize($cron_table[$i]);
        $command = unserialize($cron_date[5]);
        $ii = 0;
        $commandLine = "";
        while($ii <= (count($command)-1)){
            $commandLine .=  chr($command[$ii++]);
        }
        $job_old = sprintf("%s %s %s %s %s %s",$cron_date[0],$cron_date[1],$cron_date[2],$cron_date[3],$cron_date[4],$commandLine);
        $job = sprintf("%s %s %s %s %s %s",$this->in_mass['minutes'],$this->in_mass['hours'],$this->in_mass['days'],$this->in_mass['monthes'],$this->in_mass['dows'],$this->sd(urldecode($this->in_mass['commands'])));

        if ($ws->Crontab()->changeEntry($_SESSION['customerName'],$_SESSION['password'],$job_old, $job))
            $this->getMainPage($this->change_ok);
        else
            $this->getMainPage($this->job_err);
    }

    private function getMainPage($alert){
        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("crontab.tpl.html", true, true);
        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");
        $tpl->setCurrentBlock("DOCUMENT");
        $ws = WSDL_local::singleton();

        $cron_table = unserialize($ws->Crontab()->listCrontabArray($_SESSION['customerName'],$_SESSION['password']));

        $mail_to = $ws->Crontab()->getMailTo($_SESSION['customerName'],$_SESSION['password']);
        $tpl->setVariable("MAILTO",$mail_to);
        $i = count($cron_table)-1;
        $j = 0;
        while($i >= 0){
            $cron_date = unserialize($cron_table[$i--]);
            $command = unserialize($cron_date[5]);
            $ii = 0;
            $commandLine = "";
            while($ii <= (count($command)-1)){
                $commandLine .=  chr($command[$ii++]);
            }
            $tpl->setCurrentBlock("CRONTABLE");
            $tpl->setVariable("MINUTES",$cron_date[0]);
            $tpl->setVariable("HOURS",$cron_date[1]);
            $tpl->setVariable("DAYS",$cron_date[2]);
            $tpl->setVariable("MONTHES",$cron_date[3]);
            $tpl->setVariable("DOWS",$cron_date[4]);
            $tpl->setVariable("COMMANDS",$commandLine);
            $tpl->setVariable("NUMBER",$j++);
            $tpl->parse("CRONTABLE");
        }
        $tpl->setVariable("ALERT",$alert);
        $tpl->parse("DOCUMENT");
        $tpl->show();
    }

    public function getDate(){
        //print_r($this->in_mass);
         if (isset($this->in_mass['actions'])){
             switch ($this->in_mass['actions']){
                 case 'addEntry':
                                 $this->addEntry();
                                 break;
                 case 'removeEntry':
                                 $this->removeEntry();
                                 break;
                 case 'setMailTo':
                                 $this->setMailTo();
                                 break;
                 case 'changeEntry':
                                 $this->changeEntry();
                                 break;
                 default:        $this->getMainPage("");
             }
         } else
             $this->getMainPage("");
     }
}
?>
