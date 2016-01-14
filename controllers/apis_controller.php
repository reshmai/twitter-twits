<?php
  class ApisController {
    public function index() {
      // we store all the apis in a variable
      $apis = Api::all();
      require_once('./views/apis/index.php');
      print json_encode($apis);exit();
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

    public function signup(){
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
    if(!empty($_FILES)){
        array_push($_POST, $_FILES);  
      }      
      $api = Api::signup($_POST);
      $allusers = new stdClass();
      if($api!=0){
        $allusers = Api::all();
      }


      $result = new stdClass();
      $result->statusCode = 200;
      $result->message = $api;
      $result->data = $allusers;

      print json_encode($result);exit();
    }
  }
?>