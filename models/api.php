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

    public function saveUser($data){

      $checkUserExist = self::checkUserExist($data[RequestParam::$senderPhoneNo]);
      
      $db = Database::getInstance();  
      if (!empty($checkUserExist)){
        
          self::updateUser($data);
      }else{
        
          self::insertUser($data);
      } 

      return 1;

    }
    public function checkUserExist($senderPhoneNo){

      $db = Database::getInstance(); 
      $stmt = $db->prepare("SELECT * FROM user WHERE phonenumber=:phonenumber");
      $stmt->bindParam(':phonenumber', $senderPhoneNo);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function insertUser($data){

      $db = Database::getInstance(); 
      $stmt = $db->prepare("INSERT INTO user (phonenumber, registration_token) VALUES (:phonenumber, :registration_token)");
      $stmt->bindParam(':phonenumber', $data[RequestParam::$senderPhoneNo]);
      $stmt->bindParam(':registration_token', $data[RequestParam::$gcmRegistrationToken]);
      $stmt->execute();
      return 1;
    }
    public function updateUser($data){

      $db = Database::getInstance(); 
      $stmt = $db->prepare("UPDATE user SET phonenumber=:phonenumber, registration_token=:registration_token where phonenumber=:phonenumber");
      $stmt->bindParam(':phonenumber', $data[RequestParam::$senderPhoneNo]);
      $stmt->bindParam(':registration_token', $data[RequestParam::$gcmRegistrationToken]);
      $stmt->execute();
      return 1;
    }

    public function sendMessage($data){
      $checkUserExist = self::checkUserExist($data[RequestParam::$senderPhoneNo]);
      
      $db = Database::getInstance();  
      if (empty($checkUserExist)){
          self::saveUser($data);  
      }
      $return = self::checkForReceiver($data);

      return $return;
    }

    public function checkForReceiver($data){
      $pushdevices = array();
      foreach ($data[RequestParam::$receiverPhoneNo] as $key => $receiverPhoneNo) {
        $checkUserExist=self::checkUserExist($receiverPhoneNo);

        if(!empty($checkUserExist)){
          $responseToReturn['data'][] = array('receiverPhoneNo'=>$receiverPhoneNo, 'isUserExisting'=>true);
          
          $pushdevices[] = self::prepareForGcm($receiverPhoneNo);
          
        }else{
          $responseToReturn['data'][] = array('receiverPhoneNo'=>$receiverPhoneNo, 'isUserExisting'=>false);
        }
        
      }

      if(!empty($pushdevices) && !empty($data[RequestParam::$messages])){
          self::pushGCM($pushdevices, $data[RequestParam::$messages]);
      }
      return $responseToReturn;
    }
    
    public function prepareForGcm($receiverPhoneNo){
      
        try {
                
          $db = Database::getInstance(); 
          $stmt = $db->prepare("SELECT registration_token from user where phonenumber = :phonenumber");
          $stmt->bindParam(':phonenumber', $receiverPhoneNo);
          $stmt->execute();
          $pushdevices = $stmt->fetch(PDO::FETCH_COLUMN);
          $db = NULL;
          return $pushdevices;
        } catch(PDOException $e) {
                echo '{"error":{"text":'. $e->getMessage() .'}}';
        }

        return $pushdevices;
    }

    public function pushGCM($pushdevices, $pushMessage){
      
        // API access key from Google API's Console
        define( 'API_ACCESS_KEY', RequestParam::$accessKey);
        if (count($pushdevices) >= 1) {
          // prep the bundle
          $msg = array
          (
                  'message'       => $pushMessage,
                  'title'         => 'Alert',
                  'subtitle'      => 'Alert message!',
                  'tickerText'    => 'This alert happened.... this alert happened... this alert happened...',
                  'vibrate'       => 1,
                  'sound'         => 'default',
                  'largeIcon'     => 'large_icon',
                  'smallIcon'     => 'small_icon'
          );

          $headers = array
          (
              'Authorization: key=' . API_ACCESS_KEY,
                  'Content-Type: application/json'
          );
          // loop through devices
          $fields = array
          (
                  'registration_ids'      => $pushdevices,
                  'data'                  => $msg
          );
          $ch = curl_init();
          curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
          curl_setopt( $ch,CURLOPT_POST, true );
          curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
          curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
          curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
          curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
          $result = curl_exec($ch );
          curl_close( $ch );
      }
    }
    
}

?>