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

      $this->facebook_id_var = RequestParam::$FACEBOOK_ID;
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


      if(isset($data[RequestParam::$FACEBOOK_ID])){

        $return = Api::addUser($data);

        $lastRow = Api::getLastInsertedRow();

        //Upload Resume code
        if(!empty($lastRow) && !empty($data['fileToUpload'])){
          $return = Api::uploadResume($data['fileToUpload']); 
        }

        //Insert technologies code
        if(!empty($data['skill'])){
          $return = Api::addSkill($data['skill']);
        }          
        
      }else{
        $return = 0;
      }

      return $return;
    }


    public static function getUserByFacebookId($facebook_id){

      $facebook_id_var = RequestParam::$FACEBOOK_ID;
      //ECHO "SELECT * FROM users WHERE {$facebook_id_var}=:{$facebook_id}";DIE;
      $db = Database::getInstance();
      $userProfile = $db->prepare("SELECT * FROM users WHERE {$facebook_id_var}=:{$facebook_id_var}"); 
      
      $userProfile->bindParam(":{$facebook_id_var}", $facebook_id);
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

    public static function addSkill($skill){

      $return = 0;
      $db = Database::getInstance();
      $sql_tech = 'INSERT INTO skill (name) VALUES ';
      $sql_tech_user = 'INSERT INTO user_skill (uid,name,created,modified) VALUES ';
            
      $existSkills = Api::checkSkillExist($skill);

      if (!empty($existSkills->prepareSkillQuery) && isset($existSkills->prepareSkillQuery)) {
        $sql_tech .= implode(', ', $existSkills->prepareSkillQuery);
        $stmt = $db->prepare($sql_tech);
        $stmt->execute($existSkills->prepareSkillData);
      }
      if(!empty($existSkills->userSkillqData)){
        $sql_tech_user .= implode(', ', $existSkills->userSkillqQuery);      
        $stmt_user_tech = $db->prepare($sql_tech_user);
        $stmt_user_tech->execute($existSkills->userSkillqData); 
        $return = 1;
      }

      return $return;
    }

    public static function checkSkillExist($skill){
      $skillObj = new stdClass();
      $db = Database::getInstance();
      $prepareSkillQuery = array();//insert data into Skill
      $prepareSkillData = array();

      $userSkillqQuery = array();//insert data into user_skill
      $userSkillqData = array();
      $n = 0;
      $lastRow = Api::getLastInsertedRow();
      $currentDate = date("Y-m-d H:i:s");

      foreach ($skill as $row) {
        $exist_skill = $db->prepare("SELECT * FROM skill WHERE name='".$row."'"); 
        $exist_skill->execute();             
        $get_exist_technologies = $exist_skill->fetchAll();

        if(empty($get_exist_technologies)){
          $skillObj->prepareSkillQuery[] = '(:name' . $n . ')';
          $skillObj->prepareSkillData['name' . $n] = $row;
        }  

        $skillObj->userSkillqQuery[] = '(:uid' . $n . ',:name' . $n .',:created' . $n .',:modified' . $n .')';
        $skillObj->userSkillqData['uid' . $n] = $lastRow['id'];
        $skillObj->userSkillqData['name' . $n] = $row;
        $skillObj->userSkillqData['created' . $n] = $currentDate;
        $skillObj->userSkillqData['modified' . $n] = $currentDate;

        $n++;        
      }

      return $skillObj;
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

      $facebook_id_var = RequestParam::$FACEBOOK_ID;

      $stmt = $db->prepare("INSERT INTO users ({$facebook_id_var}, name, email, phone_number, alternate_phone_number, location, designation, experience_year, experience_month, willing_to_relocate, refer_me, created, modified) 
      VALUES (:{$facebook_id_var}, :name, :email, :phone_number, :alternate_phone_number, :location, :designation, :experience_year, :experience_month, :willing_to_relocate, :refer_me, :created, :modified)");
      $stmt->bindParam(':{$facebook_id_var}', $user[RequestParam::$FACEBOOK_ID]);
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

    public static function getSkills(){
      $db = Database::getInstance();
      $all_skill = $db->prepare("SELECT name FROM skill"); 
      $all_skill->execute();             
      $all_skill = $all_skill->fetchAll(PDO::FETCH_COLUMN, 0);
      return $all_skill;
    }

    public static function getWorkingAs(){
      $db = Database::getInstance();
      $all_working_as = $db->prepare("SELECT working_as FROM users WHERE working_as IS NOT NULL GROUP BY working_as"); 
      $all_working_as->execute();             
      $all_working_as = $all_working_as->fetchAll(PDO::FETCH_COLUMN, 0);
      return $all_working_as;
    }

    public static function getDesignations(){
      $db = Database::getInstance();
      $all_designations = $db->prepare("SELECT designation FROM users WHERE designation IS NOT NULL GROUP BY working_as"); 
      $all_designations->execute();             
      $all_designations = $all_designations->fetchAll(PDO::FETCH_COLUMN, 0);
      return $all_designations;
    }

    public static function getUsers($phone_number){

    }

    public static function getProfile($facebook_id){    
      $facebook_id_var = RequestParam::$FACEBOOK_ID;  
      $db = Database::getInstance();
      $userProfile = $db->prepare("SELECT u.id, u.{$facebook_id_var}, u.name, u.email, u.phone_number, 
        u.alternate_phone_number, u.location, u.designation, u.experience_year, 
        u.experience_month, u.willing_to_relocate as willing_to_relocate, u.refer_me as refer_me,u.working_as,
        CASE willing_to_relocate WHEN 1 THEN 'true' ELSE 'false' END as willing_to_relocate,  
        CASE refer_me WHEN 1 THEN 'true' ELSE 'false' END as refer_me 
FROM users u WHERE u.{$facebook_id_var} =:facebook_id"); 
      $userProfile->bindParam(':facebook_id', $facebook_id);
      $userProfile->execute();             
      $userProfile = $userProfile->fetchAll(PDO::FETCH_ASSOC);
      
      return $userProfile;
    }

  }

?>