<?php
  require_once 'security.php';
  session_name("BegetCp");
  session_start();
  if (notAuthorized()){
      header("Location: $base_url");
      exit;
  }
  $date = new DNS_($_POST,$_GET);
  $date->getdate();
?>