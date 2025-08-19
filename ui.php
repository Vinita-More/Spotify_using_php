<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// DB connection
$host = "localhost";
$user = "your_username";
$pass = "your_password";
$dbname = "your_database";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Get params from frontend
$country = isset($_GET['country']) ? $conn->real_escape_string($_GET['country']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

if (empty($country) || empty($category)) {
    echo json_encode(["error" => "country and category are required"]);
    exit;
}

// Fetch podcasts
$sql = "
    SELECT rank, title, host, trending
    FROM podcasts
    WHERE countryCode = '$country' AND category = '$category'
    ORDER BY rank ASC
    LIMIT 50
";

$result = $conn->query($sql);

$podcasts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $podcasts[] = $row;
    }
}

echo json_encode($podcasts);

$conn->close();
