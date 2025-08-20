<?php
// Has both episodes data from top episodes and all the rest present in to_db.php, this will aid rank comparison
include_once '../config.php';

$COUNTRY_NAMES = [
    "ad" => "Andorra", "ae" => "United Arab Emirates", "al" => "Albania", "ar" => "Argentina", "at" => "Austria", "au" => "Australia",
    "az" => "Azerbaijan", "ba" => "Bosnia and Herzegovina", "be" => "Belgium", "bg" => "Bulgaria", "bh" => "Bahrain", "bo" => "Bolivia",
    "br" => "Brazil", "bw" => "Botswana", "ca" => "Canada", "ch" => "Switzerland", "cl" => "Chile", "co" => "Colombia", "cr" => "Costa Rica",
    "cy" => "Cyprus", "cz" => "Czechia", "de" => "Germany", "dk" => "Denmark", "do" => "Dominican Republic", "dz" => "Algeria",
    "ec" => "Ecuador", "ee" => "Estonia", "eg" => "Egypt", "es" => "Spain", "fi" => "Finland", "fr" => "France", "gb" => "United Kingdom",
    "ge" => "Georgia", "gh" => "Ghana", "gr" => "Greece", "gt" => "Guatemala", "hk" => "Hong Kong", "hn" => "Honduras", "hr" => "Croatia",
    "hu" => "Hungary", "id" => "Indonesia", "ie" => "Ireland", "il" => "Israel", "in" => "India", "is" => "Iceland", "it" => "Italy",
    "jm" => "Jamaica", "jo" => "Jordan", "jp" => "Japan", "ke" => "Kenya", "kh" => "Cambodia", "kr" => "South Korea", "kw" => "Kuwait",
    "lb" => "Lebanon", "lt" => "Lithuania", "lu" => "Luxembourg", "lv" => "Latvia", "ma" => "Morocco", "mk" => "North Macedonia",
    "mt" => "Malta", "mu" => "Mauritius", "mw" => "Malawi", "mx" => "Mexico", "my" => "Malaysia", "mz" => "Mozambique", "na" => "Namibia",
    "ng" => "Nigeria", "ni" => "Nicaragua", "nl" => "Netherlands", "no" => "Norway", "np" => "Nepal", "nz" => "New Zealand", "om" => "Oman",
    "pa" => "Panama", "pe" => "Peru", "ph" => "Philippines", "pl" => "Poland", "pt" => "Portugal", "py" => "Paraguay", "qa" => "Qatar",
    "ro" => "Romania", "rs" => "Serbia", "rw" => "Rwanda", "sa" => "Saudi Arabia", "se" => "Sweden", "sg" => "Singapore", "si" => "Slovenia",
    "sk" => "Slovakia", "sn" => "Senegal", "sv" => "El Salvador", "th" => "Thailand", "tn" => "Tunisia", "tr" => "Turkey",
    "tt" => "Trinidad and Tobago", "tw" => "Taiwan", "tz" => "Tanzania", "ua" => "Ukraine", "us" => "United States",
    "uy" => "Uruguay", "uz" => "Uzbekistan", "vn" => "Vietnam", "za" => "South Africa", "zm" => "Zambia", "zw" => "Zimbabwe",
    "lc" => "Saint Lucia", "mc" => "Monaco", "mg" => "Madagascar", "me" => "Montenegro", "bb" => "Barbados", 
    "bs" => "Bahamas", "bz" => "Belize", "fj" => "Fiji", "gm" => "Gambia", "gy" => "Guyana", "mn" => "Mongolia", 
    "ne" => "Niger", "mo" => "Macao", "pg" => "Papua New Guinea", "ps" => "Palestine", "sc" => "Seychelles", 
    "sl" => "Sierra Leone", "sm" => "San Marino", "sr" => "Suriname", "sz" => "Eswatini"
];

$One =["al", "ad", "ae", "az", "ba", "be", "bg", "bh",
    "bo", "bw", "ch", "cr", "cy", "cz", "do", 
    "dz", "ec", "ee", "eg",
    "ge", "gh", "gr", "gt",  "hk", "hn", "hr", "hu", 
 "il", "is", "jm", "jo",
    "ke", "kh", "kr", "kw", "lb", "lt", "lu", "lv", 
    "ma", "mk", "mt", "mu", 
    "mw", "my", "mz", "na", "ng", "ni",  "np",  
    "om", "pa", "pe", "pt", "py","qa", 
    "ro", "rs", "rw" , 
    "sa", "sg", "si", "sk", "sn", "sv", "th", 
    "tn", "tr", "tt", "tw", "tz", "ua", "uy", "uz", 
    "vn", "za", "zm", "zw", 
    "lc", "mc", "mg", "me", "bb", "bs", "bz", "fj",   
    "gm", "gy", "mn", "ne", "mo", "pg", "ps", "sc",   
    "sl", "sm", "sr", "sz"  ];

