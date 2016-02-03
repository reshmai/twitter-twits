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

      $return = 0;
      $currentDate = date("Y-m-d H:i:s");
      $db = Database::getInstance();

      if(isset($data['fb_id'])){

        $return = Api::addUser($data);

        $lastRow = Api::getLastInsertedRow();

        //Upload Resume code
        if(!empty($lastRow) && !empty($data['fileToUpload'])){
          $return = Api::uploadResume($data['fileToUpload']); 
        }

        //Insert technologies code
        if(!empty($data['technology'])){
          $return = Api::addTechnology($data['technology']);
        }          
        
      }else{
        $return = 0;
      }

      return $return;
    }

    public static function getUserByFacebookId($fb_id){

      $db = Database::getInstance();
      $userProfile = $db->prepare("SELECT * FROM users WHERE fb_id=:fb_id"); 
      
      $userProfile->bindParam(':fb_id', $fb_id);
      $userProfile->execute();             
      $userProfile = $userProfile->fetchAll(PDO::FETCH_ASSOC);
      
      return $userProfile;
    }

    public static function getLastInsertedRow(){
      $db = Database::getInstance();
      $stmt_last = $db->prepare("SELECT * FROM users ORDER BY id DESC LIMIT 10"); 
      $stmt_last->execute(); 
      $lastRow = $stmt_last->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
      return $lastRow;
    }

    public static function addTechnology($technology){

      $return = 0;
      $db = Database::getInstance();
      $sql_tech = 'INSERT INTO technology (name) VALUES ';
      $sql_tech_user = 'INSERT INTO user_technology (uid,name,created,modified) VALUES ';
            
      $existTechnologies = Api::checkTechnologyExist($technology);

      if (!empty($existTechnologies->prepareTechnologyQuery) && isset($existTechnologies->prepareTechnologyQuery)) {
        $sql_tech .= implode(', ', $existTechnologies->prepareTechnologyQuery);
        $stmt = $db->prepare($sql_tech);
        $stmt->execute($existTechnologies->prepareTechnologyData);
      }
      if(!empty($existTechnologies->userTechnologyqData)){
        $sql_tech_user .= implode(', ', $existTechnologies->userTechnologyqQuery);      
        $stmt_user_tech = $db->prepare($sql_tech_user);
        $stmt_user_tech->execute($existTechnologies->userTechnologyqData); 
        $return = 1;
      }

      return $return;
    }

    public static function checkTechnologyExist($technology){
      $technologyObj = new stdClass();
      $db = Database::getInstance();
      $prepareTechnologyQuery = array();//insert data into Technology
      $prepareTechnologyData = array();

      $userTechnologyqQuery = array();//insert data into user_technology
      $userTechnologyqData = array();
      $n = 0;
      $lastRow = Api::getLastInsertedRow();
      $currentDate = date("Y-m-d H:i:s");

      foreach ($technology as $row) {
        $exist_technology = $db->prepare("SELECT * FROM technology WHERE name='".$row."'"); 
        $exist_technology->execute();             
        $get_exist_technologies = $exist_technology->fetchAll();

        if(empty($get_exist_technologies)){
          $technologyObj->prepareTechnologyQuery[] = '(:name' . $n . ')';
          $technologyObj->prepareTechnologyData['name' . $n] = $row;
        }  

        $technologyObj->userTechnologyqQuery[] = '(:uid' . $n . ',:name' . $n .',:created' . $n .',:modified' . $n .')';
        $technologyObj->userTechnologyqData['uid' . $n] = $lastRow['id'];
        $technologyObj->userTechnologyqData['name' . $n] = $row;
        $technologyObj->userTechnologyqData['created' . $n] = $currentDate;
        $technologyObj->userTechnologyqData['modified' . $n] = $currentDate;

        $n++;        
      }

      return $technologyObj;
    }

    public static function uploadResume($fileToUpload) {

      if(!empty($fileToUpload[RequestParam::$fileToUpload][RequestParam::$FACEBOOK_ID])){
        $facebookid = $fileToUpload[RequestParam::$fileToUpload][RequestParam::$FACEBOOK_ID];
        $userRow = Api::getUserByFacebookId($facebookid);
      }
      else{
        $userRow = Api::getLastInsertedRow();
      }

      $return = 0;
      $uploaddir = FILES_PATH;
      $uploadfile = $uploaddir . basename($fileToUpload[RequestParam::$fileToUpload]['name']);
      $db = Database::getInstance();
      
      $currentDate = date("Y-m-d H:i:s");
      if (move_uploaded_file($fileToUpload[RequestParam::$fileToUpload]['tmp_name'], $uploadfile)) {

          $stmt_resume = $db->prepare("INSERT INTO resumes(uid, filename, title, created, modified) 
          VALUES (:uid, :filename, :title, :created, :modified)");

          $stmt_resume->bindParam(':uid', $userRow['id']);
          $stmt_resume->bindParam(':filename', $uploadfile);
          $stmt_resume->bindParam(':title', $fileToUpload[RequestParam::$fileToUpload]['name']);
          $stmt_resume->bindParam(':created', $currentDate);
          $stmt_resume->bindParam(':modified', $currentDate);
          $stmt_resume = $stmt_resume->execute();

          $return = 1;
      } else {
          $return = 0;
      }
      return $return;

    }

    public static function addUser($user){
      $db = Database::getInstance();
      $return = 0;
      $currentDate = date("Y-m-d H:i:s");

      $stmt = $db->prepare("INSERT INTO users (fb_id, name, email, phone_number, alternate_phone_number, location, designation, experience_year, experience_month, willing_to_relocate, refer_me, created, modified) 
      VALUES (:fb_id, :name, :email, :phone_number, :alternate_phone_number, :location, :designation, :experience_year, :experience_month, :willing_to_relocate, :refer_me, :created, :modified)");
      $stmt->bindParam(':fb_id', $user['fb_id']);
      $stmt->bindParam(':name', $user['name']);
      $stmt->bindParam(':email', $user['email']);
      $stmt->bindParam(':phone_number', $user['phone_number']);
      $stmt->bindParam(':alternate_phone_number', $user['alternate_phone_number']);
      $stmt->bindParam(':location', $user['location']);
      $stmt->bindParam(':designation', $user['designation']);
      $stmt->bindParam(':experience_year', $user['experience_year']);
      $stmt->bindParam(':experience_month', $user['experience_month']);
      $stmt->bindParam(':willing_to_relocate', $user['willing_to_relocate']);
      $stmt->bindParam(':refer_me', $user['refer_me']);
      $stmt->bindParam(':created', $currentDate);
      $stmt->bindParam(':modified', $currentDate);    

      if($stmt->execute()){
        $return = 1;
      }
      return $return;
    }

    public static function getTechnologies(){
      $db = Database::getInstance();
      $all_technology = $db->prepare("SELECT * FROM technology"); 
      $all_technology->execute();             
      $all_technology = $all_technology->fetchAll();
      return $all_technology;
    }

    public static function getProfile($fb_id){      
      $db = Database::getInstance();
      $userProfile = $db->prepare("SELECT u.id, u.fb_id, u.name, u.email, u.phone_number, 
        u.alternate_phone_number, u.location, u.designation, u.experience_year, 
        u.experience_month, u.willing_to_relocate, u.refer_me, u.created, u.modified, ut.id as technology_id, ut.name as technology_name
FROM users u LEFT JOIN user_technology ut ON u.id = ut.uid WHERE u.fb_id =:fb_id"); 
      $userProfile->bindParam(':fb_id', $fb_id);
      $userProfile->execute();             
      $userProfile = $userProfile->fetchAll(PDO::FETCH_ASSOC);
      $technology = array();
      foreach ($userProfile as $key => $value) {
        if(!empty($value['technology_name'])){
          $technology['technology'][] = $value['technology_name'];          
        }        
      }

      if(!empty($technology)){

        $userProfile = array_merge($userProfile[0], $technology);
      }

      return $userProfile;
    }

  }

?>