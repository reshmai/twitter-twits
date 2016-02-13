<?php
  class ApisController {
    public function index() {
      // we store all the apis in a variable
      $apis = Api::all();
      require_once('./views/apis/index.php');
      //print json_encode($apis);exit();
    }

    public function getProfile(){
      print_r($_POST);die;  
      if(isset($_POST) && !empty($_POST[RequestParam::$FACEBOOK_ID])){
        if(!empty($currentUserProfile)){
          $message = "User profile dose not exist.";
        }else{
          $message = "User profile exist.";
        }
        $currentUserProfile = Api::getProfile(RequestParam::$FACEBOOK_ID);
        $result = new stdClass();
        $result->statusCode = 200;
        $result->message = $message;
        $result->data = $currentUserProfile;

        print json_encode($result);exit();
      }
    }

    public function signup(){

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
        $message = 'User inserted successfully.';
      }

      $result = new stdClass();
      $result->statusCode = 200;
      $result->message = $message;
      $result->data = $allusers;

      print json_encode($result);exit();
    }

    //http://refer.local.com/apis/gettechnologies
    public function getTechnologies(){

      $technologies = Api::getTechnologies();

      $result = new stdClass();
      $result->statusCode = 200;
      $result->message = "";
      $result->data = $technologies;

      print json_encode($result);exit();

    }

    public function uploadResume(){

      if(isset($_FILES[RequestParam::$fileToUpload]) && isset($_POST[RequestParam::$FACEBOOK_ID])){
        
        $filesData = array();
        $filesData[RequestParam::$fileToUpload] = $_FILES[RequestParam::$fileToUpload];
        $filesData[RequestParam::$fileToUpload][RequestParam::$FACEBOOK_ID] = $_POST[RequestParam::$FACEBOOK_ID];
        $resume = Api::uploadResume($filesData);

        $result = new stdClass();
        $result->statusCode = 200;
        $result->message = "Resume uploaded suucessfully!";
        $result->data = null;

        print json_encode($result);exit();
      }

    }

    public function checkUserExist(){

      if(!empty($_POST[RequestParam::$FACEBOOK_ID])){

        $existUser = Api::getUserByFacebookId($_POST[RequestParam::$FACEBOOK_ID]);

        if(!empty($existUser)){
          $status = 200;
          $message = 'User exist!';
        }else{
          $status = 201;
          $message = 'User dose not exist!';
        }

        $result = new stdClass();
        $result->statusCode = $status;
        $result->message = $message;
        $result->data = null;

        print json_encode($result);exit();
      }
    }

  }
?>