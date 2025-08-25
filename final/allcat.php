<?php
// Improved Spotify Charts scraper with better error handling and empty columns
include_once '../config.php';

// Initialize logging
$timestamp = date("Ymd_His");
$success_log = "success_$timestamp.log";
$error_log = "error_$timestamp.log";

function writeLog($message, $type = 'INFO') {
    global $success_log, $error_log;
    
    $formatted_message = "[" . date('Y-m-d H:i:s') . "] [$type] $message" . PHP_EOL;
    
    if ($type === 'ERROR' || $type === 'WARN') {
        file_put_contents($error_log, $formatted_message, FILE_APPEND | LOCK_EX);
    } else {
        file_put_contents($success_log, $formatted_message, FILE_APPEND | LOCK_EX);
    }
    
    // Also output to console
    echo $formatted_message;
}

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

$One = ["al", "ad", "ae", "az", "ba", "be", "bg", "bh", "bo", "bw", "ch", "cr", "cy", "cz", "do", 
    "dz", "ec", "ee", "eg", "ge", "gh", "gr", "gt", "hk", "hn", "hr", "hu", 
    "il", "is", "jm", "jo", "ke", "kh", "kr", "kw", "lb", "lt", "lu", "lv", 
    "ma", "mk", "mt", "mu", "mw", "my", "mz", "na", "ng", "ni", "np",  
    "om", "pa", "pe", "pt", "py","qa", "ro", "rs", "rw" , "sa", "sg", "si", 
    "sk", "sn", "sv", "th", "tn", "tr", "tt", "tw", "tz", "ua", "uy", "uz", 
    "vn", "za", "zm", "zw", "lc", "mc", "mg", "me", "bb", "bs", "bz", "fj",   
    "gm", "gy", "mn", "ne", "mo", "pg", "ps", "sc", "sl", "sm", "sr", "sz"];

$Three = ["ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", "id", "ie", "it", "jp", "nz", "no", "ph", "es", "nl", "pl"];
$Seventeen = ["au", "us", "gb", "br", "de", "mx", "se"];

// Categories
$CATEGORIES_20 = [
    "top", "trending", "arts", "business", "comedy", "education", "fiction", "history",
    "health%252520%2526%252520fitness", "leisure", "music", "news", 
    "religion%252520%2526%252520spirituality", "science", "society%252520%2526%252520culture",
    "sports", "technology", "true%252520crime", "tv%252520%2526%252520film"
];
$CATEGORIES_3 = ["top", "trending"];
$CATEGORIES_1 = ["top"];

function fetchWithRetry($url, $retries = 3, $delay = 5) {
    $attempt = 0;
    while ($attempt < $retries) {
        $attempt++;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Increased timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            writeLog("Attempt $attempt failed with error: $err", 'WARN');
        } elseif ($http_code >= 200 && $http_code < 300) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            } else {
                writeLog("Attempt $attempt: Failed to decode JSON - " . json_last_error_msg(), 'WARN');
            }
        } else {
            writeLog("Attempt $attempt: HTTP status $http_code", 'WARN');
        }

        if ($attempt < $retries) {
            writeLog("Waiting {$delay}s before retry...", 'INFO');
            sleep($delay);
        }
    }
    return null;
}

