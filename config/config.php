<?php
/*
* Mysql database class - only one connection alowed
*/
class Database {
	private static $instance = NULL;

    private function __construct() {}

    private function __clone() {}
 
    public static function getInstance() {
      if (!isset(self::$instance)) {
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        
        if (array_key_exists('OPENSHIFT_APP_NAME', $_SERVER)) {
          $src = $_SERVER;
        } else {
          $src = $_ENV;
        }
        $host = $src['OPENSHIFT_MYSQL_DB_HOST'];
        $dbname = $src['OPENSHIFT_APP_NAME'];
        $user = $src['OPENSHIFT_MYSQL_DB_USERNAME'];
        $pass = $src['OPENSHIFT_MYSQL_DB_PASSWORD'];
        self::$instance = new PDO('mysql:host=$host;dbname=$dbname', $user, $pass, $pdo_options);
        
        //self::$instance = new PDO('mysql:host=localhost;dbname=refer', 'root', 'clarion', $pdo_options);

      }
      return self::$instance;
    }

}
?>