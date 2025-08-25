<?php
include_once './config.php';

date_default_timezone_set("Asia/Kolkata");

// Country + Category Setup
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

$all_countries = [
    "ad", "ae", "al", "ar", "at", "au", "az", "ba", "bb", "be", "bg", "bh", 
    "bo", "br", "bs", "bw", "bz", "ca", "ch", "cl", "co", "cr", "cy", "cz", 
    "de", "dk", "do", "dz", "ec", "ee", "eg", "es", "fi", "fj", "fr", "gb", 
    "ge", "gh", "gm", "gr", "gt", "gy", "hk", "hn", "hr", "hu", "id", "ie", 
    "il", "in", "is", "it", "jm", "jo", "jp", "ke", "kh", "kr", "kw", "lb", 
    "lc", "lt", "lu", "lv", "ma", "mc", "me", "mg", "mk", "mn", "mo", "mt", 
    "mu", "mx", "mw", "my", "mz", "na", "ne", "ng", "ni", "nl", "no", "np", 
    "nz", "om", "pa", "pe", "pg", "ph", "pl", "ps", "pt", "py", "qa", "ro", 
    "rs", "rw", "sa", "sc", "se", "sg", "si", "sk", "sl", "sm", "sn", "sr", 
    "sv", "sz", "th", "tn", "tr", "tt", "tw", "tz", "ua", "us", "uy", "uz", 
    "vn", "za", "zm", "zw"
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
$success_log = "success_$timestamp.log";
$error_log   = "error_$timestamp.log";

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
// Resume Progress Functions
// ==========================
function saveProgress($country, $category, $status = 'started') {
    $progress = [
        'timestamp' => date('Y-m-d H:i:s'),
        'country' => $country,
        'category' => $category,
        'status' => $status
    ];
    file_put_contents('scraping_progress.json', json_encode($progress, JSON_PRETTY_PRINT));
}

function loadProgress() {
    if (file_exists('scraping_progress.json')) {
        $content = file_get_contents('scraping_progress.json');
        return json_decode($content, true);
    }
    return null;
}

function clearProgress() {
    if (file_exists('scraping_progress.json')) {
        unlink('scraping_progress.json');
    }
}

function findExistingTable($mysqli, $date = null) {
    if ($date === null) {
        $date = date("Ymd");
    }
    
    $search_pattern = "spotify_charts_{$date}_";
    $sql = "SELECT table_name FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name LIKE '{$search_pattern}%' 
            ORDER BY table_name DESC 
            LIMIT 1";
    
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['table_name'] ?? $row['TABLE_NAME'] ?? null;
    }
    return null;
}

function getLastProcessedCountryCategory($mysqli, $table_name) {
    // Get the last processed country and category
    $sql = "SELECT countryCode, category, MAX(id) as last_id 
            FROM `$table_name` 
            GROUP BY countryCode, category 
            ORDER BY last_id DESC 
            LIMIT 1";
    
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'country' => $row['countryCode'],
            'category' => $row['category'],
            'last_id' => $row['last_id']
        ];
    }
    return null;
}

function cleanupLastCountryData($mysqli, $table_name, $country) {
    // Delete all data for the last country to start fresh
    $delete_sql = "DELETE FROM `$table_name` WHERE countryCode = ?";
    $stmt = $mysqli->prepare($delete_sql);
    $stmt->bind_param("s", $country);
    $result = $stmt->execute();
    $deleted_rows = $mysqli->affected_rows;
    $stmt->close();
    
    writeLog("Cleaned up $deleted_rows rows for country: $country", 'INFO');
    return $result;
}

function getResumePosition($all_countries, $resume_country) {
    $position = array_search($resume_country, $all_countries);
    return $position !== false ? $position : 0;
}

