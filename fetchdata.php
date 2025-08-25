<?php
// ========== CONFIG ==========

$client_id     = "dd4864512b85444fac8c87a3bf3c4f3a";
$client_secret = "944b1f4833824fee9f2ba16c3fbd511e";

// The Spotify Show ID (from URL: https://open.spotify.com/show/{SHOW_ID})
$show_id = "2MAi0BvDc6GTFvKFPXnkCL"; 

// ========== 1. Get Access Token ==========
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://accounts.spotify.com/api/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

$data = http_build_query([
    "grant_type" => "client_credentials"
]);

$headers = [
    "Authorization: Basic " . base64_encode($client_id . ":" . $client_secret),
    "Content-Type: application/x-www-form-urlencoded"
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);
if (!$result) {
    die("Error getting token: " . curl_error($ch));
}
curl_close($ch);

$response = json_decode($result, true);
$access_token = $response['access_token'] ?? null;

if (!$access_token) {
    die("Failed to get access token.");
}

// ========== 2. Fetch Show Episodes ==========
$show_url = "https://api.spotify.com/v1/shows/$show_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $show_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $access_token
]);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$raw_result = curl_exec($ch);
curl_close($ch);

// ========== 3. Output Nicely Formatted JSON ==========
header('Content-Type: application/json');
echo json_encode(json_decode($raw_result, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// ========== 1. Get Access Token ==========
/*$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://accounts.spotify.com/api/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

$data = http_build_query([
    "grant_type" => "client_credentials"
]);

$headers = [
    "Authorization: Basic " . base64_encode($client_id . ":" . $client_secret),
    "Content-Type: application/x-www-form-urlencoded"
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


$result = curl_exec($ch);
if (!$result) {
    die("Error getting token: " . curl_error($ch));
}
curl_close($ch);

$response = json_decode($result, true);
$access_token = $response['access_token'] ?? null;

if (!$access_token) {
    die("Failed to get access token.");
}

// ========== 2. Fetch Show Details ==========
function spotify_api_request($url, $access_token) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $access_token
    ]);

    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

$show_url = "https://api.spotify.com/v1/shows/$show_id/episodes";
$show_data = spotify_api_request($show_url, $access_token);
echo $show_data;

// ========== 3. Output Show Info ==========
echo "🎙 Podcast Title: " . $show_data['name'] . PHP_EOL;
echo "👤 Publisher: " . $show_data['publisher'] . PHP_EOL;
echo "📄 Description: " . substr($show_data['description'], 0, 200) . "..." . PHP_EOL;
echo "🖼 Cover Image: " . $show_data['images'][0]['url'] . PHP_EOL;
echo "Total Episodes: " . $show_data['total_episodes'] . PHP_EOL;
echo "Spotify Link: " . $show_data['external_urls']['spotify'] . PHP_EOL;*/
?>
