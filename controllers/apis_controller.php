<?php
  class ApisController {
    public function index() {
      // we store all the apis in a variable
      $apis = Api::all();
      require_once('./views/apis/index.php');
      //print json_encode($apis);exit();

    }

    public function register_user(){
      $data = json_decode(file_get_contents('php://input'), true);

      if(!empty($data[RequestParam::$senderPhoneNo]) && !empty($data[RequestParam::$gcmRegistrationToken])){
        Api::saveUser($data);

        $data = null;
        $message = "User saved";
        $statusCode = 200;
        self::responseFormat($data, $message, $statusCode);
      }else{
        $data = null;
        $message = "Error: Please send correct values";
        $statusCode = 201;
        self::responseFormat($data, $message, $statusCode);
      }
    }

    public function responseFormat($result, $message, $statusCode){
              
              //echo "<pre>";print_r($result);die;
        $return = new stdClass();
        $return->statusCode = $statusCode;
        $return->message = $message;
        $return->data = $result;

        print json_encode($return,JSON_NUMERIC_CHECK);exit();
    }

    public function sendmessage(){
      $data = json_decode(file_get_contents('php://input'), true);

      if(!empty($data[RequestParam::$senderPhoneNo]) && !empty($data[RequestParam::$gcmRegistrationToken])){
       $data = Api::sendMessage($data);

        $data = null;
        $message = "User saved";
        $statusCode = 200;
        self::responseFormat($data, $message, $statusCode);
      }else{
        $data = null;
        $message = "Error: Please send correct values";
        $statusCode = 201;
        self::responseFormat($data, $message, $statusCode);
      }
    }
  }
?>