// ==========================
// Fetch with retry
// ==========================
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
            writeLog("Attempt $attempt failed with error: $err", 'WARN');
        } elseif ($http_code >= 200 && $http_code < 300) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            } else {
                writeLog("Attempt $attempt: Failed to decode JSON", 'WARN');
            }
        } else {
            writeLog("Attempt $attempt: HTTP status $http_code", 'WARN');
        }

        if ($attempt < $retries) sleep($delay);
    }
    return null;
}

// ==========================
// Function to compare table data differences
// ==========================
function compareTableData($mysqli, $today_table, $yesterday_table) {
    writeLog("=== Starting data comparison between tables ===", 'INFO');
    
    // Get today's data
    $sql_today = "SELECT showId, countryCode, category, chart_rank, showName 
                  FROM `$today_table` 
                  ORDER BY countryCode, category, chart_rank";
    $result_today = $mysqli->query($sql_today);
    
    if (!$result_today) {
        writeLog("Error fetching today's data: " . $mysqli->error, 'ERROR');
        return false;
    }
    
    // Get yesterday's data
    $sql_yesterday = "SELECT showId, countryCode, category, chart_rank, showName 
                     FROM `$yesterday_table` 
                     ORDER BY countryCode, category, chart_rank";
    $result_yesterday = $mysqli->query($sql_yesterday);
    
    if (!$result_yesterday) {
        writeLog("Error fetching yesterday's data: " . $mysqli->error, 'ERROR');
        return false;
    }
    
    // Convert to arrays for comparison
    $today_data = [];
    $yesterday_data = [];
    
    while ($row = $result_today->fetch_assoc()) {
        $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
        $today_data[$key] = [
            'rank' => (int)$row['chart_rank'],
            'name' => $row['showName'],
            'country' => $row['countryCode'],
            'category' => $row['category']
        ];
    }
    
    while ($row = $result_yesterday->fetch_assoc()) {
        $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
        $yesterday_data[$key] = [
            'rank' => (int)$row['chart_rank'],
            'name' => $row['showName'],
            'country' => $row['countryCode'],
            'category' => $row['category']
        ];
    }
    
    // Comparison statistics
    $stats = [
        'today_total' => count($today_data),
        'yesterday_total' => count($yesterday_data),
        'new_shows' => 0,
        'dropped_shows' => 0,
        'rank_changes' => 0,
        'unchanged' => 0,
        'identical_data' => false
    ];
    
    // Find new shows (in today but not in yesterday)
    $new_shows = [];
    foreach ($today_data as $key => $data) {
        if (!isset($yesterday_data[$key])) {
            $new_shows[] = $data;
            $stats['new_shows']++;
        }
    }
    
    // Find dropped shows (in yesterday but not in today)
    $dropped_shows = [];
    foreach ($yesterday_data as $key => $data) {
        if (!isset($today_data[$key])) {
            $dropped_shows[] = $data;
            $stats['dropped_shows']++;
        }
    }
    
    // Find rank changes (shows that exist in both but have different ranks)
    $rank_changes = [];
    foreach ($today_data as $key => $today_show) {
        if (isset($yesterday_data[$key])) {
            $yesterday_rank = $yesterday_data[$key]['rank'];
            $today_rank = $today_show['rank'];
            
            if ($yesterday_rank != $today_rank) {
                $rank_changes[] = [
                    'show' => $today_show['name'],
                    'country' => $today_show['country'],
                    'category' => $today_show['category'],
                    'old_rank' => $yesterday_rank,
                    'new_rank' => $today_rank,
                    'change' => $yesterday_rank - $today_rank
                ];
                $stats['rank_changes']++;
            } else {
                $stats['unchanged']++;
            }
        }
    }
    
    // Check if data is identical
    $stats['identical_data'] = ($stats['new_shows'] == 0 && 
                               $stats['dropped_shows'] == 0 && 
                               $stats['rank_changes'] == 0);
    
    // Log comparison results
    writeLog("=== TABLE COMPARISON RESULTS ===", 'INFO');
    writeLog("Today's table ($today_table): {$stats['today_total']} records", 'INFO');
    writeLog("Yesterday's table ($yesterday_table): {$stats['yesterday_total']} records", 'INFO');
    writeLog("New shows: {$stats['new_shows']}", 'INFO');
    writeLog("Dropped shows: {$stats['dropped_shows']}", 'INFO');
    writeLog("Shows with rank changes: {$stats['rank_changes']}", 'INFO');
    writeLog("Shows with no changes: {$stats['unchanged']}", 'INFO');
    
    if ($stats['identical_data']) {
        writeLog("⚠️  DATA IS IDENTICAL - No differences found between tables", 'WARN');
    } else {
        writeLog("✓ Data differences detected - Movement calculation will proceed", 'INFO');
    }
    
    // Log some examples of changes (limit to avoid spam)
    if (!empty($new_shows)) {
        writeLog("=== SAMPLE NEW SHOWS (first 3) ===", 'INFO');
        $sample_new = array_slice($new_shows, 0, 3);
        foreach ($sample_new as $show) {
            writeLog("NEW: {$show['name']} - {$show['country']}/{$show['category']} - Rank {$show['rank']}", 'INFO');
        }
        if (count($new_shows) > 3) {
            writeLog("... and " . (count($new_shows) - 3) . " more new shows", 'INFO');
        }
    }
    
    if (!empty($dropped_shows)) {
        writeLog("=== SAMPLE DROPPED SHOWS (first 3) ===", 'INFO');
        $sample_dropped = array_slice($dropped_shows, 0, 3);
        foreach ($sample_dropped as $show) {
            writeLog("DROPPED: {$show['name']} - {$show['country']}/{$show['category']} - Was Rank {$show['rank']}", 'INFO');
        }
        if (count($dropped_shows) > 3) {
            writeLog("... and " . (count($dropped_shows) - 3) . " more dropped shows", 'INFO');
        }
    }
    
    if (!empty($rank_changes)) {
        writeLog("=== SAMPLE RANK CHANGES (first 3) ===", 'INFO');
        // Sort by biggest changes first
        usort($rank_changes, function($a, $b) {
            return abs($b['change']) - abs($a['change']);
        });
        
        $sample_changes = array_slice($rank_changes, 0, 3);
        foreach ($sample_changes as $change) {
            $direction = $change['change'] > 0 ? "UP" : "DOWN";
            $movement = abs($change['change']);
            writeLog("$direction: {$change['show']} - {$change['country']}/{$change['category']} - {$change['old_rank']} → {$change['new_rank']} (moved $movement positions)", 'INFO');
        }
        if (count($rank_changes) > 3) {
            writeLog("... and " . (count($rank_changes) - 3) . " more rank changes", 'INFO');
        }
    }
    
    writeLog("=== END COMPARISON ===", 'INFO');
    
    return [
        'has_differences' => !$stats['identical_data'],
        'stats' => $stats,
        'new_shows' => $new_shows,
        'dropped_shows' => $dropped_shows,
        'rank_changes' => $rank_changes
    ];
}

