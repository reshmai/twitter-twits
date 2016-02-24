<?php
/*
* Mysql database class - only one connection alowed
*/

class Database {
	private static $instance = NULL;

    private function __construct() {}

    private function __clone() {}

    public static function getInstance() {
      if (array_key_exists('OPENSHIFT_APP_NAME', $_SERVER)) {
        $src = $_SERVER;
      } else {
        $src = $_ENV;
      }
      if (!isset(self::$instance)) {
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        self::$instance = new PDO('mysql:host='.$src['OPENSHIFT_MYSQL_DB_HOST'].';dbname='.$src['OPENSHIFT_APP_NAME'], $src['OPENSHIFT_MYSQL_DB_USERNAME'], $src['OPENSHIFT_MYSQL_DB_PASSWORD'], $pdo_options);
      }
      return self::$instance;
    }

}
?>