$Three = ["ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", "id", "ie", "it", "jp", "nz", "no", "ph", "es", "nl", "pl"];
$Seventeen = ["au", "us", "gb", "br", "de", "mx", "se"];

$CATEGORIES_20 = [
    "top", "trending", "top_episodes", "arts", "business", "comedy", "education", "fiction", "history", "health%252520%2526%252520fitness",
    "leisure", "music", "news", "religion%252520%2526%252520spirituality", "science",
    "society%252520%2526%252520culture", "sports", "technology", "true%252520crime", "tv%252520%2526%252520film"
];

$CATEGORIES_3 = ["top", "trending", "top_episodes"];
$CATEGORIES_1 = ["top"];

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
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Create table with timestamp-based name
$timestamp = date("Ymd_His");
$newTable = "spotify_charts_$timestamp";

$createSQL = "CREATE TABLE `$newTable` (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    showId VARCHAR(255) NOT NULL,
    showName VARCHAR(255) NOT NULL,
    showPublisher VARCHAR(255) DEFAULT NULL,
    showImageUrl TEXT DEFAULT NULL,
    showDescription TEXT DEFAULT NULL,
    countryName VARCHAR(100) NOT NULL,
    countryCode VARCHAR(10) NOT NULL,
    category VARCHAR(50) NOT NULL,
    chart_rank INT NOT NULL,
    chartRankMove VARCHAR(100) DEFAULT NULL,
    episodeId VARCHAR(255) DEFAULT NULL,
    episodeName VARCHAR(255) DEFAULT NULL,
    movement INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

if (!$mysqli->query($createSQL)) {
    die("Failed to create table: " . $mysqli->error);
}
echo "[INFO] Table '$newTable' created successfully.\n";

$insert_sql = "INSERT INTO `$newTable`
    (showId, showName, showPublisher, showImageUrl, showDescription,
     countryName, countryCode, category, chart_rank, chartRankMove, episodeId, episodeName, movement)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($insert_sql);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}

$countries = array_unique(array_merge($One, $Three, $Seventeen));

foreach ($countries as $country) {
    $categories = in_array($country, $Seventeen) ? $CATEGORIES_20 : 
                 (in_array($country, $Three) ? $CATEGORIES_3 : $CATEGORIES_1);

    foreach ($categories as $category) {
        $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
        echo "[INFO] Fetching " . strtoupper($country) . " - $category → $url\n";

        $data = fetchWithRetry($url);
        if ($data === null) {
            echo "[ERROR] Failed to fetch data after retries: " . strtoupper($country) . " - $category\n";
            continue;
        }

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
        } elseif (is_array($data)) {
            $items = $data;
        }

        $rank = 1;
        foreach ($items as $item) {
            // Show details
            $showUri = isset($item['showUri']) ? $item['showUri'] : "";
            $showId = $showUri ? explode(":", $showUri)[count(explode(":", $showUri)) - 1] : "";

            $showName        = $item['showName'] ?? '';
            $showPublisher   = $item['showPublisher'] ?? '';
            $showImageUrl    = $item['showImageUrl'] ?? '';
            $showDescription = $item['showDescription'] ?? '';
            $countryName     = $COUNTRY_NAMES[$country] ?? '';
            $categoryName    = ucwords(str_replace('-', ' ', $category));
            $chartRankMove   = $item['chartRankMove'] ?? "";
            
            // Episode details (only for top_episodes)
            $episodeId   = null;
            $episodeName = null;
            if ($category === "top_episodes") {
                if (!empty($item['episodeUri'])) {
                    $parts = explode(':', $item['episodeUri']);
                    $episodeId = $parts[2] ?? '';
                } 
                $episodeName = $item['episodeName'] ?? ''; 
            }

            $stmt->bind_param(
                "ssssssssisisi",
                $showId,
                $showName,
                $showPublisher,
                $showImageUrl,
                $showDescription,
                $countryName,
                $country,
                $categoryName,
                $rank,
                $chartRankMove,
                $episodeId,
                $episodeName,
                null // movement set to null - will be calculated later
            );

            if (!$stmt->execute()) {
                echo "[ERROR] Insert failed for " . strtoupper($country) . " - $category - " . $stmt->error . "\n";
            }

            $rank++;
        }

        echo "[OK] Inserted " . count($items) . " rows for " . strtoupper($country) . " - $category\n";
        sleep(2);
    }
}

$stmt->close();
$mysqli->close();

echo "[DONE] Completed fetching and inserting podcast charts into table '$newTable'.\n";
?>