//===================================
// To Delete table that is 3 days old
//===================================
function deleteOldTables($mysqli, $days_back = 3) {
    $target_date = date('Ymd', strtotime("-$days_back days"));
    $search_pattern = "spotify_charts_{$target_date}_";
    
    writeLog("Looking for tables to delete from $days_back days ago (date: $target_date)", 'INFO');
    
    // Find all tables matching the pattern
    $find_sql = "SELECT table_name FROM information_schema.tables 
                 WHERE table_schema = DATABASE() 
                 AND table_name LIKE '{$search_pattern}%' 
                 ORDER BY table_name";
    
    $result = $mysqli->query($find_sql);
    
    if (!$result) {
        writeLog("Error finding tables: " . $mysqli->error, 'ERROR');
        return false;
    }
    
    if ($result->num_rows == 0) {
        writeLog("No tables found matching pattern: {$search_pattern}%", 'INFO');
        return true;
    }
    
    $tables_to_delete = [];
    while ($row = $result->fetch_assoc()) {
        // Handle different possible column names (case variations)
        $table_name = $row['table_name'] ?? $row['TABLE_NAME'] ?? $row['Table_name'] ?? '';
        
        if ($table_name) {
            $tables_to_delete[] = $table_name;
        } else {
            writeLog("ERROR: Could not extract table name from row: " . print_r($row, true), 'ERROR');
        }
    }
    
    writeLog("Found " . count($tables_to_delete) . " table(s) to delete:", 'INFO');
    foreach ($tables_to_delete as $table) {
        writeLog("- $table", 'INFO');
    }
    
    // Delete each table
    $deleted_count = 0;
    $failed_count = 0;
    
    foreach ($tables_to_delete as $table) {
        $drop_sql = "DROP TABLE IF EXISTS `$table`";
        
        if ($mysqli->query($drop_sql)) {
            writeLog("✓ Successfully deleted table: $table", 'INFO');
            $deleted_count++;
        } else {
            writeLog("✗ Failed to delete table $table: " . $mysqli->error, 'ERROR');
            $failed_count++;
        }
    }
    
    writeLog("Cleanup summary: $deleted_count deleted, $failed_count failed", 'INFO');
    
    return $failed_count == 0;
}


