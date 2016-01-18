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

    public function login() {
      // we store all the apis in a variable
      if(isset($_POST['phonenumber']) && $_POST['password']){
        $phonenumber = $_POST['phonenumber'];
        $password = $_POST['password'];
      }
      $api = Api::login('4444444444', '@abc');
      print json_encode($api); exit();
    }

    public function getprofile(){
      
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
    public function gettechnologies(){

      $technologies = Api::getTechnologies();

      $result = new stdClass();
      $result->statusCode = 200;
      $result->message = "";
      $result->data = $technologies;

      print json_encode($result);exit();

    }

  }
?>