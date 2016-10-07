<?php

require_once 'HTML/Template/IT.php';
require_once 'config/db_init.inc';
require_once 'config/cp_global_php5.php';
require_once 'config/globals_ws_client.php';
require_once 'config/WSDL.php';
require_once 'setSession.php';

class NewDomains_ {
    protected $in_mass;

    private $err_tld_not_found    = "<script>alert('Данная зона не поддерживается.');</script>";
    private $err_domain_alnum     = "<script>alert('Домен может состоять из цифр, букв латинского алфавита и символа подчеркивания');</script>";
    private $err_external         = "<script>alert('Внутренняя ошибка сервера.');</script>";
    private $err_fqdn_exist       = "<script>alert('Данный домен уже присутствует на NS серверах компании BeGet');</script>";
    private $ok_move_add          = "<script>alert('Заявка на регистрацию домена принята.');</script>";
    private $ok_delete            = "<script>alert('Домен успешно удален.');</script>";

    public function  __construct($post,$get){
        $this->in_mass   = $post;
        $this->ajax_mass = $get;

        $this->ajax_fpar['ajax_check_domain'] = '2';

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

    private function ajax_check_domain(){
        global $db;
        $domain  = $this->in_mass['p0'];
        $tld  = $this->in_mass['p1'];
        $tld = $db->getOne("SELECT zone FROM tld WHERE id = '$tld'");
        $whoIs = new WhoIs_($domain.$tld);

        if ($whoIs->getBoolFreeDomain()){
            echo '1';
        } else {
            echo '0';
        }
    }

    private function dropDomain(){
        $ws = WSDL_local::singleton();
        if ( !$ws->Domains()->dropDomain($_SESSION['customerName'],$_SESSION['password'],$this->in_mass['id']) )
             $this->getMainPage($this->err_external);
         else
             $this->getMainPage($this->ok_delete);
    }

    private function regDomain(){
        global $db;
        $domain = $this->in_mass['domain'];
        $tld    = $this->in_mass['tld'];
        $person_id    = $this->in_mass['id'];

        echo " $domain - $tld - $person_id ";

        if (!ereg("[[:alnum:]]",$domain) || ereg("\.",$domain)){
           $this->getMainPage($this->err_domain_alnum);
           exit;
        }
        

        $tld_count = $db->getOne("SELECT count(*) FROM tld WHERE id = '$tld'");
        if ($tld_count == 0){
           $this->getMainPage($this->err_tld_not_found);
           exit;
        }
        
        $tld = $db->getOne("SELECT zone FROM tld WHERE id = '$tld'");

        if(ereg("www\.", $domain))
                    $domain = substr($domain, 4);

        $domain_count = $db->getOne("SELECT count(*) FROM domains WHERE fqdn='$domain$tld'");
        if ($domain_count != 0){
           $this->getMainPage($this->err_fqdn_exist);
           exit;
        }

        $ws = WSDL_local::singleton();
        
        if (!$ws->Domains()->addNewDomain($_SESSION['customerName'],$_SESSION['password'],$this->in_mass['domain'],$tld,'reg',$person_id)){
           $this->getMainPage($this->err_external);
           exit;
        }
        
        
        $this->getMainPage($this->ok_move_add);
    }

    private function createNewPerson(){
        global $db;
        $cust_id     = $_SESSION['customerId'];
        $type        = isset($this->in_mass['type'])?$this->in_mass['type']:0;
        $id          = isset($this->in_mass['id'])?$this->in_mass['id']:'-1';

        if ($type == 'org'){
            $company     = isset($this->in_mass['company'])?$this->in_mass['company']:'';
            $fio_english = isset($this->in_mass['company_eng'])?$this->in_mass['company_eng']:'';
            $tel         = isset($this->in_mass['o_tel'])?$this->in_mass['o_tel']:'';
            $index       = isset($this->in_mass['o_index'])?$this->in_mass['o_index']:'';
            $adress      = isset($this->in_mass['o_phis_address'])?$this->in_mass['o_phis_address']:'';
            $email       = isset($this->in_mass['o_email'])?$this->in_mass['o_email']:'';
            $inn         = isset($this->in_mass['inn'])?$this->in_mass['inn']:'';
            $jur_addr    = isset($this->in_mass['jur_addr'])?$this->in_mass['jur_addr']:'';

            if ($id == '-1' || $id == ''){
                $db->query("INSERT INTO domain_person(cust_id,`type`,company,fio_english,tel,`index`,adress,email,inn,jur_addr,time_create)
                 VALUES('$cust_id','$type','$company','$fio_english','$tel','$index','$adress','$email','$inn','$jur_addr',NOW())");
            } else {
                $db->query("UPDATE domain_person SET company = '$company', fio_english ='$fio_english', tel = '$tel', `index` = '$index',
                adress = '$adress', email= '$email', inn='$inn', jur_addr = '$jur_addr' WHERE cust_id = '$cust_id' AND id = '$id'");
            }

        } elseif ($type = 'person'){
            $family      = isset($this->in_mass['family'])?$this->in_mass['family']:'';
            $name        = isset($this->in_mass['name'])?$this->in_mass['name']:'';
            $patronymic  = isset($this->in_mass['patronymic'])?$this->in_mass['patronymic']:'';
            $fio_english = isset($this->in_mass['fio_english'])?$this->in_mass['fio_english']:'';
            $tel         = isset($this->in_mass['tel'])?$this->in_mass['tel']:'';
            $pa_address  = isset($this->in_mass['pp_address'])?$this->in_mass['pp_address']:'';
            $pp_series   = isset($this->in_mass['pp_series'])?$this->in_mass['pp_series']:'';
            $pp_num      = isset($this->in_mass['pp_num'])?$this->in_mass['pp_num']:'';
            $pp_date     = isset($this->in_mass['pp_date'])?$this->in_mass['pp_date']:'';
            $birth_date  = isset($this->in_mass['birth_date'])?$this->in_mass['birth_date']:'';
            $index       = isset($this->in_mass['index'])?$this->in_mass['index']:'';
            $adress      = isset($this->in_mass['adress'])?$this->in_mass['adress']:'';
            $email       = isset($this->in_mass['email'])?$this->in_mass['email']:'';

            if ($id == '-1' || $id == ''){
                $db->query("INSERT INTO domain_person(cust_id,`type`,family,name,patronymic,fio_english,tel,pa_address,pp_series,
                pp_num,pp_date,birth_date,`index`,adress,email,time_create) VALUES('$cust_id','$type','$family','$name','$patronymic','$fio_english','$tel',
                '$pa_address','$pp_series','$pp_num','$pp_date','$birth_date','$index','$adress','$email',NOW())");
            } else {
                $db->query("UPDATE domain_person SET family='$family', name='$name', patronymic='$patronymic',
                fio_english='$fio_english',tel='$tel',pa_address='$pa_address',pp_series='$pp_series',
                pp_num='$pp_num',pp_date='$pp_date',birth_date='$birth_date',`index`='$index',adress='$adress',
                email='$email' WHERE cust_id = '$cust_id' AND id = '$id'");
            }
        }
        // TODO: Заменить на функции без записи в Базу

    }

    private function getMainPage($alert){
        global $db;

        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("newdomains.tpl.html", true, true);

        $this->_ajaxFunction($tpl);

        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");

        $tpl->setCurrentBlock("DOCUMENT");
        $tpl->setVariable("START","");

        $rs  = $db->query("SELECT id,zone,price FROM tld");
        while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT)){
            if ($rw->price >= 0){
                $tpl->setCurrentBlock("SELECT_ZONE_NAME");
                $tpl->setVariable("ID",$rw->id);
                $tpl->setVariable("ZONE",$rw->zone." | ".$rw->price." руб.");
                $tpl->parse("SELECT_ZONE_NAME");
            }
        }

        $clt_id = $_SESSION['customerId'];
        $rs = $db->query("SELECT id, type, company, family, name, patronymic FROM domain_person WHERE cust_id = '$clt_id'");
        if ($rs->numRows() == 0)
        {
            $tpl->setCurrentBlock('PERSON_DOMAIN');
            $tpl->setVariable('ID','-1');
            $tpl->setVariable('NAME','Create new person');
            $tpl->parse('PERSON_DOMAIN');
        } else
        while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT)){
            $tpl->setCurrentBlock('PERSON_DOMAIN');
            $name = '';
            if ($rw->type == 'org'){
                $name = $rw->company;
            } else {
                $name = $rw->name." ".$rw->patronymic." ".$rw->family;
            }
            $tpl->setVariable('ID',$rw->id);
            $tpl->setVariable('NAME',$name);
            $tpl->parse('PERSON_DOMAIN');
        }

        $rs  = $db->query("SELECT fqdn,date_registry FROM domains WHERE subdomain IS NULL AND cust_id = '$clt_id' ORDER BY date_registry");
        while ($rw = $rs->fetchRow(DB_FETCHMODE_OBJECT)){
            $tpl->setCurrentBlock("DOMAINS");
            $tpl->setVariable("NAME",$rw->fqdn);
            $tpl->setVariable("DATE",$rw->date_registry);
            $tpl->parse("DOMAINS");
        }

        $tpl->setVariable("ALERT",$alert);
        $tpl->parse("DOCUMENT");
        $tpl->show();
    }

