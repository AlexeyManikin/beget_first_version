<?    
class WSDL_local {

    private static $instance;
    public $wsdl;
    
    private function __construct() {}

    public static function singleton()
    {
        if (!isset(self::$instance))
	{
            $c = __CLASS__;
            self::$instance = new $c;
        }
        return self::$instance;
    }

    public function __call($method,$a)
    {    
        $this->wsdl = 'http://cp:123@localhost:7557/CpAgent/' . $method . '.pm';
        return new SoapClient(NULL, array_merge(array('location' => $this->wsdl), array('uri' => "http://localhost/CpAgent/$method", 'encoding' => 'cp1251')));
    }
}
?>