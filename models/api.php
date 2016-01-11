<?php
  class Api {
    // we define 3 attributes
    // they are public so that we can access them using $api->author directly
    public $id;
    public $author;
    public $content;

    public function __construct($id, $author, $content) {
      $this->id      = $id;
      $this->author  = $author;
      $this->content = $content;
    }

    public static function all() {
      $list = array();
      $db = Database::getInstance();      
      
      $req = $db->query('SELECT * FROM users');

      // we create a list of Api objects from the database results
      foreach($req->fetchAll() as $api) {
        $list[] = new Api($api['id'],$api['firstname'], $api['lastname'], $api['email']);
      }

      return $list;
    }

    public static function find($id) {
      $db = Database::getInstance();
      // we make sure $id is an integer
      $id = intval($id);
      $req = $db->prepare('SELECT * FROM users WHERE id = :id');
      // the query was prepared, now we replace :id with our actual $id value
      $req->execute(array('id' => $id));
      $api = $req->fetch();

      return new Api($api['id'], $api['firstname'], $api['lastname'], $api['email']);
    }

     public static function login($phonenumber, $password) {
      $db = Database::getInstance();

      $req = $db->prepare('SELECT * FROM users WHERE phonenumber = :phonenumber and password = :password');
      // the query was prepared, now we replace :id with our actual $id value
      $req->execute(array('phonenumber' => $phonenumber, 'password' => $password));
      $api = $req->fetch();

      return new Api($api['id'], $api['firstname'], $api['lastname'], $api['email']);
    }
  }
?>