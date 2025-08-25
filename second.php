<?php
include_once './config.php';


// ==========================
// Country + Category Setup
// ==========================
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

$One = [
    "al", "ad", "ae", "az", "ba", "be", "bg", "bh", "bo", "bw", "ch", "cr", 
    "cy", "cz", "do", "dz", "ec", "ee", "eg", "ge", "gh", "gr", "gt", "hk", 
    "hn", "hr", "hu", "il", "is", "jm", "jo", "ke", "kh", "kr", "kw", "lb", 
    "lt", "lu", "lv", "ma", "mk", "mt", "mu", "mw", "my", "mz", "na", "ng", 
    "ni", "np", "om", "pa", "pe", "pt", "py","qa", "ro", "rs", "rw" , "sa", 
    "sg", "si", "sk", "sn", "sv", "th", "tn", "tr", "tt", "tw", "tz", "ua", 
    "uy", "uz", "vn", "za", "zm", "zw", "lc", "mc", "mg", "me", "bb", "bs", 
    "bz", "fj", "gm", "gy", "mn", "ne", "mo", "pg", "ps", "sc", "sl", "sm", 
    "sr", "sz"
];

$Three = [
    "ar", "at", "ca", "cl", "co", "dk", "fi", "fr", "in", "id", "ie", "it", 
    "jp", "nz", "no", "ph", "es", "nl", "pl"
];

$Seventeen = [
    "au", "us", "gb", "br", "de", "mx", "se"
];

$CATEGORIES_20 = [
    "top", "trending", "arts", "business", "comedy", "education", "fiction", "history",
    "health%252520%2526%252520fitness", "leisure", "music", "news", 
    "religion%252520%2526%252520spirituality", "science", "society%252520%2526%252520culture",
    "sports", "technology", "true%252520crime", "tv%252520%2526%252520film"
];
$CATEGORIES_3 = ["top", "trending"];
$CATEGORIES_1 = ["top"];

// ==========================
// Logging
// ==========================
$timestamp = date("Ymd_His");
$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$success_log = "$log_dir/success_$timestamp.log";
$error_log   = "$log_dir/error_$timestamp.log";

function writeLog($message, $type = 'INFO') {
    global $success_log, $error_log;
    $formatted_message = "[" . date('Y-m-d H:i:s') . "] [$type] $message" . PHP_EOL;
    
    if ($type === 'ERROR' || $type === 'WARN') {
        file_put_contents($error_log, $formatted_message, FILE_APPEND | LOCK_EX);
    } else {
        file_put_contents($success_log, $formatted_message, FILE_APPEND | LOCK_EX);
    }
    echo $formatted_message;
}

// ==========================
// Fetch with retry and rate limiting
// ==========================
function fetchWithRetry($url, $retries = 3, $delay = 2) {
    $attempt = 0;
    while ($attempt < $retries) {
        $attempt++;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; SpotifyChartsScraper/1.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            writeLog("Attempt $attempt failed with cURL error: $err", 'WARN');
        } elseif ($http_code === 429) {
            writeLog("Rate limited (429). Waiting longer...", 'WARN');
            sleep($delay * 3); // Wait longer for rate limits
            continue;
        } elseif ($http_code >= 200 && $http_code < 300) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            } else {
                writeLog("Attempt $attempt: Invalid JSON response - " . json_last_error_msg(), 'WARN');
            }
        } else {
            writeLog("Attempt $attempt: HTTP status $http_code", 'WARN');
        }

        if ($attempt < $retries) {
            sleep($delay * $attempt); // Exponential backoff
        }
    }
    return null;
}

// ==========================
// Database connection with error handling
// ==========================
try {
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        throw new Exception("MySQL connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8mb4");
} catch (Exception $e) {
    writeLog($e->getMessage(), 'ERROR');
    die($e->getMessage());
}

// ==========================
// Create today table with better indexing
// ==========================
$today_table = `spotify_charts_$timestamp`;
$createSQL = "CREATE TABLE `$today_table` (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    showId VARCHAR(255) NOT NULL,
    showName VARCHAR(500) NOT NULL,
    showPublisher VARCHAR(500) DEFAULT NULL,
    showImageUrl TEXT DEFAULT NULL,
    showDescription TEXT DEFAULT NULL,
    countryName VARCHAR(100) NOT NULL,
    countryCode VARCHAR(10) NOT NULL,
    category VARCHAR(100) NOT NULL,
    chart_rank INT NOT NULL,
    y_rank INT DEFAULT NULL,
    chartRankMove VARCHAR(100) DEFAULT NULL,
    cal_move ENUM('new', 'up', 'down', 'unchanged') NOT NULL DEFAULT 'new',
    movement INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_show_country_cat (showId, countryCode, category),
    INDEX idx_country_category (countryCode, category),
    INDEX idx_rank (chart_rank),
    INDEX idx_movement (cal_move)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$mysqli->query($createSQL)) {
    writeLog("Failed to create table: " . $mysqli->error, 'ERROR');
    die("Failed to create table: " . $mysqli->error);
}
writeLog("Table '$today_table' created successfully.", 'INFO');

