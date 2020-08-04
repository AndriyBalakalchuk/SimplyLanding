<?php
  $cookie_name = "Language";
  if(!isset($_COOKIE[$cookie_name]) or $_COOKIE[$cookie_name] == ''){
    $cookie_value = "_en";
  }else{
    $cookie_value = "";
  }
  setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
  if($_SERVER['HTTP_REFERER']=='' or !isset($_SERVER['HTTP_REFERER'])){
    header("Location: ../index.php");exit;
  }
  header("Location: {$_SERVER['HTTP_REFERER']}");exit;