<?php
class Header_
{
    protected $tpl;

    function Header_($userid,$dir){ 
         $this->tpl = new HTML_Template_IT($dir);
         $this->tpl->loadTemplatefile("head.table.tpl.html", true, true);
         
         $this->tpl->setCurrentBlock("Documetn");
         $this->tpl->setVariable("START","");
         $this->tpl->parse("Document");
    }

    function get(){
        return $this->tpl->get();
    }
}
?>