// ==========================
// Prepare insert statement
// ==========================
$insert_sql = "INSERT INTO `$today_table`
    (showId, showName, showPublisher, showImageUrl, showDescription,
     countryName, countryCode, category, chart_rank, chartRankMove)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $mysqli->prepare($insert_sql);
if (!$stmt) {
    writeLog("Prepare failed: " . $mysqli->error, 'ERROR');
    die("Prepare failed: " . $mysqli->error);
}

// ==========================
// Fetch & Insert with better error handling
// ==========================
$countries = array_unique(array_merge($One, $Three, $Seventeen));
$failed_combos = [];
$total_requests = 0;
$successful_requests = 0;

foreach ($countries as $country) {
    $categories = in_array($country, $Seventeen) ? $CATEGORIES_20 : 
                 (in_array($country, $Three) ? $CATEGORIES_3 : $CATEGORIES_1);

    foreach ($categories as $category) {
        $total_requests++;
        $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
        writeLog("Fetching [" . sprintf("%03d", $total_requests) . "] " . strtoupper($country) . " - $category", 'INFO');

        $data = fetchWithRetry($url);
        if ($data === null) {
            $failed_combos[] = [$country, $category];
            writeLog("Failed after retries: " . strtoupper($country) . " - $category", 'ERROR');
            continue;
        }

        $items = $data['items'] ?? (is_array($data) ? $data : []);
        if (empty($items)) {
            writeLog("No data returned for $country - $category", 'WARN');
            continue;
        }

        $rank = 1;
        $inserted_count = 0;
        
        foreach ($items as $item) {
            // Better showId extraction
            $showUri = $item['showUri'] ?? "";
            $showId = "";
            if ($showUri && strpos($showUri, ':') !== false) {
                $parts = explode(":", $showUri);
                $showId = end($parts);
            }
            
            // Sanitize and validate data
            $showName = trim($item['showName'] ?? '');
            if (empty($showName)) {
                writeLog("Skipping item with empty showName for $country - $category", 'WARN');
                $rank++;
                continue;
            }
            
            $showPublisher = trim($item['showPublisher'] ?? '');
            $showImageUrl = filter_var($item['showImageUrl'] ?? '', FILTER_VALIDATE_URL) ?: '';
            $showDescription = trim($item['showDescription'] ?? '');
            $countryName = $COUNTRY_NAMES[$country] ?? '';
            $categoryName = ucwords(str_replace(['%252520', '%2526', '-'], [' ', ' And ', ' '], $category));
            $chartRankMove = $item['chartRankMove'] ?? "";

            // Bind and execute
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
            
            if ($stmt->execute()) {
                $inserted_count++;
            } else {
                writeLog("Insert failed for $country - $category - rank $rank: " . $stmt->error, 'ERROR');
            }
            $rank++;
        }
        
        writeLog("Successfully inserted $inserted_count/" . count($items) . " items for $country - $category", 'INFO');
        $successful_requests++;
        
        // Rate limiting - be nice to the API
        usleep(500000); // 0.5 second delay between requests
    }
}

writeLog("Initial fetch complete: $successful_requests/$total_requests successful", 'INFO');

