<?php
  require_once 'security.php';
  session_name("BegetCp");
  session_start();

  $date = new MySQL_($_POST,$_GET,"mysql.tpl.html");
  $date->getdate();
?>