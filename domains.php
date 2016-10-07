<?php
  require_once 'security.php';
  session_name("BegetCp");
  session_start();
  $date = new Domains_($_POST,$_GET,'domains.tpl.html');
  $date->getdate();
?>