<?php
$host = "localhost"; 
$user = "root"; 
$password = "1234"; 
$dbname = "contestdb"; 

$con = mysqli_connect($host, $user, $password,$dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
    return;
}

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');

$config = array('client_id'=> '3d338aa52ae9f4fd9282cf64bd9b4693',
    'client_secret' => 'b069d0e6237ab7692de868ac919aa109',
    'api_endpoint'=> 'https://api.codechef.com/',
    'authorization_code_endpoint'=> 'https://api.codechef.com/oauth/authorize',
    'access_token_endpoint'=> 'https://api.codechef.com/oauth/token',
    'redirect_uri'=> 'http://localhost:8080',
    'website_base_url' => 'http://localhost:8080');

$oauth_details = array('authorization_code' => '',
    'access_token' => '',
    'refresh_token' => '');

function make_api_request($oauth_config, $path){
    $headers[] = 'Authorization: Bearer ' . $oauth_config['access_token'];
    return make_curl_request($path, false, $headers);
}

function make_curl_request($url, $post = FALSE, $headers = array()){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
    }

    $headers[] = 'content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    return $response;
}

function Search_User($config, $oauth_details , $username ){
    $path =  $config['api_endpoint']."users/".$username;
    $response = make_api_request($oauth_details , $path);
    return $response;
}

function List_contest( $config, $oauth_details ){
    $path =  $config['api_endpoint']."contests/";
    $response = make_api_request( $oauth_details , $path);
    return $response;
}

function Get_Contest( $config, $oauth_details ,$Contest_Code){
    $path =  $config['api_endpoint']."contests/".$Contest_Code;
    $response = make_api_request( $oauth_details , $path);
    return $response;
}

function Get_Problem( $config, $oauth_details , $Contest_Code, $Problem_code){
    $path =  $config['api_endpoint']."contests/".$Contest_Code."/problems/".$Problem_code;
    $response = make_api_request( $oauth_details , $path);
    return $response;
}

function Get_submissions( $config, $oauth_details , $toget, $code, $Username){
    
    $path =  $config['api_endpoint']."submissions/?".$toget."=".$code;
    if($Username){
        $path .= '&username='.$Username;
    }
    $response = make_api_request($oauth_details , $path);
    return $response;
}

function Rankings( $config, $oauth_details, $Contest_Code ){
    $path =  $config['api_endpoint']."rankings/".$Contest_Code;
    // echo $path;
    $response = make_api_request($oauth_details , $path);
    return $response;
}

function country( $config, $oauth_details){
    $path =  $config['api_endpoint']."country/";
    $response = make_api_request($oauth_details , $path);
    return $response;
}

function institution( $config, $oauth_details){
    $path =  $config['api_endpoint']."institution/";
    $response = make_api_request($oauth_details , $path);
    return $response;
}

function run_code( $config, $oauth_details ,$params){
    $path =  $config['api_endpoint']."ide/run/";
    $response = make_post_api($oauth_details, $path, $params);
    return $response;
}
function make_post_api($oauth_details ,$path, $post){
    $headers[] = 'Authorization: Bearer '.$oauth_details['access_token'];
    return make_curl_request($path, $post, $headers);
}


////////////////////////////////////////////////////////////////////////////////

$func = $_GET['func'];
$ip = $_GET['ip'];

///////////////Requesting database////////////////////////////////////////////////

function generate_access_token_from_refresh_token($config, $oauth_details){
    $oauth_config = array('grant_type' => 'refresh_token', 'refresh_token'=> $oauth_details['refresh_token'], 'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret']);
    $response = make_curl_request($config['access_token_endpoint'], $oauth_config);
    // echo $response;
    $response = json_decode($response, true);
    if($response['status'] == "error"){
        echo '{ "code" : "unauthorized" }'; exit();
    }
    $result = $response['result']['data'];

    $oauth_details['access_token'] = $result['access_token'];
    $oauth_details['refresh_token'] = $result['refresh_token'];
    $oauth_details['scope'] = $result['scope'];
    return $oauth_details;
}

function get_token($con, $config, $oauth_details, $ip){
    $sql = "select * from users where ip='".$ip."' and session ='1' ";
    $result = mysqli_query($con,$sql);
    if(!$result || mysqli_num_rows($result) == 0){  echo ' { "code" : "unauthorized" }'; exit();  }
    
    $result = mysqli_fetch_object($result);
    $result = json_encode($result);
    $result = json_decode($result);
    $endtime = $result->valid_till;
    $currtime = time();
    $oauth_details['access_token'] = $result->token;
    $oauth_details['refresh_token'] = $result->refresh;

    if ((int)$currtime - (int)$endtime > 0) {
        $oauth_details = generate_access_token_from_refresh_token($config, $oauth_details);
        saveindb($con, $oauth_details, $ip);
    }
    return $oauth_details;
}

function saveindb($con, $oauth_details, $ip){
    $extra = strtotime("+50 minute");
    $endtime = strtotime(date("Y-m-d h:i:sa",$extra));
    $sql = "update users set token='".$oauth_details['access_token']."' , refresh='".$oauth_details['refresh_token']."' valid_till='".$endtime."'"; 
    $result = mysqli_query($con,$sql);
    return;
}
////////////////////////////////////////////////////////////////////////////////////////
$oauth_details = get_token($con, $config, $oauth_details, $ip);

if($func == 'Get_my_username'){
    $sql = "select * from users where ip='".$ip."' and session ='1' ";
    $result = mysqli_query($con,$sql);
    $result = mysqli_fetch_object($result);
    $result = json_encode($result);
    $result = json_decode($result);
    $result = $result->username;
    if($result != "demo"){ echo '{ "username" : "'.$result.'" }'; return; }
    $response = Search_User($config, $oauth_details,"me");
    echo $response;
}
else if($func == 'List_contest'){
    $response = List_contest( $config, $oauth_details );
    echo $response;
}
else if($func == 'Get_Contest'){
    $code = $_GET['code'];
    $response = Get_Contest( $config, $oauth_details ,$code);
    echo $response;
}
else if($func == 'Get_Problem'){
    $pcode = $_GET['pcode'];
    $contestcode = $_GET['contestcode'];
    $response  = Get_Problem( $config, $oauth_details , $contestcode, $pcode);
    echo $response;
}
else if($func == 'Get_contest_submissions'){
    $user = false;
    if(isset($_GET['username'])){ $user = $_GET['username']; }
    if(isset($_GET['contest'])){
         $response = Get_submissions( $config, $oauth_details , "contestCode", $_GET['contest'] , $user);
    }
    if(isset($_GET['problem'])){
        $response = Get_submissions( $config, $oauth_details , "problemCode", $_GET['problem'] , $user);
     }
    echo $response;
}
else if($func == 'Get_rankings'){
    $response = Rankings($config, $oauth_details, $_GET['code']);
    echo $response;
}
else if($func == 'Country'){
    $response = country($config, $oauth_details);
    echo $response;
}
else if($func == 'Institution'){
    $response = institution($config, $oauth_details);
    echo $response;
}
else if($func == 'runcode'){

    $response = file_get_contents('php://input');
    $response = json_decode($response);
    $params = array(
        'sourceCode' => $response->sourceCode,
        'language' => $response->language,
        'input' => $response->input
    );
    $response = run_code( $config, $oauth_details ,$params);
    echo $response;
}
else if($func == 'get_status'){
    $link =$_GET['link'];
    $path = $config['api_endpoint']."ide/status?link=".$link;
    $response = make_api_request($oauth_details , $path);
    echo $response;
}