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

    public function signup($data){

      $return = 0;
      $currentDate = date("Y-m-d H:i:s");
      $db = Database::getInstance();

      if(isset($data[RequestParam::$FACEBOOK_ID])){
        $existUser = Api::getUserByFacebookId($data[RequestParam::$FACEBOOK_ID]);
        //check for exist user for empty
        if (empty($existUser)) {
          //INSERT
          $this->insertNewUser($data);
          return 1;
        }else {
          //UPDATE
          $this->updateExistingUser($data);
          return 1;
        }

        return $return;
      }

    }
    public function updateExistingUser($user){
      //Update user table
      $this->updateUserTable($user);
      //update skills table
      $this->updateSkillsTable($user['skills']);
      //update designation table
      $this->updateDesignationTable($user['designation']);
      //update jobs table
      $this->updateJobsTable($user['working_as']);

    }
    public function updateUserTable($user){

      $db = Database::getInstance();
      $currentDate = date("Y-m-d H:i:s");
      $sql = "UPDATE users SET name=:name, email=:email, phone_number=:phone_number, 
        alternate_phone_number=:alternate_phone_number, location=:location, designation=:designation, skills=:skills, experience_year=:experience_year, experience_month=:experience_month, willing_to_relocate=:willing_to_relocate, refer_me=:refer_me, 
        working_as=:working_as, modified=:modified WHERE facebook_id = :facebook_id";

      $stmt = $db->prepare($sql);
      $return = $stmt->execute(array(
        ':facebook_id' => $user[RequestParam::$FACEBOOK_ID],
        ':name'=> $user['name'],
        ':email'=> $user['email'],
        ':phone_number'=> $user['phone_number'],
        ':alternate_phone_number'=>$user['alternate_phone_number'],
        ':location'=>$user['location'],
        ':designation'=> $user['designation'],
        ':skills'=>$user['skills'],
        ':experience_year'=> (int) $user['experience_year'],
        ':experience_month'=> (int) $user['experience_month'],
        ':willing_to_relocate'=> $user['willing_to_relocate'],
        ':refer_me'=>$user['refer_me'],
        ':working_as'=>$user['working_as'],
        ':modified'=>$currentDate));

    
    }
    public function updateSkillsTable($skills = null){

      $skillArray = explode(",", $skills);
      //for all skills
      foreach ($skillArray as $key => $skillName) {
      //check if value exists in skills table 
        $this->checkForExistingNameAndInsert($table = 'skill',$skillName); 
      }
    }
    public function updateDesignationTable($designation){
      //check if designation exists in table
      $this->checkForExistingNameAndInsert($table = 'designation',$designation); 
    }
    public function updateJobsTable($workingAs){
      //check if jobs exists in table
      $this->checkForExistingNameAndInsert($table = 'jobs',$workingAs);
    }
    public function insertNewUser($user){
      //Insert into user table
      $this->insertIntoUserTable($user);

      //insert into skills table
      $this->insertIntoSkillsTable($user['skills']);
      //insert into designation table
      $this->insertIntoDesignationTable($user['designation']);
      //insert into jobs table
      $this->insertIntoJobsTable($user['working_as']);
    }

    public function insertIntoJobsTable($workingAs){
      //check if jobs exists in table
      $this->checkForExistingNameAndInsert($table = 'jobs',$workingAs);
    }

    public function checkForExistingNameAndInsert($table, $whereValue){

      if (!empty($whereValue) && !$this->isExistInTable($table,$whereColumn = 'name', $whereValue)) {
          
            $db = Database::getInstance();
            $sql = "INSERT INTO ".$table." (name) VALUES (:name)";
            $stmt = $db->prepare($sql);
            $stmt->execute(array(':name'=> $whereValue));    
      }
    }

    public function insertIntoDesignationTable($designation){
      //check if designation exists in table
      $this->checkForExistingNameAndInsert($table = 'designation',$designation); 
    }

    public function insertIntoSkillsTable($skills = null){

      $skillArray = explode(",", $skills);
      //for all skills
      foreach ($skillArray as $key => $skillName) {
      //check if value exists in skills table 
        $this->checkForExistingNameAndInsert($table = 'skill',$skillName); 
      }
    }

    public function isExistInTable($table, $whereColumn,$whereValue){
      $db = Database::getInstance();
      $data = $db->prepare("SELECT * FROM ".$table." WHERE ".$whereColumn." = :".$whereColumn.""); 
      $data->bindParam(":".$whereColumn, $whereValue);
      $data->execute();
      $result=$data->fetch(PDO::FETCH_ASSOC);
      if (!empty($result)) {
        return true;
      }
      return false;             
    }

    public function insertIntoUserTable($user){

      $db = Database::getInstance();
      $currentDate = date("Y-m-d H:i:s");
      $sql = "INSERT INTO users (facebook_id, name, email, phone_number, alternate_phone_number, location, designation, skills, experience_year, experience_month, willing_to_relocate, refer_me, working_as, created, modified) 
      VALUES (:facebook_id, :name, :email, :phone_number, 
        :alternate_phone_number, :location, :designation, :skills, 
        :experience_year, :experience_month, :willing_to_relocate, :refer_me, 
        :working_as, :created, :modified)";
      $stmt = $db->prepare($sql);
      $stmt->execute(array(
        ':facebook_id' => $user[RequestParam::$FACEBOOK_ID],
        ':name'=> $user['name'],
        ':email'=> $user['email'],
        ':phone_number'=> $user['phone_number'],
        ':alternate_phone_number'=>$user['alternate_phone_number'],
        ':location'=>$user['location'],
        ':designation'=> $user['designation'],
        ':skills'=>$user['skills'],
        ':experience_year'=> (int) $user['experience_year'],
        ':experience_month'=> (int) $user['experience_month'],
        ':willing_to_relocate'=> $user['willing_to_relocate'],
        ':refer_me'=>$user['refer_me'],
        ':working_as'=>$user['working_as'],
        ':created'=>$currentDate,
        ':modified'=>$currentDate));

    }

    public static function getUserByFacebookId($facebook_id){

      $facebook_id_var = RequestParam::$FACEBOOK_ID;
      //ECHO "SELECT * FROM users WHERE {$facebook_id_var}=:{$facebook_id}";DIE;
      $db = Database::getInstance();
      $userProfile = $db->prepare("SELECT * FROM users WHERE facebook_id=:facebook_id"); 
      
      $userProfile->bindParam(":facebook_id", $facebook_id);
      $userProfile->execute();             
      $userProfile = $userProfile->fetch(PDO::FETCH_ASSOC);
      unset($userProfile['id']);
      unset($userProfile['created']);
      unset($userProfile['modified']);
      
      return $userProfile;
    }

    public static function getLastInsertedRow(){
      $db = Database::getInstance();
      $stmt_last = $db->prepare("SELECT * FROM users ORDER BY id DESC LIMIT 10"); 
      $stmt_last->execute(); 
      $lastRow = $stmt_last->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
      return $lastRow;
    }

    public static function uploadResume($fileToUpload, $userExist=null) {

      if(!empty($fileToUpload[RequestParam::$fileToUpload][RequestParam::$FACEBOOK_ID])){
        $facebookid = $fileToUpload[RequestParam::$fileToUpload][RequestParam::$FACEBOOK_ID];
        $resumeExist = Api::checkResumeExistByFid($facebookid);
      }
      
      $return = 0;
      $uploaddir = FILES_PATH;
      $uploadfile = $uploaddir . basename($fileToUpload[RequestParam::$fileToUpload]['name']);
      $db = Database::getInstance();
      
      $currentDate = date("Y-m-d H:i:s");
      if (move_uploaded_file($fileToUpload[RequestParam::$fileToUpload]['tmp_name'], $uploadfile)) {

        if($userExist!=null){
          $stmt_resume = $db->prepare("INSERT INTO resumes(uid, filename, title, created, modified) 
          VALUES (:uid, :filename, :title, :created, :modified)");
          $stmt_resume->bindParam(':uid', $userExist['id']);
          $stmt_resume->bindParam(':facebook_id', $userExist[RequestParam::$FACEBOOK_ID]);
        }else{
          $stmt_resume = $db->prepare("UPDATE resumes SET uid=:uid, filename=:filename, title=:title, modified=:modified WHERE facebook_id=:facebook_id");
          $stmt_resume->bindParam(':uid', $resumeExist['id']);
          $stmt_resume->bindParam(':facebook_id', $resumeExist[RequestParam::$FACEBOOK_ID]);
        }                   
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

    public function getJobWorkingasDesignation($colunName, $table){
      $db = Database::getInstance();
      $getList = $db->prepare("SELECT ".$colunName." FROM ".$table); 
      $getList->execute();             
      $getList = $getList->fetchAll(PDO::FETCH_COLUMN, 0);
      return $getList;
    }

    public function getSkills(){
      return self::getJobWorkingasDesignation('name', 'skill');
    }

    public function getWorkingAs(){
      return self::getJobWorkingasDesignation("name", "jobs");
    }

    public function getDesignations(){
      return self::getJobWorkingasDesignation("name", "designation");
    }

    public static function getUsers($phone_number){
      $db = Database::getInstance();
      
      $facebook_id_var = RequestParam::$FACEBOOK_ID; 
      $users_by_phone = $db->prepare("SELECT DISTINCT u.{$facebook_id_var}, u.name, u.email, u.phone_number, 
        u.alternate_phone_number, u.location, u.designation, u.experience_year, 
        u.experience_month, u.willing_to_relocate as willing_to_relocate, u.refer_me as refer_me,u.working_as, GROUP_CONCAT(us.name) as skill
FROM  `users` AS u
LEFT JOIN user_skill AS us ON us.uid = u.id
WHERE phone_number IN({$phone_number}) GROUP BY u.id
ORDER BY u.id DESC limit 3"); 
      $users_by_phone->execute();             
      $users_by_phone = $users_by_phone->fetchAll(PDO::FETCH_ASSOC);
      
      foreach($users_by_phone as $userKey=>$userRow){
        $users_by_phone[$userKey]['willing_to_relocate'] = (bool) $userRow['willing_to_relocate'];
        $users_by_phone[$userKey]['refer_me'] = (bool) $userRow['refer_me'];
      }
      return $users_by_phone;
    }

    public static function getProfile($facebook_id){    
      $facebook_id_var = RequestParam::$FACEBOOK_ID;  
      $db = Database::getInstance();
      $userProfile = $db->prepare("SELECT u.{$facebook_id_var}, u.name, u.email, u.phone_number, 
        u.alternate_phone_number, u.location, u.designation, u.experience_year, 
        u.experience_month, u.willing_to_relocate as willing_to_relocate, u.refer_me as refer_me,u.working_as, us.name as skill 
        FROM users u 
        LEFT JOIN user_skill us on us.uid=u.id 
        WHERE u.{$facebook_id_var} =:facebook_id"); 

      $userProfile->bindParam(':facebook_id', $facebook_id);
      $userProfile->execute();
      $userProfile = $userProfile->fetchAll(PDO::FETCH_ASSOC);

      $skill = array();
      foreach($userProfile as $userKey=>$userRow){
        $userProfile[$userKey]['willing_to_relocate'] = (bool) $userRow['willing_to_relocate'];
        $userProfile[$userKey]['refer_me'] = (bool) $userRow['refer_me'];
        $skill[] = $userRow['skill'];
      }

      $skill = implode(",", $skill);
      if(!empty($skill)){
        $userProfile[0]['skill'] = $skill;
      }
      $profile = $userProfile[0];

      return $profile;
    }

    public static function checkResumeExistByFid($facebook_id){

      $facebook_id_var = RequestParam::$FACEBOOK_ID;
      //ECHO "SELECT * FROM users WHERE {$facebook_id_var}=:{$facebook_id}";DIE;
      $db = Database::getInstance();
      $userProfile = $db->prepare("SELECT * FROM users u inner join resumes r on r.uid=u.id WHERE u.facebook_id=:facebook_id"); 
      
      $userProfile->bindParam(":facebook_id", $facebook_id);
      $userProfile->execute();             
      $userProfile = $userProfile->fetch(PDO::FETCH_ASSOC);
      
      return $userProfile;
    }

    public function isValidRequest($post){
      if(empty($post['facebook_id']) || empty($post['name']) || 
        empty($post['email']) || empty($post['phone_number']) || 
        empty($post['working_as']) || filter_var($post['email'], FILTER_VALIDATE_EMAIL) === false){
        return false;
      }elseif(strlen($post['phone_number'])!=10 || strlen($post['alternate_phone_number'])!=10){
        return false;
      }
      return true;
    }

  }

?>