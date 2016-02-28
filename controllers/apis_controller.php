<?php
  class ApisController {
    public function index() {
      // we store all the apis in a variable
      $apis = Api::all();
      require_once('./views/apis/index.php');
      //print json_encode($apis);exit();
    }

    public function show() {

      // we expect a url of form ?controller=apis&action=show&id=x
      // without an id we just redirect to the error page as we need the api id to find it in the database
      if (!isset($_GET['id']))
        return call('pages', 'error');

      // we use the given id to get the right api
      $api = Api::find($_GET['id']);

      print json_encode($api);exit();
    }

    public function get_user_profile(){

      if(isset($_POST) && !empty($_POST[RequestParam::$FACEBOOK_ID])){
        $currentUserProfile = Api::getProfile($_POST[RequestParam::$FACEBOOK_ID]);

        if(empty($currentUserProfile)){
          $message = "User does not exist";
          $statusCode = 201;
        }else{
          $message = "User data received successfully";
          $statusCode = 200;
        }
 
        self::responseFormat($currentUserProfile, $message, $statusCode);
      }
    }

    public function update_user(){
      $message = '';
      if(!empty($_FILES)){
        $postData = array_merge($_POST, $_FILES);  
      }else{
        $postData = $_POST;
      }  

      $userSaved = Api::signup($postData);

      $allusers = new stdClass();
      if($userSaved==1){
        $allusers = Api::all();
        $message = 'User updated successfully';
      }

      $result = new stdClass();
      $result->statusCode = 200;
      $result->message = $message;
      $result->data = $allusers;

      print json_encode($result);exit();
    }

    //http://refer.local.com/apis/get_skill_list

    public function get_skill_list(){


      $skills = Api::getSkills();
      if(empty($skills)){
          $message = "Skills does not exist";
          $statusCode = 201;
        }else{
          $message = "Skills exist";
          $statusCode = 200;
        }
      self::responseFormat($skills, $message, $statusCode);

    }


    public function get_job_list(){

      $working_as = Api::getWorkingAs();
      if(empty($working_as)){
          $message = "no data exist for Working as:";
          $statusCode = 201;
        }else{
          $message = "data exist";
          $statusCode = 200;
        }
      self::responseFormat($working_as, $message, $statusCode);

    }


    public function get_designation_list(){


      $designations = Api::getDesignations();
      if(empty($designations)){
          $message = "no data exist for Designation:";
          $statusCode = 201;
        }else{
          $message = "data exist";
          $statusCode = 200;
        }
      self::responseFormat($designations, $message, $statusCode);

    }


    public function get_user_list(){

      if(!empty($_POST['phone_number'])){
        $phone_number = explode(',', $_POST['phone_number']);
      }

      if(!empty($phone_number)){
        $user_list = Api::getUsers($phone_number);
        if(empty($user_list)){
            $message = "No data exist";
            $statusCode = 201;
          }else{
            $message = "Data exist";
            $statusCode = 200;
          }
        self::responseFormat($user_list, $message, $statusCode);
      }
    }

    public function upload_resume(){

      if(isset($_FILES[RequestParam::$fileToUpload]) && isset($_POST[RequestParam::$FACEBOOK_ID])){
        
        $filesData = array();
        $filesData[RequestParam::$fileToUpload] = $_FILES[RequestParam::$fileToUpload];
        $filesData[RequestParam::$fileToUpload][RequestParam::$FACEBOOK_ID] = $_POST[RequestParam::$FACEBOOK_ID];
        $resume = Api::uploadResume($filesData);

        $result = new stdClass();
        $result->statusCode = 200;
        $result->message = "Resume uploaded succesfully!";
        $result->data = null;

        print json_encode($result);exit();
      }

    }


    public function check_user_exist(){

      if(!empty($_POST[RequestParam::$FACEBOOK_ID])){

        $existUser = Api::getUserByFacebookId($_POST[RequestParam::$FACEBOOK_ID]);

        if(!empty($existUser)){
          $status = 200;
          $message = 'User exist';
        }else{
          $status = 201;
          $message = 'User does not exist';
        }

        $result = new stdClass();
        $result->statusCode = $status;
        $result->message = $message;
        $result->data = null;

        print json_encode($result);exit();
      }
    }

    public function responseFormat($result, $message, $statusCode){
              
        $return = new stdClass();
        $return->statusCode = $statusCode;
        $return->message = $message;
        $return->data = $result;

        print json_encode($return);exit();
    }

  }
?>