<?php
  require_once 'security.php';
  session_name("BegetCp");
  session_start();
  $date = new Sites_($_POST,$_GET,"sites.tpl.html");
  $date->getdate();
?>