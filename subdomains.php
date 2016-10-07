<?php
  require_once 'security.php';
  session_name("BegetCp");
  session_start();
  $date = new SubDomains_($_POST,$_GET,"subdomains.tpl.html");
  $date->getdate();
?>