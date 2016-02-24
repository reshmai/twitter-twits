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
<<<<<<< HEAD
        self::$instance = new PDO('mysql:host=localhost;dbname=refer', 'root', 'clarion', $pdo_options);
=======
        self::$instance = new PDO('mysql:host=localhost;dbname=refer', 'root', 'poiuytrewq', $pdo_options);
>>>>>>> 664d99d1a457edb046edc294f4f54260278744ae
      }
      return self::$instance;
    }

}
?>