    private function createPerson($alert){
        global $db;

        $tpldir = "templates";
        $tpl = new HTML_Template_IT($tpldir);
        $tpl->loadTemplatefile("newperson.tpl.html", true, true);

        $this->_ajaxFunction($tpl);

        $tpl->setCurrentBlock("TOP");
        $top_table = new Header_($_SESSION['cust_id'],$tpldir);
        $tpl->setVariable("TEXT_TOP",$top_table->get());
        $tpl->parse("TOP");

        $tpl->setCurrentBlock("DOCUMENT");
        $tpl->setVariable("START","");


        $id = isset($this->in_mass['person'])?$this->in_mass['person']:0;

        if ($id == 0){
            $tpl->setCurrentBlock('PERSON_TYPE');
            $tpl->setVariable('NULL','');
            $tpl->parse('PERSON_TYPE');
            $tpl->setCurrentBlock('PERSON_DATE');
            $tpl->setVariable('FAMILY','');
            $tpl->parse('PERSON_DATE');
        } else {
            $clt_id = $_SESSION['customerId'];
            $rs  = $db->query("SELECT * FROM domain_person WHERE id = '$id' AND cust_id = '$clt_id'");
            if ($rs->numRows() != 1)
            {
                $tpl->setCurrentBlock('PERSON_TYPE');
                $tpl->setVariable('NULL','');
                $tpl->parse('PERSON_TYPE');
                $tpl->setCurrentBlock('PERSON_DATE');
                $tpl->setVariable('ID','-1');
                $tpl->setVariable('FAMILY','');
                $tpl->parse('PERSON_DATE');
            } else {
                $rw = $rs->fetchRow(DB_FETCHMODE_OBJECT);
                if ($rw->type == 'person')
                {
                    $tpl->setCurrentBlock('PERSON');
                    $tpl->setVariable('NULL',' ');
                    $tpl->parse('PERSON');
                    $tpl->setCurrentBlock('PERSON_DATE');
                    $tpl->setVariable('ID',$id);
                    $tpl->setVariable('FAMILY',$rw->family);
                    $tpl->setVariable('NAME',$rw->name);
                    $tpl->setVariable('PATRONYMIC',$rw->patronymic);
                    $tpl->setVariable('FIO_ENGLISH',$rw->fio_english);
                    $tpl->setVariable('PP_SERIES',$rw->pp_series);
                    $tpl->setVariable('PP_NUM',$rw->pp_num);
                    $tpl->setVariable('PP_DATE',$rw->pp_date);
                    $tpl->setVariable('PA_ADDRESS',$rw->pa_address);
                    $tpl->setVariable('BIRTH_DATE',$rw->birth_date);
                    $tpl->setVariable('INDEX',$rw->index);
                    $tpl->setVariable('ADRESS',$rw->adress);
                    $tpl->setVariable('TEL',$rw->tel);
                    $tpl->setVariable('EMAIL',$rw->email);
                    $tpl->parse('PERSON_DATE');
                } elseif($rw->type == 'org') {
                    $tpl->setCurrentBlock('ORG');
                    $tpl->setVariable('NULL',' ');
                    $tpl->parse('ORG');
                    $tpl->setCurrentBlock('PERSON_DATE');
                    $tpl->setVariable('ID',$id);
                    $tpl->setVariable('COMPANY',$rw->company);
                    $tpl->setVariable('FIO_ENGLISH',$rw->fio_english);
                    $tpl->setVariable('INN',$rw->inn);
                    $tpl->setVariable('JUR_ADDR',$rw->jur_addr);
                    $tpl->setVariable('INDEX',$rw->index);
                    $tpl->setVariable('ADDRESS',$rw->adress);
                    $tpl->setVariable('PHONE',$rw->tel);
                    $tpl->setVariable('EMAIL',$rw->email);
                    $tpl->parse('PERSON_DATE');

                }
            }
        }
        $tpl->setVariable("ALERT",$alert);
        $tpl->parse("DOCUMENT");
        $tpl->show();
    }

    private function getPostPage(){
        if (isset($this->in_mass['actions'])){
             switch ($this->in_mass['actions']){
                 case 'createPerson':

                                 $this->createPerson("");
                                 break;
                 case 'dropDomain':
                                 $this->dropDomain();
                                 break;
                 case 'doCreatePerson':
                                 $this->createNewPerson();
                                 $this->getMainPage("");
                                 break;
                 case 'regDomain':
                                 $this->regDomain();
                                 break;
                 default:        $this->getMainPage("");
             }
         } else
             $this->getMainPage("");
    }

    public function getDate(){
        if (isset($this->ajax_mass['ajax'])){
            if (substr($this->ajax_mass['method'],0,4) == "ajax")
                if (method_exists($this, $this->ajax_mass['method']))
                    call_user_func(array($this, $this->ajax_mass['method']));
        } else {
             $this->getPostPage();
        }
     }
}
?>