// ==========================
// Retry failed combos with longer delays
// ==========================
if (!empty($failed_combos)) {
    writeLog("Retrying " . count($failed_combos) . " failed combinations...", 'INFO');
    sleep(5); // Wait before retry batch
    
    foreach ($failed_combos as $combo) {
        list($country, $category) = $combo;
        $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
        writeLog("RETRY: $country - $category", 'INFO');
        
        $data = fetchWithRetry($url, 2, 5); // Fewer retries but longer delays
        if ($data === null) {
            writeLog("Final retry failed for $country - $category", 'ERROR');
            continue;
        }
        
        $items = $data['items'] ?? (is_array($data) ? $data : []);
        $rank = 1;
        
        foreach ($items as $item) {
            $showUri = $item['showUri'] ?? "";
            $showId = "";
            if ($showUri && strpos($showUri, ':') !== false) {
                $parts = explode(":", $showUri);
                $showId = end($parts);
            }
            
            $showName = trim($item['showName'] ?? '');
            $showPublisher = trim($item['showPublisher'] ?? '');
            $showImageUrl = filter_var($item['showImageUrl'] ?? '', FILTER_VALIDATE_URL) ?: '';
            $showDescription = trim($item['showDescription'] ?? '');
            $countryName = $COUNTRY_NAMES[$country] ?? '';
            $categoryName = ucwords(str_replace(['%252520', '%2526', '-'], [' ', ' And ', ' '], $category));
            $chartRankMove = $item['chartRankMove'] ?? "";
            
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
            
            $stmt->execute();
            $rank++;
        }
        writeLog("Retry successful for $country - $category", 'INFO');
        sleep(2); // Longer delay between retry requests
    }
}

// ==========================
// Compare with yesterday for movement calculation
// ==========================
$yesterday_prefix = "spotify_charts_" . date('Ymd', strtotime('-1 day')) . "_";

$sql_yesterday_table = "
    SELECT table_name 
    FROM information_schema.tables 
    WHERE table_schema = ? 
      AND table_name LIKE ?
    ORDER BY table_name DESC
    LIMIT 1
";

$stmt_yesterday = $mysqli->prepare($sql_yesterday_table);
$like_pattern = $yesterday_prefix . '%';
$stmt_yesterday->bind_param("ss", $db_name, $like_pattern);
$stmt_yesterday->execute();
$result = $stmt_yesterday->get_result();
$yesterday_table = $result->num_rows ? $result->fetch_assoc()['table_name'] : null;
$stmt_yesterday->close();

if ($yesterday_table) {
    writeLog("Comparing with yesterday's table: $yesterday_table", 'INFO');

    // Load yesterday's data into memory for faster comparison
    $old_data = [];
    $sql_old = "SELECT showId, countryCode, category, chart_rank FROM `$yesterday_table`";
    $result_old = $mysqli->query($sql_old);
    
    if ($result_old) {
        while ($row = $result_old->fetch_assoc()) {
            $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
            $old_data[$key] = (int)$row['chart_rank'];
        }
        writeLog("Loaded " . count($old_data) . " records from yesterday for comparison", 'INFO');
    }

    // Update today's data with movement calculations
    $sql_today = "SELECT id, showId, countryCode, category, chart_rank FROM `$today_table`";
    $result_today = $mysqli->query($sql_today);

    $update_sql = "UPDATE `$today_table` SET y_rank=?, movement=?, cal_move=? WHERE id=?";
    $stmt_update = $mysqli->prepare($update_sql);
    
    $movement_stats = ['new' => 0, 'up' => 0, 'down' => 0, 'unchanged' => 0];

    if ($stmt_update && $result_today) {
        while ($row = $result_today->fetch_assoc()) {
            $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
            $today_rank = (int)$row['chart_rank'];
            $y_rank = $old_data[$key] ?? null;
            
            $movement = $y_rank !== null ? $y_rank - $today_rank : null;
            
            if ($y_rank === null) {
                $cal_move = "new";
            } elseif ($today_rank < $y_rank) {
                $cal_move = "up";
            } elseif ($today_rank > $y_rank) {
                $cal_move = "down";
            } else {
                $cal_move = "unchanged";
            }
            
            $movement_stats[$cal_move]++;
            
            $stmt_update->bind_param("iisi", $y_rank, $movement, $cal_move, $row['id']);
            $stmt_update->execute();
        }
        
        writeLog("Movement analysis complete: " . json_encode($movement_stats), 'INFO');
        $stmt_update->close();
    }
} else {
    writeLog("No yesterday table found to compare with pattern: $yesterday_prefix", 'WARN');
}

// ==========================
// Final statistics and cleanup
// ==========================
$total_records_query = "SELECT COUNT(*) as total FROM `$today_table`";
$total_result = $mysqli->query($total_records_query);
$total_records = $total_result ? $total_result->fetch_assoc()['total'] : 0;

writeLog("Script completed successfully!", 'INFO');
writeLog("Total records inserted: $total_records", 'INFO');
writeLog("Table created: $today_table", 'INFO');

if (!empty($failed_combos)) {
    writeLog("Failed combinations that need manual review: " . count($failed_combos), 'WARN');
}

$stmt->close();
$mysqli->close();
?>