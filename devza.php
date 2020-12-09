<?php
set_time_limit(0);
date_default_timezone_set('Asia/Kolkata');
echo "Started at ". Date('d-M-Y H:i:s')."\n";
/**
 *
 */
class curlK2S
{
  protected $userName;
  protected $password;
  protected $baseUrl;
  protected $authCode;

  function __construct($loginCredentials = array())
  {

    if($loginCredentials){
      $this->userName =  $loginCredentials['username'];
      $this->password =  $loginCredentials['password'];
      $this->baseUrl  =  "https://keep2share.cc";
    }
  }

  function login($debug = false){
      $url = $this->baseUrl."/api/v2/login";
      $postFields = ["username" => $this->userName, "password" => $this->password];
      $response = json_decode($this->executeCurl($url,"POST",null,$postFields,$debug));
      $this->authCode = $response->auth_token;
      return $response;

  }

  function profile($debug = false){
    $url = $this->baseUrl."/profile";
    $options[CURLOPT_HTTPHEADER][] = "Cookie: accessToken=$this->authCode;";
    $options[CURLOPT_FOLLOWLOCATION] = true;
    $options[CURLOPT_USERAGENT] = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8';
    $response = $this->executeCurl($url,"GET",null,[],$debug,$options);
    return $response;
  }

  function prepareCurloption($url, $method, $arguments, $postFields, $debug, $options){
    $option = [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_URL => $url,
        CURLOPT_HEADER => true,
        //CURLOPT_HTTPHEADER => [['Accept: application/json,*/*']],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_VERBOSE => $debug

    ];
    if(strtoupper($method) == "POST"){
      $option[CURLOPT_POSTFIELDS] = is_array($postFields)?json_encode($postFields):array();
      $option [CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
    }
      return $option + $options;
  }

  function executeCurl($url, $method, $arguments= null, $postFields = [], $debug = false, $options = [] ){
    $connection = curl_init();
    //if($this->authCode){
    //  $postFields['auth_token'] = $this->authCode;
    //}
    $curlOptions = $this->prepareCurloption($url, $method, $arguments, $postFields, $debug, $options);
    curl_setopt_array($connection, $curlOptions);
    $response = curl_exec($connection);
    if($debug){
      print_r($curlOptions);
      $verbose = fopen('php://temp', 'w+');
      $result = curl_exec($connection);
      if ($result === FALSE) {
          printf("cUrl error (#%d): %s<br>\n", curl_errno($connection),htmlspecialchars(curl_error($connection)));
      }
      rewind($verbose);
      $verboseLog = stream_get_contents($verbose);
      echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
    }

    $statusCode = curl_getinfo($connection, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($connection, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    if($statusCode !==200){
      print_r($body);
    }
    curl_close($connection);
    return $body;
  }
}
$loginDetails = ["username" => "email@yourdomain.com", "password" => "********"];
$c2kObject = new curlK2S($loginDetails);
$loggedIn = $c2kObject->login(false);
print_r($loggedIn);
$profileDetails = $c2kObject->profile(false);
print_r($profileDetails);

?>
