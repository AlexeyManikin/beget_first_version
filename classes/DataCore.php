<?

class DataCore extends DB {

    public $db;
    
    public function __construct() {
        $this->db = DB::Connect(DSN);
        if(DB::isError($this->db)) throw new SoapFault(__METHOD__, $this->db->getMessage());
        $this->db->query("set names cp1251");
    }

    protected function getValue($table, $field, $clause) {    
        $res = $this->db->query("select $field from $table where $clause");
        if(DB::isError($res)) throw new SoapFault(__METHOD__, $res->getMessage());
        
        $row = $res->fetchRow();
        return $row[0];
    }

    protected function getValues($table, $clause) {    
        $res = $this->db->query("select * from $table where $clause");
        if(DB::isError($res)) throw new SoapFault(__METHOD__, $res->getMessage());
        
        return $res->fetchRow(DB_FETCHMODE_OBJECT);
    }

    protected function makePersistent($table, $clause, $new) {
    
        $new  
        ? $res = $this->db->query("insert into $table set $clause")
        : $res = $this->db->query("update $table set $clause limit 1");
        
        if(DB::isError($res)) throw new SoapFault(__METHOD__, $res->getMessage());
        
        return (int)$this->db->getOne("select last_insert_id()");
    }

    protected function dropValue($table, $clause) {
        $res = $this->db->query("delete from $table where $clause limit 1");
        if(DB::isError($res)) throw new SoapFault(__METHOD__, $res->getMessage());
    }

    protected function getRowQuery($sql) {
        $res = $this->db->getRow($sql);
        if(DB::isError($res)) throw new Exception($res->getMessage());

        return $res;
    }

    protected function getAssocQuery($sql) {
        return $this->db->getAssoc($sql);
    }

    protected function getAllValues($table, $field, $clause,$just1 = false){
        $sql = "select $field from $table where $clause";
        $res = $this->db->query($sql);
        if(DB::isError($res)) throw new SoapFault(__METHOD__, $res->getMessage());
        $array = array();

        while($rw = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            array_push($array,$rw);
        }
        return ($just1 ? $array[0] : $array);
    }

    public function array_utf8_decode($a) {
        function utf8_dec(&$v, $k) {
            $v = iconv("utf-8","cp1251",$v);
        }
        array_walk($a, "utf8_dec");
        return $a;
    }
    
    public function disconnect() {
        $this->db->disconnect();
    }
}
?>