// ==========================
// Connect MySQL
// ==========================
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    writeLog("Failed to connect to MySQL: " . $mysqli->connect_error, 'ERROR');
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// ==========================
// Check for existing incomplete table and resume logic
// ==========================
$existing_table = findExistingTable($mysqli);
$is_resuming = false;
$start_country_index = 0;

if ($existing_table) {
    writeLog("Found existing table for today: $existing_table", 'INFO');
    
    // Check if table has data
    $count_sql = "SELECT COUNT(*) as count FROM `$existing_table`";
    $count_result = $mysqli->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $existing_records = $count_row['count'];
    
    if ($existing_records > 0) {
        writeLog("Table has $existing_records existing records. Checking for resume...", 'INFO');
        
        // Get last processed country/category
        $last_processed = getLastProcessedCountryCategory($mysqli, $existing_table);
        
        if ($last_processed) {
            $last_country = $last_processed['country'];
            writeLog("Last processed: Country={$last_country}, Category={$last_processed['category']}", 'INFO');
            
            // Clean up the last country's data and resume from that country
            cleanupLastCountryData($mysqli, $existing_table, $last_country);
            
            // Find the position of the last country in our array
            $start_country_index = getResumePosition($all_countries, $last_country);
            $is_resuming = true;
            $today_table = $existing_table;
            
            writeLog("🔄 RESUMING from country index $start_country_index ({$last_country})", 'INFO');
        }
    }
}

// ==========================
// Create today table (only if not resuming)
// ==========================
if (!$is_resuming) {
    $today_table = "spotify_charts_$timestamp";
    $createSQL = "CREATE TABLE `$today_table` (
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
        y_rank INT DEFAULT NULL,
        chartRankMove VARCHAR(100) DEFAULT NULL,
        cal_move VARCHAR(100) NOT NULL,
        movement INT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

    if (!$mysqli->query($createSQL)) {
        writeLog("Failed to create table: " . $mysqli->error, 'ERROR');
        die("Failed to create table: " . $mysqli->error);
    }
    writeLog("Table '$today_table' created successfully.", 'INFO');
}

// ==========================
// Prepare insert
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
// Fetch & Insert with retry tracking (starting from resume position)
// ==========================
$countries = $all_countries;
$failed_combos = [];

