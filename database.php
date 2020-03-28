<?php
$host = "localhost"; 
$user = "root"; 
$password = "1234"; 
$dbname = "contestdb"; 

$con = mysqli_connect($host, $user, $password,$dbname);

if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: Content-Type');


$func = $_GET['func'];

if($func == 'savenewuser'){
    $user = $_GET['user'];
    $ip = $_GET['ip']; 
    $sql = "update users set username = '".$user."' where ip='".$ip."'"; 
    $result = mysqli_query($con,$sql);
    return;
}

if($func == 'logout'){
    $ip = $_GET['ip'];
    $sql = "delete from users where ip='".$ip."'"; 
    $result = mysqli_query($con,$sql);
    return;
}

if($func == 'logout_from_other_sessions'){
    $ip = $_GET['ip'];
    $user = $_GET['user'];
    $sql = "delete from users where username='".$user."' ip <>'".$ip."'"; 
    $result = mysqli_query($con,$sql);
    return;
}

if($func == 'savecreds'){
    $IP = $_GET['ip'];
    $token = $_GET['token'];
    $refresh = $_GET['refresh'];
    $sql = "delete from users where IP='".$IP."'";
    $result = mysqli_query($con,$sql);
    $extra = strtotime("+50 minute");
    $endtime = strtotime(date("Y-m-d h:i:sa",$extra));
    $sql = "insert into users (username,token,refresh,valid_till,session,IP) values ('demo','$token','$refresh','$endtime','1','$IP')";
    echo $sql;
    $result = mysqli_query($con,$sql);
}

$con->close();
