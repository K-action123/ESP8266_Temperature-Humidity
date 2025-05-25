<?php

$conn = new mysqli("localhost", "root", "", "sensor_data");
if($conn->connect_error){
    die("connection failed".$conn->connect_error);
}
$temperature = $_GET['temperature'];
$humidity = $_GET['humidity'];

$sql = "INSERT INTO dht11_data(temperature, humidity) values('$temperature', '$humidity')";
if($conn->query($sql)=== TRUE){
    echo"Data stored successfully";
}else{
    echo"Error: ". $sql ."<br>" . $conn->error;
}
$conn->close();
?>