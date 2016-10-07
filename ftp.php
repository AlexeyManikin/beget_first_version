<?php
  require_once 'security.php';
  session_name("BegetCp");
  session_start();

  $date = new Ftp_($_POST,$_GET,"ftp.tpl.html");
  $date->getDate();
?>