// Start from the resume position
for ($i = $start_country_index; $i < count($countries); $i++) {
    $country = $countries[$i];
    
    // Save progress
    saveProgress($country, 'starting');
    
    $categories = in_array($country, $Seventeen) ? $CATEGORIES_20 : 
                 (in_array($country, $Three) ? $CATEGORIES_3 : $CATEGORIES_1);

    foreach ($categories as $category) {
        // Update progress with current category
        saveProgress($country, $category, 'processing');
        
        $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
        writeLog("Fetching " . strtoupper($country) . " - $category → $url", 'INFO');

        $data = fetchWithRetry($url);
        if ($data === null) {
            $failed_combos[] = [$country, $category];
            writeLog("Failed after retries: " . strtoupper($country) . " - $category", 'WARN');
            continue;
        }

        $items = $data['items'] ?? (is_array($data) ? $data : []);
        $rank = 1;
        foreach ($items as $item) {
            $showUri = $item['showUri'] ?? "";
            $showId = $showUri ? explode(":", $showUri)[count(explode(":", $showUri)) - 1] : "";
            $showName        = $item['showName'] ?? '';
            $showPublisher   = $item['showPublisher'] ?? '';
            $showImageUrl    = $item['showImageUrl'] ?? '';
            $showDescription = $item['showDescription'] ?? '';
            $countryName     = $COUNTRY_NAMES[$country] ?? '';
            $categoryName    = ucwords(str_replace(['%252520', '%2526', '-'], [' ', ' And ', ' '], $category));
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
                $chartRankMove,
            );
            if (!$stmt->execute()) {
                writeLog("Insert failed for $country - $category - " . $stmt->error, 'ERROR');
                $failed_combos[] = [$country, $category];
            }
            $rank++;
        }
        writeLog("Inserted " . count($items) . " rows for $country - $category", 'INFO');
        
        // Mark category as completed
        saveProgress($country, $category, 'completed');
        
        sleep(2);
    }
    
    writeLog("✓ Completed country: " . strtoupper($country), 'INFO');
}

// Retry failed combos once more
foreach ($failed_combos as $combo) {
    list($country, $category) = $combo;
    $url = "https://podcastcharts.byspotify.com/api/charts/$category?region=$country";
    writeLog("Retrying failed combo: $country - $category", 'INFO');
    $data = fetchWithRetry($url);
    if ($data === null) {
        writeLog("Retry failed for $country - $category", 'ERROR');
        continue;
    }
    $items = $data['items'] ?? (is_array($data) ? $data : []);
    $rank = 1;
    foreach ($items as $item) {
            $showUri = $item['showUri'] ?? "";
            $showId = $showUri ? explode(":", $showUri)[count(explode(":", $showUri)) - 1] : "";
            $showName        = $item['showName'] ?? '';
            $showPublisher   = $item['showPublisher'] ?? '';
            $showImageUrl    = $item['showImageUrl'] ?? '';
            $showDescription = $item['showDescription'] ?? '';
            $countryName     = $COUNTRY_NAMES[$country] ?? '';
            $categoryName    = ucwords(str_replace(['%252520', '%2526', '-'], [' ', ' And ', ' '], $category));
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
                $chartRankMove,
            );
        $stmt->execute();
        $rank++;
    }
}

// Clear progress file when scraping is completed successfully
clearProgress();
writeLog("✅ All countries processed successfully. Progress file cleared.", 'INFO');


