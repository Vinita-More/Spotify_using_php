
<?php
// has data about all categories and countries but the data about top episodes is not proper since there is a mix up the rank comparison is not working properly
include_once './config.php';

$COUNTRY_NAMES = [
    "ar" => "Argentina", "at" => "Austria", "au" => "Australia",
    "br" => "Brazil", "ca" => "Canada", "cl" => "Chile", 
    "co" => "Colombia", "de" => "Germany", "dk" => "Denmark", 
    "es" => "Spain", "fi" => "Finland", "fr" => "France", 
    "gb" => "United Kingdom", "id" => "Indonesia", "ie" => "Ireland", 
    "in" => "India", "it" => "Italy", "jp" => "Japan", 
    "mx" => "Mexico", "nl" => "Netherlands", "no" => "Norway", 
    "nz" => "New Zealand", "ph" => "Philippines", "se" => "Sweden",
    "us" => "United States", 
];

// Country groups
$Three = ["ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", "id", "ie", "it", "jp", "nz", "no", "ph", "es", "nl"];
$Seventeen = ["au", "us", "gb", "br", "de", "mx", "se"];

$CATEGORIES_20 = [
    "top", "trending", "top_episodes", "arts", "business", "comedy", "education", "fiction", "history", "health%252520%2526%252520fitness",
    "leisure", "music", "news", "religion%252520%2526%252520spirituality", "science",
    "society%252520%2526%252520culture", "sports", "technology", "true%252520crime", "tv%252520%2526%252520film"];

$CATEGORIES_3 = ["top", "trending", "top_episodes"];

/**
 * Make HTTP GET request with retry logic.
 * @param string $url
 * @param int $retries
 * @param int $delay Seconds between retries
 * @return mixed|null JSON-decoded response or null on failure
 */
function fetchWithRetry($url, $retries = 3, $delay = 5) {
    $attempt = 0;
    while ($attempt < $retries) {
        $attempt++;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            echo "[WARN] Attempt $attempt failed with error: $err\n";
        } elseif ($http_code >= 200 && $http_code < 300) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            } else {
                echo "[WARN] Attempt $attempt: Failed to decode JSON\n";
            }
        } else {
            echo "[WARN] Attempt $attempt: HTTP status $http_code\n";
        }

        if ($attempt < $retries) {
            sleep($delay);
        }
    }
    return null;
}

// Connect to MySQL
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name="spotify-using-php");
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

$insert_sql = "INSERT INTO spotify_podcast_charts_with_category
    (showId, showName, showPublisher, showImageUrl, showDescription,
     countryName, countryCode, category, chart_rank, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

$stmt = $mysqli->prepare($insert_sql);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}

$countries = array_unique(array_merge($Three, $Seventeen));

foreach ($countries as $country) {
    if (in_array($country, $Seventeen)) {
        $categories = $CATEGORIES_20;
    } else {
        $categories = $CATEGORIES_3;
    }

    foreach ($categories as $category) {
        $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
        echo "[INFO] Fetching " . strtoupper($country) . " - $category → $url\n";

        $data = fetchWithRetry($url);
        if ($data === null) {
            echo "[ERROR] Failed to fetch data after retries: " . strtoupper($country) . " - $category\n";
            continue;
        }

        // Extract items array from response
        if (isset($data['items'])) {
            $items = $data['items'];
        } elseif (is_array($data)) {
            $items = $data;
        } else {
            $items = [];
        }

        $rank = 1;
        foreach ($items as $item) {
            $showUri = isset($item['showUri']) ? $item['showUri'] : "";
            $showId = $showUri ? explode(":", $showUri)[count(explode(":", $showUri)) - 1] : "";
            $showName        = $item['showName'] ?? '';
    $showPublisher   = $item['showPublisher'] ?? '';
    $showImageUrl    = $item['showImageUrl'] ?? '';
    $showDescription = $item['showDescription'] ?? '';
    $countryName     = $COUNTRY_NAMES[$country] ?? '';
    $categoryName    = ucwords(str_replace('-', ' ', $category));
            $stmt->bind_param(
                "ssssssssi",
        $showId,
        $showName,
        $showPublisher,
        $showImageUrl,
        $showDescription,
        $countryName,
        $country,
        $categoryName,
                $rank
            );

            if (!$stmt->execute()) {
                echo "[ERROR] Insert failed for " . strtoupper($country) . " - $category - " . $stmt->error . "\n";
            }

            $rank++;
        }

        echo "[OK] Inserted " . count($items) . " rows for " . strtoupper($country) . " - $category\n";

        // Be kind to server
        sleep(1);
    }
}

$stmt->close();
$mysqli->close();

echo "[DONE] Completed fetching and inserting podcast charts.\n";

?>