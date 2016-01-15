<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('PUBLIC_PATH',dirname(realpath(__FILE__)) . "/");
define('BASE_PATH',dirname(PUBLIC_PATH));
define('FILES_PATH',PUBLIC_PATH . "/upload/");
define('LIB_PATH',BASE_PATH . "/lib/");
require_once('config/config.php');

if (isset($_GET['controller']) && isset($_GET['action'])) {
    $controller = $_GET['controller'];
    $action     = $_GET['action'];
  } else {
    $controller = 'pages';
    $action     = 'home';
  }

  require_once('views/layout.php');
?>