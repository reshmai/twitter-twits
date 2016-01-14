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

      return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
      $db = Database::getInstance();
      // we make sure $id is an integer
      $id = intval($id);
      $req = $db->prepare('SELECT * FROM users WHERE id = :id');
      // the query was prepared, now we replace :id with our actual $id value
      $req->execute(array('id' => $id));
      $api = $req->fetch();

      return new Api($api['id'], $api['name'], $api['email']);
    }

     public static function login($phonenumber, $password) {
      $db = Database::getInstance();

      $req = $db->prepare('SELECT * FROM users WHERE phone_number = :phone_number and password = :password');
      // the query was prepared, now we replace :id with our actual $id value
      $req->execute(array('phone_number' => $phonenumber, 'password' => $password));
      $api = $req->fetch();

      return new Api($api['id'], $api['name'], $api['email']);
    }

    public static function signup($data){
      /*File upload $_FILES
      Array
      (
          [fileToUpload] => Array
              (
                  [name] => TaxCertificateHome14to15.pdf
                  [type] => application/pdf
                  [tmp_name] => /tmp/phpHlDfcs
                  [error] => 0
                  [size] => 74912
              )

      )*/
      $return = array();
      $message = '';
      $db = Database::getInstance();

      $stmt = $db->prepare("INSERT INTO users (fb_id, name, email, phone_number, alternate_phone_number, location, designation, experience_year, experience_month, willing_to_relocate, refer_me, created, modified) 
      VALUES (:fb_id, :name, :email, :phone_number, :alternate_phone_number, :location, :designation, :experience_year, :experience_month, :willing_to_relocate, :refer_me, :created, :modified)");
      $stmt->bindParam(':fb_id', $data['fb_id']);
      $stmt->bindParam(':name', $data['name']);
      $stmt->bindParam(':email', $data['email']);
      $stmt->bindParam(':phone_number', $data['phone_number']);
      $stmt->bindParam(':alternate_phone_number', $data['alternate_phone_number']);
      $stmt->bindParam(':location', $data['location']);
      $stmt->bindParam(':designation', $data['designation']);
      $stmt->bindParam(':experience_year', $data['experience_year']);
      $stmt->bindParam(':experience_month', $data['experience_month']);
      $stmt->bindParam(':willing_to_relocate', $data['willing_to_relocate']);
      $stmt->bindParam(':refer_me', $data['refer_me']);
      $stmt->bindParam(':created', date("Y-m-d H:i:s"));
      $stmt->bindParam(':modified', date("Y-m-d H:i:s"));    
      $stmt->execute();

      // $aid = $db->prepare("select * from users"); 
      // $aid->execute(); 
      // $row = $aid->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);

      // print_R($row);die;

      $stmt_last = $db->prepare("SELECT * FROM users ORDER BY id DESC LIMIT 10"); 
      $stmt_last->execute(); 
      $lastRow = $stmt_last->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);

      if(!empty($lastRow)){
          //UPLOAD resume
          $uploaddir = '/upload/';
          $uploadfile = $uploaddir . basename($data['fileToUpload']['name']);

          if (move_uploaded_file($data['fileToUpload']['tmp_name'], $uploadfile)) {
              $message .= "File is valid, and was successfully uploaded.\n";
          } else {
              $message .= "Possible file upload attack!\n";
          }

          $stmt_resume = $db->prepare("INSERT INTO resumes(uid, filename, title, created, modified) 
          VALUES (:uid, :filename, :title, :created, :modified)");
          $stmt_resume->bindParam(':uid', $lastRow['id']);
          $stmt_resume->bindParam(':filename', $uploadfile);
          $stmt_resume->bindParam(':title', $data['fileToUpload']['name']);
          $stmt_resume->bindParam(':created', date("Y-m-d H:i:s"));
          $stmt_resume->bindParam(':modified', date("Y-m-d H:i:s"));
          $stmt_resume = $stmt_resume->execute();
      }
      

      if($stmt_user==1){
        return $return[$message];
      }else{
        return 0;
      }


    }
  }
?>