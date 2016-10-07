<?php

class WhoIs_ {

    protected $m_url;
    protected $m_bFreeDomain;
    protected $m_bValid;
    protected $m_strDomainDate;

    public function  __construct($url){
        $this->m_url = $url;
        $this->checkDomain($url);
    }

    protected function validURL($url){
        if (preg_match ("/^[a-z0-9][a-z0-9\-]+[a-z0-9](\.[a-z]{2,4})+$/i", $url))
            return true;
        return false;
    }

    protected function checkDomain($url){
        if ($this->validURL($url)){
            $this->m_bValid  = true;
            $returnDate = shell_exec ("whois ".$this->m_url);
            if (!preg_match("/No entries found for the selected source/i",$returnDate)){
                $this->m_bFreeDomain   = false;
                $this->m_strDomainDate = $returnDate;
            } else {
                $this->m_bFreeDomain   = true;
                $this->m_strDomainDate = '';
            }
        } else {
            $this->m_bValid        = false;
            $this->m_bFreeDomain   = false;
            $this->m_strDomainDate = '';
        }
    }

    public function setDomainName($url){
        $this->m_url = $url;
        $this->checkDomain($url);
    }

    public function getDomainName(){
        return $this->m_url;
    }

    public function getBoolFreeDomain(){
        return $this->m_bFreeDomain;
    }

    public function getDomainDate(){
        if ($this->m_bFreeDomain)
            return $this->m_strDomainDate;
        else
            return '';
    }

    public function getBoolValidUrl(){
        return $this->m_bValid;
    }

    public function getDomainDateHTML(){
        if (!$this->m_bFreeDomain){
            return preg_replace("/\n/", "<br> \n",$this->m_strDomainDate);
        }
        else
            return '';
    }
}

?>