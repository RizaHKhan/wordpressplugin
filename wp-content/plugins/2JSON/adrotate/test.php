<?php

$site = $
$user = $_POST['user'];
$password = $_POST['password'];
$db = $_POST['db'];
$prefix = $_POST['prefix'];

$connect = mysqli_connect($host, $user, $password, $db) or die('Unable to connect to database');
$sql = 'SELECT * FROM wp_adrotate';
$result = mysqli_query($connect, $sql);
$json_array = array();

while($row = mysqli_fetch_assoc($result)) {
    $json_array[] = $row;
}

if($json_array) {
    echo '<pre>';
    print_r($json_array);
    echo '</pre>';
}