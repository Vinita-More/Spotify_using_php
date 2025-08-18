<?php
include_once './config.php';

// Connect to MySQL
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) die("Failed to connect to MySQL: " . $mysqli->connect_error);

// Step 1: Check if 'today' exists and archive old table
$res = $mysqli->query("SHOW TABLES LIKE 'today'");
if ($res->num_rows > 0) {
    $timestamp = date("Ymd_His");
    $old_table = "{$timestamp}_spotify_charts_today";
    $mysqli->query("RENAME TABLE today TO `$old_table`") or die($mysqli->error);
} else {
    $old_table = null;
}


// Step 2: Create fresh 'today' table (structure can match old_table or base template)
$mysqli->query("CREATE TABLE today LIKE `14-08-with-top-episodes`") or die($mysqli->error);

// Step 3: Prepare insert statement
$insert_sql = "INSERT INTO today (showId, showName, showPublisher, showImageUrl, showDescription,
                 countryName, countryCode, category, episodeId, chart_rank, created_at, updated_at)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = $mysqli->prepare($insert_sql) or die($mysqli->error);

$COUNTRY_NAMES = [
    "ar" => "Argentina", "at" => "Austria", "au" => "Australia",
    "br" => "Brazil", "ca" => "Canada", "cl" => "Chile", 
    "co" => "Colombia", "de" => "Germany", "dk" => "Denmark", 
    "es" => "Spain", "fi" => "Finland", "fr" => "France", 
    "gb" => "United Kingdom", "id" => "Indonesia", "ie" => "Ireland", 
    "in" => "India", "it" => "Italy", "jp" => "Japan", 
    "mx" => "Mexico", "nl" => "Netherlands", "no" => "Norway", 
    "nz" => "New Zealand", "ph" => "Philippines", "se" => "Sweden",
    "us" => "United States", "pl" => "Poland",
];

$Three = [ 
    "ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", "pl",
    "id", "ie", "it", "jp", "nz", "no", "ph", "es", "nl"
];
$Seventeen = [
    "au", "us", "gb", "br", "de", "mx", "se"
];
$CATEGORIES_3 = ["top", "top_episodes", "trending"];
$CATEGORIES_20 = [
    "top", "trending", "top_episodes", "arts", "business", "comedy", "education", "fiction", "history", 
    "health%252520%2526%252520fitness", "leisure", "music", "news", "religion%252520%2526%252520spirituality", 
    "science", "society%252520%2526%252520culture", "sports", "technology", "true%252520crime", "tv%252520%2526%252520film"
];


// Step 4: Fetch API data and insert
$countries = array_merge($Three, $Seventeen);
foreach ($countries as $country) {
    $categories = in_array($country, $Seventeen) ? $CATEGORIES_20 : $CATEGORIES_3;
    foreach ($categories as $category) {
        $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
        $data = fetchWithRetry($url);
        if (!$data) continue;
        $items = $data['items'] ?? [];
        $rank = 1;
        foreach ($items as $item) {
            $showUri = $item['showUri'] ?? '';
            $showId = $showUri ? explode(":", $showUri)[2] ?? '' : '';
            $episodeUri = $item['episodeUri'] ?? null;
            $episodeId = $episodeUri ? explode(":", $episodeUri)[2] ?? null : null;

            $stmt->bind_param(
                "sssssssssii",
                $showId,
                $item['showName'] ?? '',
                $item['showPublisher'] ?? '',
                $item['showImageUrl'] ?? '',
                $item['showDescription'] ?? '',
                $COUNTRY_NAMES[$country] ?? '',
                $country,
                $category,
                $episodeId,
                $rank
            );
            $stmt->execute();
            $rank++;
        }
    }
}

// Step 5: Add index for efficient rank comparison if old table exists
if ($old_table) {
    $mysqli->query("CREATE INDEX idx_country_category_show 
                    ON `13-08-with-top-episodes` (countryCode, category, showId, episodeId)") or die($mysqli->error);

    // Step 6: Compare ranks only for relevant country/category using filtered query
    $mysqli->query("
        UPDATE today t
        JOIN `$old_table` y
        ON t.showId = y.showId AND t.episodeId <=> y.episodeId
        SET t.diff = y.chart_rank - t.chart_rank,
            t.movement = CASE
                WHEN y.chart_rank > t.chart_rank THEN 'up'
                WHEN y.chart_rank < t.chart_rank THEN 'down'
                ELSE 'same'
            END
        WHERE t.countryCode = 'us' AND t.category = 'top'
    ") or die($mysqli->error);
}

$stmt->close();
$mysqli->close();
echo "[DONE] Data inserted and rank comparison complete.\n";

// --- Utility function ---
function fetchWithRetry($url, $retries = 3, $delay = 5) {
    $attempt = 0;
    while ($attempt < $retries) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$err && $http_code >= 200 && $http_code < 300) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) return $data;
        }
        $attempt++;
        sleep($delay);
    }
    return null;
}
?>
