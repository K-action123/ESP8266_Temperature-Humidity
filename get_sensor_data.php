<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin (for development)

$conn = new mysqli("localhost", "root", "", "sensor_data");
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection Failed: " . $conn->connect_error]);
    exit();
}

$response = [];

// --- Get Latest Data for Cards ---
$latest_result = $conn->query("SELECT temperature, humidity, start_time FROM sensor_logs ORDER BY id DESC LIMIT 1");
if ($latest_result->num_rows > 0) {
    $latest_row = $latest_result->fetch_assoc();
    $response['current_data'] = [
        'temperature' => $latest_row['temperature'],
        'humidity' => $latest_row['humidity'],
        'last_updated' => $latest_row['start_time']
    ];
} else {
    $response['current_data'] = [
        'temperature' => 'N/A',
        'humidity' => 'N/A',
        'last_updated' => 'No data yet'
    ];
}

// --- Get Historical Data for Graph (e.g., last 24 hours or last 100 entries) ---
// For a graph, you might want to fetch more entries, e.g., the last 50-100 unique changes.
// Or, fetch data for a specific time range (e.g., last 24 hours)
$historical_data = [];
$historical_result = $conn->query("SELECT start_time, temperature, humidity FROM sensor_logs ORDER BY id DESC LIMIT 4"); // Adjust LIMIT as needed
// OR for a time range (e.g., last 24 hours):
// $twenty_four_hours_ago = date("Y-m-d H:i:s", strtotime('-24 hours'));
// $historical_result = $conn->query("SELECT start_time, temperature, humidity FROM sensor_logs WHERE start_time >= '$twenty_four_hours_ago' ORDER BY start_time ASC");

if ($historical_result->num_rows > 0) {
    while ($row = $historical_result->fetch_assoc()) {
        $historical_data[] = [
            'time' => $row['start_time'],
            'temperature' => $row['temperature'],
            'humidity' => $row['humidity']
        ];
    }
    // Reverse the array if fetching DESC, so it's chronological for graphing
    $response['historical_data'] = array_reverse($historical_data);
} else {
    $response['historical_data'] = [];
}

$conn->close();

echo json_encode($response);
?>