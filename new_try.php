<?php
include_once './config.php';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Step 1: Check if `today` exists
$res = $mysqli->query("SHOW TABLES LIKE 'today'");
if ($res->num_rows > 0) {
    // Step 2: Rename old table
    $timestamp = date("Ymd_His");
    $old_table = "{$timestamp}_changed_spotify_charts_today";
    if (!$mysqli->query("RENAME TABLE today TO `$old_table`")) {
        die("Failed to rename table: " . $mysqli->error);
    }

    // Step 3: Create a fresh today table
    if (!$mysqli->query("CREATE TABLE today LIKE `$old_table`")) {
        die("Failed to create table: " . $mysqli->error);
    }
} else {
    // If no table exists yet, create from your base table structure
    $mysqli->query("CREATE TABLE today LIKE `14-08-with-top-episodes`");
    $old_table = null;
}

// Step 4: Prepare insert into `today`
$insert_sql = "INSERT INTO today
    (showId, showName, showPublisher, showImageUrl, showDescription,
     countryName, countryCode, category, chart_rank, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = $mysqli->prepare($insert_sql);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}

// Fetch + insert (reuse your API fetching logic here)
// ─────────────────────────────────────────────
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

$Three = [ 
    "ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", 
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

$countries = array_unique(array_merge($Three, $Seventeen));

foreach ($countries as $country) {
    $categories = in_array($country, $Seventeen) ? $CATEGORIES_20 : $CATEGORIES_3;
    foreach ($categories as $category) {
        $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
        
        $data = fetchWithRetry($url);
        if (!$data) continue;

        $items = isset($data['items']) ? $data['items'] : (is_array($data) ? $data : []);
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
                $COUNTRY_NAMES[$country],
                $country,
                $categoryName,
                $rank
            );
            $stmt->execute();
            $rank++;
        }
    }
}
// ─────────────────────────────────────────────

$stmt->close();

// Step 5: Compare with old table if exists
if ($old_table) {
    // Add diff and movement columns if missing
    $result = $mysqli->query("SHOW COLUMNS FROM today LIKE 'diff'");
if ($result->num_rows === 0) {
    // Column doesn't exist, so create it
    $mysqli->query("ALTER TABLE today ADD COLUMN diff INT DEFAULT NULL");
}
    $result = $mysqli->query("SHOW COLUMNS FROM today LIKE 'movement'");
if ($result->num_rows === 0) {
    // Column doesn't exist, so create it
    $mysqli->query("ALTER TABLE today ADD COLUMN movement INT DEFAULT NULL");
}
    // Set-based update for rank difference
    $mysqli->query("
        UPDATE today t
        JOIN $old_table y
        ON CONCAT_WS('-', t.showId, t.category, t.countryCode, COALESCE(t.episodeId, ''))
           = CONCAT_WS('-', y.showId, y.category, y.countryCode, COALESCE(y.episodeId, ''))
        SET 
            t.diff = (y.chart_rank - t.chart_rank),
            t.movement = CASE
                WHEN y.chart_rank > t.chart_rank THEN 'up'
                WHEN y.chart_rank < t.chart_rank THEN 'down'
                ELSE 'same'
            END
    ");
}

$mysqli->close();
echo "[DONE] Data updated and rank comparison complete.\n";

/**
 * Fetch API data with retries
 */
function fetchWithRetry($url, $retries = 3, $delay = 5) {
    $attempt = 0;
    while ($attempt < $retries) {
        $attempt++;
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
        if ($attempt < $retries) sleep($delay);
    }
    return null;
}
?>
