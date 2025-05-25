<?php

$conn=new mysqli("localhost", "root", "", "sensor_data");
if($conn->connect_error){
    die("Connection Failed". $conn->connect_error);
}

$temperature = $_GET['temperature'];
$humidity = $_GET['humidity'];

// Define a small tolerance for floating point comparisons
$temp_tolerance = 0.5; // e.g., 0.05 degrees Celsius
$hum_tolerance = 2.0;   // e.g., 0.5% humidity

//getting last records
$result=$conn->query("SELECT id, temperature, humidity, start_time FROM sensor_logs ORDER BY id DESC LIMIT 1");
$now=time();

if($result->num_rows > 0){ // Check if there's an existing record
    $row=$result->fetch_assoc();

    // Compare with tolerance for floating point numbers
    $is_temp_same = abs($row['temperature'] - $temperature) < $temp_tolerance;
    $is_hum_same = abs($row['humidity'] - $humidity) < $hum_tolerance;

    if($is_temp_same && $is_hum_same){
        $start = strtotime($row['start_time']);
        $duration=$now - $start;

        $update_sql = "UPDATE sensor_logs SET duration_seconds = $duration WHERE id={$row['id']}";
        if($conn->query($update_sql) === TRUE){
            echo "Data unchanged. Duration updated for ID: " . $row['id'] . ". Duration: " . $duration . "s";
        } else {
            echo "Error updating duration: " . $conn->error;
        }
    }else{
        // Data has changed, insert a new record
        $nowFormatted = date("Y-m-d H:i:s", $now);
        $sql = "INSERT INTO sensor_logs(temperature, humidity, start_time, duration_seconds) VALUES('$temperature','$humidity','$nowFormatted', 0)";
        if($conn->query($sql) === TRUE){
            echo "New data stored. Temperature: $temperature, Humidity: $humidity";
        } else {
            echo "Error inserting new data: " . $conn->error;
        }
    }
}else{
    // This is the very first record, insert it
    $nowFormatted = date("Y-m-d H:i:s", $now);
    $sql = "INSERT INTO sensor_logs(temperature, humidity, start_time, duration_seconds) VALUES('$temperature','$humidity','$nowFormatted', 0)";
    if($conn->query($sql) === TRUE){
        echo "First data logged. Temperature: $temperature, Humidity: $humidity";
    } else {
        echo "Error logging first data: " . $conn->error;
    }
}
$conn->close();
?>