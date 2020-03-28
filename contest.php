<?php
$host = "localhost"; 
$user = "root"; 
$password = "1234"; 
$dbname = "contestdb"; 
$id = '';

$con = mysqli_connect($host, $user, $password,$dbname);

$method = $_SERVER['REQUEST_METHOD'];
// $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));


if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');

// switch ($method) {
//     case 'GET':
//       // $id = $_GET['id'];
//       // $sql = "select * from temp".($id?" where id=$id":'');
//       $sql = "select * from temp where id = 1"; 
//       break;
//     case 'POST':
//       $id = $_POST["id"];
//       $pcode = $_POST["name"];
//       $sql = "insert into temp (Id, name) values ('$id', '$pcode')"; 
//       break;
// }

// // run SQL statement
// $result = mysqli_query($con,$sql);

// // die if SQL statement failed
// if (!$result) {
//   http_response_code(404);
//   die(mysqli_error($con));
// } 
// // echo $result;
// // return;
// if ($method == 'GET') {
//     if (!$id) echo '[';
//     for ($i=0 ; $i<mysqli_num_rows($result) ; $i++) {
//       echo ($i>0?',':'').json_encode(mysqli_fetch_object($result));
//     }
//     if (!$id) echo ']';
//   } elseif ($method == 'POST') {
//     echo json_encode($result);
//   } else {
//     echo mysqli_affected_rows($con);
//   }

$method = $_SERVER['REQUEST_METHOD']; 
if( $method == 'POST'){
    // echo "yes";
    $inputJSON = file_get_contents('php://input');
    echo $inputJSON;
    // $response = json_encode($_POST, true);
    // echo $response;
    // $response = json_encode($_GET);
    // echo $response;
}

$con->close();