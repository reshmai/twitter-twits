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
        
        define('DB_HOST', getenv('OPENSHIFT_MYSQL_DB_HOST'));
        define('DB_PORT',getenv('OPENSHIFT_MYSQL_DB_PORT')); 
        define('DB_USER',getenv('OPENSHIFT_MYSQL_DB_USERNAME'));
        define('DB_PASS',getenv('OPENSHIFT_MYSQL_DB_PASSWORD'));
        define('DB_NAME',getenv('OPENSHIFT_GEAR_NAME'));

        $dsn = 'mysql:dbname='.DB_NAME.';host='.DB_HOST.';port='.DB_PORT;
        self::$instance = new PDO($dsn, DB_USER, DB_PASS);
        
        //self::$instance = new PDO('mysql:host=localhost;dbname=refer', 'root', 'clarion', $pdo_options);

      }
      return self::$instance;
    }

}
?>