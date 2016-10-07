<?php
ini_set('error_reporting', E_ALL);
class Customer_ extends DataCore {
    public       $data;
    protected $id;
    protected $customerId;
    protected $customers;
    protected $password;
    protected $dataObj;

    public function __construct($name) {
        parent::__construct();
        
        $this->customerId = $name;

        $customers = $this->getValue("customers","company,cont_fio,cont_mail,person_mail,type,tel,fax,nic_hdl","id='$this->customerId'",true,'obj');
        $this->addToObj($customers);

        $acc_status = $this->getValues("account_status","cust_id='$this->customerId'",'obj');
        $this->addToObj($acc_status);
        
        // wsdl
        //$this->data->ftp_num = parent::getValue("ftpusers","count(*)","customer_id='$this->customerId'");


        //$cust_ext = $this->getValue("customer_plan_ext","ext_id,ext_value","customer_id='$this->customerId'");
        //$rate_ext = 0; $this->data->have_ext = 0;
        
        //foreach ($cust_ext as $cust_ext_){
        //    $ext_id = $cust_ext_['ext_id']; $ext_value = $cust_ext_['ext_value'];
        //    $exts = $this->getValue("available_ext","ext_plan_name,price", "dealer_id='sweb' and id='$ext_id'",true,'obj');
        //    $ext_plan_name = $exts->ext_plan_name.'_ext';
        //    $rate_ext += ($exts->price*$ext_value);
        //    $this->data->$ext_plan_name = $ext_value;
        //    if($ext_value > 0){
        //    $this->data->have_ext++;
        //    $this->data->rate_ext = $rate_ext;
        //    }
        //}
        
        //$this->rate_ext = round($this->rate_ext,2);
        
        $this->data->server = strtoupper($this->data->server_name);
        $plans = $this->getValues("plans","id='".$this->data->plan_id."'",'obj');
        $this->addToObj($plans,'plan_');
        
        $this->data->customerName = parent::getValue("customers","cust_login","id='$this->customerId'");
        $this->data->customerId   = $this->customerId;
        
        $this->data->doc_root = '/home/'.substr($this->data->customerName,0,1).'/'.$this->data->customerName;
        
        if($this->data->type == 'org')
            $this->data->fio = $this->data->company;
        else
            $this->data->fio = $this->data->cont_fio;
        
        $this->data->mail = ($this->data->cont_mail == '' ? $this->data->person_mail : ($this->data->person_mail == '' ? $this->data->cont_mail : $this->data->cont_mail.', '.$this->data->person_mail));
           
        unset($this->data->company);
        unset($this->data->cont_fio);
        $this->setRate();
    }

    protected function setRate()
    {
    //        ÐÏÄÓÞÅÔ ËÏÌ×Ï ÄÎÅÊ ÄÏ ÂÌÏËÉÒÏ×ÁÎÉÑ
    //    $days = 0;
    //    if($this->data->rate > 0)
    //    {
    //        $days = $this->data->remainder/$this->data->rate;
    //        $days = round($days*30);
    //    }
    //    $this->data->days = ($days < 0 ? '0' : $days);
    }

    protected function getValues($table, $clause,$fethType = DB_FETCHMODE_ASSOC) 
    {
        $fethType == 'obj' ? $fethType = DB_FETCHMODE_OBJECT : '';
        $res = $this->db->query("select * from $table where $clause");
        if(DB::isError($res)) 
            throw new SoapFault(__METHOD__, $res->getMessage());
        return $res->fetchRow($fethType);
    }

    public function __get($n){
        return $this->data->$n;
    }

    function addToObj($what,$prefix = ''){

        is_object($this->data) == false ? $this->data = new stdClass : '';
        foreach ($what as $k=>$v){
            $k = ($prefix !== '' ? $prefix.$k : $k);
            $this->data->$k = $v;
        }
    }
    
    protected function getValue($table, $field, $clause,$just0 = false,$fethType = DB_FETCHMODE_ASSOC) 
    {
        $fethType == 'obj' ? $fethType = DB_FETCHMODE_OBJECT : '';
        $sql = "select $field from $table where $clause";
        $res = $this->db->query($sql);
        if(DB::isError($res)) 
            throw new SoapFault(__METHOD__, $res->getMessage());
        $array = array();
        if($fethType == DB_FETCHMODE_OBJECT)
            return $res->fetchRow($fethType);
        while($rw = $res->fetchRow($fethType)) 
        {
            array_push($array,$rw);
        }
        if($just0)
            return $array[0];
        return $array;
    }
}

?>
