<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
include './config.php';
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get dropdown selection (countryCode and category)
$countryCode = isset($_GET['countryCode']) ? $_GET['countryCode'] : "us";
$category = isset($_GET['category']) ? $_GET['category'] : "top";

// Table name (update dynamically if needed)
$tableName = "spotify_charts_20250820_104527";

// SQL query without movement/diff
$stmt = $conn->prepare("SELECT showId, showName, showPublisher, showImageUrl, showDescription, countryName, countryCode, category
                        FROM `$tableName`
                        WHERE countryCode = ? AND category = ?");
$stmt->bind_param("ss", $countryCode, $category);

// Execute
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