try {
    // Connect to MySQL
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        throw new Exception("Failed to connect to MySQL: " . $mysqli->connect_error);
    }
    writeLog("Connected to MySQL successfully", 'INFO');

    // Create table with timestamp-based name 
    $createSQL = "CREATE TABLE `spotify_charts_$timestamp` (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        showId VARCHAR(255) NOT NULL,
        showName VARCHAR(500) NOT NULL,
        showPublisher VARCHAR(500) DEFAULT NULL,
        showImageUrl TEXT DEFAULT NULL,
        showDescription TEXT DEFAULT NULL,
        countryName VARCHAR(100) NOT NULL,
        countryCode VARCHAR(10) NOT NULL,
        category VARCHAR(50) NOT NULL,
        chart_rank INT NOT NULL,
        y_rank INT DEFAULT NULL,
        movement INT DEFAULT NULL,
        cal_move VARCHAR(100) DEFAULT NULL,
        chartRankMove VARCHAR(100) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

    if (!$mysqli->query($createSQL)) {
        throw new Exception("Failed to create table: " . $mysqli->error);
    }
    writeLog("Table 'spotify_charts_$timestamp' created successfully.", 'INFO');

    // Prepare INSERT statement (now including cal_move)
    $insert_sql = "INSERT INTO `spotify_charts_$timestamp`
        (showId, showName, showPublisher, showImageUrl, showDescription,
     countryName, countryCode, category, chart_rank, chartRankMove)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $mysqli->prepare($insert_sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $countries = array_unique(array_merge($One, $Three, $Seventeen));
    $total_records = 0;
    $total_errors = 0;

    foreach ($countries as $country) {
        $categories = in_array($country, $Seventeen) ? $CATEGORIES_20 : 
                     (in_array($country, $Three) ? $CATEGORIES_3 : $CATEGORIES_1);

        foreach ($categories as $category) {
            $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
            writeLog("Fetching " . strtoupper($country) . " - $category → $url", 'INFO');

            $data = fetchWithRetry($url);
            if ($data === null) {
                writeLog("Failed to fetch data after retries: " . strtoupper($country) . " - $category", 'ERROR');
                $total_errors++;
                continue;
            }

            // Handle different response structures
            $items = [];
            if (isset($data['items']) && is_array($data['items'])) {
                $items = $data['items'];
            } elseif (is_array($data)) {
                $items = $data;
            } else {
                writeLog("Unexpected data structure for " . strtoupper($country) . " - $category", 'ERROR');
                $total_errors++;
                continue;
            }

            if (empty($items)) {
                writeLog("No items found for " . strtoupper($country) . " - $category", 'WARN');
                continue;
            }

            $rank = 1;
            $batch_errors = 0;
            
            foreach ($items as $item) {
                if (!is_array($item)) {
                    writeLog("Invalid item structure at rank $rank for " . strtoupper($country) . " - $category", 'WARN');
                    $batch_errors++;
                    continue;
                }

                $showUri = $item['showUri'] ?? "";
                $showId = "";
                if ($showUri) {
                    $parts = explode(":", $showUri);
                    $showId = end($parts);
                }

                $showName        = trim($item['showName'] ?? '');
                $showPublisher   = trim($item['showPublisher'] ?? '');
                $showImageUrl    = trim($item['showImageUrl'] ?? '');
                $showDescription = trim($item['showDescription'] ?? '');
                $countryName     = $COUNTRY_NAMES[$country] ?? strtoupper($country);
                $chartRankMove   = trim($item['chartRankMove'] ?? '');
                $categoryName    = ucwords(str_replace(['%252520', '%2526', '-'], [' ', ' And ', ' '], $category));

                // Validate required fields
                if (empty($showId) || empty($showName)) {
                    writeLog("Missing required data at rank $rank for " . strtoupper($country) . " - $category", 'WARN');
                    $batch_errors++;
                    $rank++;
                    continue;
                }

                $stmt->bind_param(
                    "ssssssssis",
                    $showId,
                    $showName,
                    $showPublisher,
                    $showImageUrl,
                    $showDescription,
                    $countryName,
                    $country,
                    $categoryName,
                    $rank,
                    $chartRankMove
                );

                if (!$stmt->execute()) {
                    writeLog("Insert failed for " . strtoupper($country) . " - $category - rank $rank: " . $stmt->error, 'ERROR');
                    $batch_errors++;
                } else {
                    $total_records++;
                }

                $rank++;
            }

            $successful_inserts = count($items) - $batch_errors;
            writeLog("Processed " . count($items) . " items ($successful_inserts successful, $batch_errors errors) for " . strtoupper($country) . " - $category", 'INFO');
            
            // Rate limiting
           // sleep(1);
           usleep(500000);
        }
    }

    $stmt->close();
    $mysqli->close();

    writeLog("Script completed successfully!", 'INFO');
    writeLog("Total records inserted: $total_records", 'INFO');
    writeLog("Total errors encountered: $total_errors", 'INFO');
    writeLog("Table created: spotify_charts_$timestamp", 'INFO');

} catch (Exception $e) {
    writeLog("Fatal error: " . $e->getMessage(), 'ERROR');
    
    // Clean up resources if they exist
    if (isset($stmt)) $stmt->close();
    if (isset($mysqli)) $mysqli->close();
    
    die("Script terminated due to fatal error. Check error log for details.");
}

writeLog("Script execution finished at " . date('Y-m-d H:i:s'), 'INFO');
?>