// ==========================
// Compare with yesterday for movement 
// ==========================
function movement_calculation($mysqli,$db_name,$today_table){
$yesterday_date = date('Ymd', strtotime('-1 day'));
$search_pattern = "spotify_charts_{$yesterday_date}_";

writeLog("Searching for yesterday's table with pattern: {$search_pattern}%", 'INFO');

// First, let's debug what columns are available
$debug_sql = "SELECT table_name FROM information_schema.tables 
              WHERE table_schema = '$db_name' 
              AND table_name LIKE '{$search_pattern}%' 
              ORDER BY table_name DESC";

$res = $mysqli->query($debug_sql);
$yesterday_table = null;

if ($res && $res->num_rows > 0) {
    writeLog("Query found " . $res->num_rows . " matching tables", 'INFO');
    
    // Debug: Check what columns are in the result
    $first_row = $res->fetch_assoc();
    writeLog("DEBUG: Result columns: " . implode(', ', array_keys($first_row)), 'INFO');
    writeLog("DEBUG: First row data: " . print_r($first_row, true), 'INFO');
    
    // Reset pointer and get all tables
    $res->data_seek(0); // Reset to beginning
    $tables_found = [];
    
    while ($row = $res->fetch_assoc()) {
        // Handle different possible column names
        $table_name = $row['table_name'] ?? $row['TABLE_NAME'] ?? $row['Table_name'] ?? '';
        
        if ($table_name) {
            $tables_found[] = $table_name;
            writeLog("Found table: $table_name", 'INFO');
        } else {
            writeLog("ERROR: Could not extract table name from row: " . print_r($row, true), 'ERROR');
        }
    }
    
    if (!empty($tables_found)) {
        $yesterday_table = $tables_found[0]; // Use most recent (first in DESC order)
        writeLog("Selected yesterday table: $yesterday_table", 'INFO');
    }
    
} else {
    writeLog("No tables found matching pattern: {$search_pattern}%", 'WARN');
    
    // Show all spotify_charts tables for reference
    $all_tables_sql = "SELECT table_name FROM information_schema.tables 
                      WHERE table_schema = '$db_name' 
                      AND table_name LIKE 'spotify_charts_%' 
                      ORDER BY table_name DESC 
                      LIMIT 10";
    $all_result = $mysqli->query($all_tables_sql);
    
    writeLog("=== Recent spotify_charts tables in database ===", 'INFO');
    if ($all_result && $all_result->num_rows > 0) {
        while ($row = $all_result->fetch_assoc()) {
            $table_name = $row['table_name'] ?? $row['TABLE_NAME'] ?? 'Unknown';
            writeLog("Available: $table_name", 'INFO');
        }
    } else {
        writeLog("No spotify_charts tables found at all", 'INFO');
    }
}

// Continue with the rest of your comparison logic
if ($yesterday_table) {
    writeLog("Comparing with yesterday's table: $yesterday_table", 'INFO');

    $comparison_result = compareTableData($mysqli, $today_table, $yesterday_table);
    
    if (!$comparison_result['has_differences']) {
        writeLog("🛑 SKIPPING movement calculation - tables are identical", 'WARN');
    } else {
    $old_data = [];
    $sql_old = "SELECT id, showId, countryCode, category, chart_rank FROM `$yesterday_table`";
    $result_old = $mysqli->query($sql_old);
    if ($result_old) {
        while ($row = $result_old->fetch_assoc()) {
            $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
            $old_data[$key] = (int)$row['chart_rank'];
        }
        writeLog("Loaded " . count($old_data) . " records from yesterday's table", 'INFO');
    }

    $sql_today = "SELECT id, showId, countryCode, category, chart_rank FROM `$today_table`";
    $result_today = $mysqli->query($sql_today);

    $update_sql = "UPDATE `$today_table` SET y_rank=?, movement=?, cal_move=? WHERE id=?";
    $stmt_update = $mysqli->prepare($update_sql);

    if ($stmt_update && $result_today) {
        $updates_made = 0;
        while ($row = $result_today->fetch_assoc()) {
            $key = $row['countryCode'] . "|" . $row['category'] . "|" . $row['showId'];
            $today_rank = (int)$row['chart_rank'];
            $y_rank = $old_data[$key] ?? null;
            $movement = $y_rank !== null ? $y_rank - $today_rank : null;
            $cal_move = $y_rank === null ? "new" : ($today_rank < $y_rank ? "up" : ($today_rank > $y_rank ? "down" : "unchanged"));
            $stmt_update->bind_param("iisi", $y_rank, $movement, $cal_move, $row['id']);
            $stmt_update->execute();
            $updates_made++;
        }
        writeLog("Updated $updates_made records with movement data", 'INFO');
    }
}
} else {
    writeLog("No yesterday table found to compare.", 'WARN');
}
}
movement_calculation($mysqli,$db_name,$today_table);
deleteOldTables($mysqli, $days_back = 3);

$stmt->close();
$mysqli->close();
writeLog("Script completed successfully.", 'INFO');
?>