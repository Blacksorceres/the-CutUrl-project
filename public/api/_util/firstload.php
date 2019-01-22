<?php

if (!(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' ||
   $_SERVER['HTTPS'] == 1) ||
   isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
   $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
{
   $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
   header('HTTP/1.1 301 Moved Permanently');
   header('Location: ' . $redirect);
   exit();
}

//START THE SESSION
if (session_status() === PHP_SESSION_NONE){
    session_start();
}

define('_NOWDATE', date("Y-m-d"));
define('_NOWTIME', date("h:i:sa"));

//SYSTEM SETTINGS
date_default_timezone_set("Europe/Ljubljana");
ini_set('default_charset', 'utf-8');
ini_set('upload_max_filesize', '5M');
//SET GLOBAL HEADERS
Header("Cache-Control: max-age=259200");
header('Content-Type: text/html; charset=utf-8');
